<?php

namespace App\Http\Controllers\Top;

use App\Lib\Util\DateUtil;
use App\Original\Util\SessionUtil;
use App\Commands\Top\FindInfoCommand;
use App\Commands\Top\TopGraphSumBaseCommand;
use App\Commands\Top\TopGraphSumUserCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\tInitSearch;
use App\Commands\Top\TopGraphCsvCommand;
use App\Lib\Util\Constants;
use App\Models\Base;

/**
 * グラフ画面用コントローラー
 *
 * @author yhatsutori
 *
 */
class GraphController extends Controller{
    
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
        $this->displayObj->category = "top";
        // 画面名
        $this->displayObj->page = "graph";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Top\GraphController";
        //　同じ処理画面チェック
        tInitSearch::checkCurrentScreen(Constants::T00);
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

        // エラーチェックフラグを取得する
        $errorCheck = SessionUtil::hasValidation();
        // セッションのinspectoin_ym_fromが空ならデフォルトを設定とチェックエラー無しの場合
        if ($errorCheck == false) {
            $search['inspection_ym_from'] = DateUtil::currentYm();
        }else{
            $search['inspection_ym_from'] = "";
        }
        //$search['act_inspection_ym_to'] = DateUtil::currentYm();

        // ユーザー情報を取得(セッション)
        $loginAccountObj = SessionUtil::getUser();

        // 店長、営業担当、工場長の時
        if( in_array( $loginAccountObj->getRolePriority(), [4,5,6] ) ){
            $search['base_code'] = $this->selectedBaseCode();
        }
        
        return $search;
    }

    #######################
    ## Controller method
    #######################

    /**
     * グラフの画面を表示
     * @param  [type] $search     [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj, $userFlg=False ){

        /*
        * ログインユーザーにより
        * 以下の表示内容を決定する
        * ・カレンダー表示（営業担当者のみ、それ以外非表示）
        * ・個人成績（営業担当者のみ、それ以外非表示）
        * ・各点検の計画＆実績
        * 　→営業担当者：所属拠点の情報
        * 　→拠点長＆工場長：担当拠点に所属する担当者の一覧で計画＆実績を表示
        * 　→本社担当者＆部長：拠点の一覧で計画＆実績を表示
        */
        // お知らせの取得
        $infoList = $this->dispatch(new FindInfoCommand());

        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"実施年月");
        $mainGraphData = null;
        if ($check == Constants::CONS_OK ) {
            // 担当者の時は担当者別の集計表示
            if ($userFlg == True) {
                // 担当者別成績の取得
                $mainGraphData = $this->dispatch(new TopGraphSumUserCommand($search, $requestObj));
            } else {
                // 拠点成績の取得
                $mainGraphData = $this->dispatch(new TopGraphSumBaseCommand($search, $requestObj));
            }
        }

        // ダウンロード設定
        $downloadCsv = false;
        if($mainGraphData != null){
            $data = $mainGraphData->inspection_4;
            foreach( $data as $ym => $list ){
                if( isset($list) && count($list) > 0){
                    $downloadCsv = true;
                    break;
                }
            }
        }

        $screenID = Constants::T00;
        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
        return view(
            'top.graph.index',
            compact(
                'search',
                'infoList',
                'mainGraphData',
                'userFlg',
                'runTime',
                'downloadCsv',
                'screenID'
            )
        )
        ->with( 'displayObj', $this->displayObj )
        ->withErrors($validator);
    }

    /**
     * 担当者単位での集計を取得する
     * @param  SearchRequest $requestObj [description]
     * @return [type]                    [description]
     */
    public function getSummaryByStaff( SearchRequest $requestObj ){
        // 並び順が格納されている配列を取得
        $sort = $this->getSortParams();

        // 検索項目の取得
        $search = $requestObj->all();
                
        return $this->showListData( $search, $sort, $requestObj, True );
    }
    
    /**
     * CSVダウンロード機能
     * @param  SearchRequest $requestObj [description]
     * @return [type]                 [description]
     */
    public function getCsv( SearchRequest $requestObj ){

        // 検索値を登録(セッション)
        SessionUtil::putSearch( $requestObj->all() );
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $requestObj->all(),"実施年月");
        if ($check == Constants::CONS_ERROR) {
            return Redirect::to('/top/search')
                ->withInput()
                ->withErrors($validator);
        }

        // 並び替えの値を取得
        //$sort = $this->getSortParams();
        //CSVファイル名
        $baseName = "";
        if ($requestObj->base_code != "") {
            $base = Base::getBaseNameByCode($requestObj->base_code) ;
            $baseName = $base[0];
        }
        if( isset($requestObj->top_staffList_flag)){
            $filename = "車検実施状況(担当者)_".$baseName."_".date("Ymd_His").".csv";
        }
        else{
            if($baseName == "" ){
                $baseName = "全拠点";
            }
            $filename = "車検実施状況(".$baseName.")_".date("Ymd_His").".csv";
        }

        $csv = $this->dispatch(new TopGraphCsvCommand($requestObj, $filename));
        return $csv;
    }
    
}
