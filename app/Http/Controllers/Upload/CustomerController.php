<?php

namespace App\Http\Controllers\Upload;

use App\Original\Util\SessionUtil;
use App\Models\Customer;
use App\Commands\Upload\Customer\ListCommand;
use App\Commands\Upload\Customer\ListCsvCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;

class CustomerController extends Controller {

    use tInitSearch;

    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
    }

    #######################
    ## initalize
    #######################
    
    /**
     * 表示部分で使うオブジェクトを作成
     * @return [type] [description]
     */
    public function initDisplayObj(){
        // 表示部分で使うオブジェクトを作成
        $this->displayObj = app('stdClass');
        // カテゴリー名
        $this->displayObj->category = "upload";
        // 画面名
        $this->displayObj->page = "customer";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Upload\CustomerController";
        // 出力するcsvファイル名
        $this->displayObj->csvFileName = 'upload_customer.csv';
    }

    #######################
    ## 検索・並び替え
    #######################
    
    /**
     * 並び替え部分のデフォルト値を指定
     * @return [type] [description]
     */
    public function extendSortParams() {
        // 複数テーブルにあるidが重複するため明示的にエイリアス指定
        $sort = [ 'tb_customer.car_manage_number' => 'asc' ];
        
        return $sort;
    }
    
    /**
     * 検索部分のデフォルト値を指定
     * @return [type] [description]
     */
    public function extendSearchParams() {
        // 検索の値を格納する配列
        $search = array();
        
        // 検索値のセッションの確認
        if ( SessionUtil::hasSearch() == True ){
            // 検索値を取得(セッション)
            $search = SessionUtil::getSearch();
            
        }else{
            // ユーザー情報を取得(セッション)
            $loginAccountObj = SessionUtil::getUser();

            // 店長権限よりも上の階層の時の処理
            if ( !in_array( $loginAccountObj->getRolePriority(), [1,2,3] ) ) {
                $search['base_code'] = $this->selectedBaseCode();
                if( !in_array( $loginAccountObj->getRolePriority(), [4,5] ) ) {
                    $search['user_id'] = $this->selectedUserId();
                }
            }   
        
        }
        
        return $search;
    }
    
    #######################
    ## 一覧画面
    #######################
    
    /**
     * 一覧画面のデータを表示
     * @param  array $search      [description]
     * @param  array $sort        [description]
     * @param  object $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj ){
        // 
        $showData = $this->dispatch(
            new ListCommand(
                $sort,
                $requestObj
            )
        );

        //　表示用に、並び替え情報を取得
        if( isset( $sort['sort'] ) == True && !empty( $sort['sort'] ) == True ){
            foreach ( $sort['sort'] as $key => $value ) {
                // 並び替え情報を格納
                $sortTypes = [
                    'sort_key' => $key,
                    "sort_by" => $value
                ];
            }
        }
        
        return view(
            $this->displayObj->tpl .'.index',
            compact(
                'search',
                'sortTypes',
                'showData'
            )
        )
        ->with( "title", "顧客リスト" )
        ->with( 'displayObj', $this->displayObj )
        ->with( "sortUrl", action( $this->displayObj->ctl . '@getSort' ) );
    }

    /**
     * CSVダウンロード機能
     * @param  SearchRequest $requestObj [description]
     * @return [type]             [description]
     */
    public function getCsv( SearchRequest $requestObj ){
        // 並び替えの値を取得
        $sort = $this->getSortParams();
        
        //try {
            $csv = $this->dispatch(
                new ListCsvCommand(
                    $sort,
                    $requestObj,
                    $this->displayObj->csvFileName
                )
            );
            
            return $csv;
            
        /*
        } catch ( \Exception $e ) {
            \Log::debug( $e->getMessage() );
            
            return redirect( action( $this->displayObj->ctl . '@getSearch' ) )
                    ->withErrors( $e->getMessage() );
            
        }
        */
    }

}
