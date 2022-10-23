<?php
//
//namespace App\Http\Controllers\Main;
//
//use App\Original\Util\SessionUtil;
//use App\Models\Credit;
//use App\Commands\Main\Credit\ListCommand;
//use App\Commands\Main\Credit\ListCsvCommand;
//use App\Http\Requests\SearchRequest;
//use App\Http\Controllers\Controller;
//use App\Http\Controllers\tInitSearch;
//use App\Http\Controllers\tEdit;
//
//class CreditController extends Controller{
//
//    use tInitSearch, tEdit;
//
//    /**
//     * コンストラクタ
//     */
//    function __construct(){
//        // 表示部分で使うオブジェクトを作成
//        $this->initDisplayObj();
//    }
//
//    #######################
//    ## initalize
//    #######################
//
//    /**
//     * 表示部分で使うオブジェクトを作成
//     * @return [type] [description]
//     */
//    public function initDisplayObj(){
//        // 表示部分で使うオブジェクトを作成
//        $this->displayObj = app('stdClass');
//        // カテゴリー名
//        $this->displayObj->category = "main";
//        // 画面名
//        $this->displayObj->page = "credit";
//        // 基本のテンプレート
//        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
//        // コントローラー名
//        $this->displayObj->ctl = "Main\CreditController";
//        // 出力するcsvファイル名
//        $this->displayObj->csvFileName = 'main_target.csv';
//    }
//
//    #######################
//    ## 検索・並び替え
//    #######################
//
//    /**
//     * 並び替え部分のデフォルト値を指定
//     * @return [type] [description]
//     */
//    public function extendSortParams() {
//        // 複数テーブルにあるidが重複するため明示的にエイリアス指定
//        $sort = [ Credit::getTableName() . '.cre_car_manage_number' => 'asc' ];
//
//        return $sort;
//    }
//
//    /**
//     * 検索部分のデフォルト値を指定
//     * @return [type] [description]
//     */
//    public function extendSearchParams(){
//        // 検索の値を格納する配列
//        $search = array();
//
//        // 検索値のセッションの確認
//        if ( SessionUtil::hasSearch() == True ){
//            // 検索値を取得(セッション)
//            $search = SessionUtil::getSearch();
//
//        }else{
//
//            // 権限を調べ為の値を取得
//            $loginAccountObj = SessionUtil::getUser();
//
//
//            // 店長権限よりも上の階層の時の処理
//            if ( !in_array( $loginAccountObj->getRolePriority(), [1,2,3] ) ) {
//                $search['base_code'] = $this->selectedBaseCode();
//               if( !in_array( $loginAccountObj->getRolePriority(), [4,5] ) ) {
//                $search['user_id'] = $this->selectedUserId();
//                }
//            }
//
//        }
//         $search['cre_shipping_ym_from'] = $this->selectedYm();;
//         $search['cre_shipping_ym_to'] = $this->selectedYm();;
//        return $search;
//    }
//
//    #######################
//    ## 一覧画面
//    #######################
//
//    /**
//     * 一覧画面のデータを表示
//     * @param  [type] $search     [description]
//     * @param  [type] $sort   [description]
//     * @param  [type] $requestObj [description]
//     * @return [type]             [description]
//     */
//    public function showListData( $search, $sort, $requestObj ){
//        // そもそも取得できないので引数はnullにしている
//        $showData = $this->dispatch(
//            new ListCommand(
//                $sort,
//                $requestObj
//            )
//        );
//        //dd($showData);
//        //　表示用に、並び替え情報を取得
//        if( isset( $sort['sort'] ) == True && !empty( $sort['sort'] ) == True ){
//            foreach ( $sort['sort'] as $key => $value ) {
//                // 並び替え情報を格納
//                $sortTypes = [
//                    'sort_key' => $key,
//                    "sort_by" => $value
//                ];
//            }
//        }
//
//        return view(
//            $this->displayObj->tpl . '.index',
//            compact(
//                'search',
//                'sortTypes',
//                'showData'
//            )
//        )
//        ->with( "title", "クレジットリスト" )
//        ->with( 'displayObj', $this->displayObj )
//        ->with( "sortUrl", action( $this->displayObj->ctl . '@getSort' ) );
//    }
//
//    /**
//     * CSVダウンロード機能
//     * @param  SearchRequest $requestObj [description]
//     * @return [type]                 [description]
//     */
//    public function getCsv( SearchRequest $requestObj ){
//        // 並び替えの値を取得
//        $sort = $this->getSortParams();
//
//        //try {
//            $csv = $this->dispatch(
//                new ListCsvCommand(
//                    $sort,
//                    $requestObj,
//                    $this->displayObj->csvFileName
//                )
//            );
//
//            return $csv;
//
//        /*
//        } catch ( \Exception $e ) {
//            \Log::debug( $e->getMessage() );
//
//            return redirect( action( $this->displayObj->ctl . '@getSearch' ) )
//                    ->withErrors( $e->getMessage() );
//        }
//        */
//    }
//}
