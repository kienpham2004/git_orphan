<?php

namespace App\Http\Controllers\Result;

use App\Original\Util\SessionUtil;
use App\Http\Requests\SearchRequest;
use App\Commands\Result\SumBaseCommand;
use App\Commands\Result\SumUserCommand;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Result\tResultSearch;
use App\Lib\Util\DateUtil;

/**
  * 実績分析の車点検別のコントローラー
 */
class BoueiController extends Controller {
    
    use tResultSearch;
    
    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
        $this->title = "拠点・担当者別防衛率";
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
        $this->displayObj->category = "result";
        // 画面名
        $this->displayObj->page = "bouei";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Result\BoueiController";
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
                $requestObj,
                "syaken_bouei"
                //"all"
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

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'runTime'
            )
        )
        ->with( "title", $this->title )
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
                $requestObj,
                "syaken_bouei"
                //"all"
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
        ->with( "title", "拠点・担当者別防衛率" )
        ->with( 'displayObj', $this->displayObj );
    }

}
