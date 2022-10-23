<?php

namespace App\Http\Controllers\Mut;

use App\Original\Util\SessionUtil;
use App\Commands\Mut\Dm\ListCommand;
use App\Commands\Mut\Dm\ListCsvCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;
// 独自
use OhInspection;

class DmController extends Controller {

    use tInitSearch;

    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
        
        // 管理者権限を有しないとアクセス不可
        $this->middleware(
            'RoleAdmin',
            ['only' => ['getIndex', 'getSearch', 'getPager', 'getSort']]
        );
    }
    
    /**
     * 担当者の顔写真の画像をzipに保存して、ダウンロード
     * @return [type] [description]
     */
    public function getImages(){
        // 担当者の顔写真の画像をzipに保存して、ダウンロード
        OhInspection::getFaceImages("inspectionApp");
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
        $this->displayObj->category = "mut";
        // 画面名
        $this->displayObj->page = "dm";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Mut\DmController";
        // 出力するcsvファイル名
        $this->displayObj->csvFileName = 'dm.csv';
    }
    
    #######################
    ## 検索・並び替え
    #######################
    
    /**
     * [extendSortParams description]
     * @return [type] [description]
     */
    public function extendSortParams() {
        // 複数テーブルにあるidが重複するため明示的にエイリアス指定
        $sort = ['tb_dm.dm_base_code' => 'asc', 'tb_dm.dm_user_id' => 'asc'];

        return $sort;
    }
    
    /**
     * 検索部分のデフォルト値を指定
     * @return [type] [description]
     */
    public function extendSearchParams(){
        // 検索の値を格納する配列
        $search = [];

        // 検索値のセッションの確認
        if ( SessionUtil::hasSearch() == True ){
            // 検索値を取得(セッション)
            $search = SessionUtil::getSearch();

        }else{
            // 取得する検索項目を設定
            // デフォルトを2ヶ月後に指定
            $search['dm_shipping_ym'] = date( "Ym", strtotime( "+2 month" ) );

            // 10日までは前月も表示
//            if( date("d") <= 10 ){
//                $search["dm_shipping_ym"] = date("Ym", strtotime("+1 month"));
//            }

            $search['dm_user_id'] = $this->selectedUserId();
            $search['dm_base_code'] = $this->selectedBaseCode();
        
        }
        
        return $search;
    }

    #######################
    ## 一覧画面
    #######################
    
    /**
     * 一覧画面のデータを表示
     * @param  [type] $search  [description]
     * @param  array $sort    [description]
     * @param  object $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj ){
        //
        list( $showData,$list_dm_car_name ) = $this->dispatch(
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
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'list_dm_car_name'
            )
        )
        ->with( "title", "DM送付用リスト" )
        ->with( 'displayObj', $this->displayObj )
        ->with( "sortUrl", action( $this->displayObj->ctl . '@getSort' ) );
    }
    
    /**
     * CSVダウンロード機能
     * @param  SearchRequest $requestObj [description]
     * @return [type]                 [description]
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
