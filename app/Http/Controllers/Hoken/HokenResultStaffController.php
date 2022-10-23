<?php

namespace App\Http\Controllers\Hoken;

use App\Lib\Util\DateUtil;
use App\Original\Util\SessionUtil;
use App\Commands\Hoken\ResultStaff\ListCommand;
use App\Commands\Hoken\ResultStaff\UpdateCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;

/**
 * グラフ画面用コントローラー
 *
 * @author yhatsutori
 *
 */
class HokenResultStaffController extends Controller{
    
    use tInitSearch;
    
    /**
     * コンストラクタ
     */
    public function __construct(){
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
        $this->displayObj->category = "hoken";
        // 画面名
        $this->displayObj->page = "hoken_result_staff";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Hoken\HokenResultStaffController";
    }

    #######################
    ## 検索・並び替え
    #######################

    /**
     * 画面固有の初期条件設定
     *
     * @return array
     */
    public function extendSearchParams(){
        // 年度の値
        $search['year'] = date("Y");

        // 1月2月3月の時は年度をマイナスにする
        if( in_array( date("m"), ["01", "02", "03"] ) == True ){
            $search['year'] = strval( intval( $search['year'] ) - 1 );
        }
        
        return $search;
    }

    #######################
    ## Controller method
    #######################
    
    /**
     * 一覧画面のデータを表示
     * @param  [type] $search     [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj ){
        // 担当者数登録(拠点毎)を取得
        $showData = $this->dispatch(new ListCommand($requestObj));

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'requestObj',
                'showData',
                'runTime',
                'screenID'
            )
        )
        ->with( 'displayObj', $this->displayObj )
        ->with( 'title', "担当者数登録(拠点毎)" );
    }

    #######################
    ## 担当者数登録(拠点毎)
    #######################

    /**
     * [postEdit description]
     * @param  SearchRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function postEdit( SearchRequest $requestObj ){
        // 成約計画の登録
        $this->dispatch(
            new UpdateCommand(
                $requestObj
            )
        );
        
        // 計画の値を登録後に、拠点情報を保持しておく
        return redirect( action( $this->displayObj->ctl . '@getSearch' ) . "?year=" . $requestObj->year );
    }
    
}
