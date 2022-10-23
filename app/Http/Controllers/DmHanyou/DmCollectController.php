<?php

namespace App\Http\Controllers\DmHanyou;

use App\Original\Util\SessionUtil;
use App\Http\Requests\SearchRequest;
use App\Commands\DmHanyou\Collect\SumBaseCommand;
use App\Commands\DmHanyou\Collect\SumUserCommand;
use App\Commands\DmHanyou\Collect\SumBaseCsvCommand;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Result\tResultSearch;

/**
  * DMの拠点明細のコントローラー
 */
class DmCollectController extends Controller {
    
    use tResultSearch;
    
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
        $this->displayObj->category = "dm_hanyou";
        // 画面名
        $this->displayObj->page = "collect";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "DmHanyou\DmCollectController";
        // CSVファイル名
        $this->displayObj->csvFileName = mb_convert_encoding( "拠点明細.csv", "Shift-JIS", "UTF-8" );
    }
    
    #######################
    ## 検索・並び替え
    #######################
    
    /**
     * 検索部分のデフォルト値を指定
     * @return [type] [description]
     */
    public function extendSearchParams(){
        // 検索の値を格納する配列
        $search = [];
        
        return $search;
    }
    
    #######################
    ## 一覧画面
    #######################
    
    /**
     * 一覧画面のデータを表示
     * @param  object $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj ){
        //
        $showData = $this->dispatch(
            new SumBaseCommand(
                $search,
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
                'showData'
            )
        )
        ->with( "title", "DM拠点明細" )
        ->with( 'displayObj', $this->displayObj );
    }
        
    /**
     * 実績分析の各画面で担当者ごとに表示
     * @param  SearchRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function getEachStaff( SearchRequest $requestObj ) {
        $search = $requestObj->all();
        
        // 検索値を登録(セッション)
        SessionUtil::putSearch( $search );
        
        $showData = $this->dispatch(
            new SumUserCommand(
                $search,
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
                'showData'
            )
        )
        ->with( "title", "DM拠点明細" )
        ->with( 'displayObj', $this->displayObj );
    }

    /**
     * CSVダウンロード機能
     * @param  SearchRequest $requestObj [description]
     * @return [type]                 [description]
     */
    public function getCsv( SearchRequest $requestObj ){
        $search = $requestObj->all();

        //try {
            $csv = $this->dispatch(
                new SumBaseCsvCommand(
                    $search,
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
