<?php

namespace App\Http\Controllers\Result;

use App\Original\Util\CodeUtil;
use App\Original\Util\SessionUtil;
use App\Http\Requests\SearchRequest;
use App\Commands\Result\SumBaseCommand;
use App\Commands\Result\SumUserCommand;
use App\Commands\Result\SyakenCsvCommand;
use App\Http\Controllers\Controller;
use App\Models\Plan;
Use Request;
Use Session;
use App\Lib\Util\DateUtil;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\tInitSearch;
use Validator;
use App\Lib\Util\Constants;

/**
  * 実績分析の車点検別のコントローラー
 */
class SyakenController extends Controller {
    
    use tResultSearch;
    
    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
        $this->title = "拠点・担当者別実施率(車検)";
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
        $this->displayObj->page = "syaken";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Result\SyakenController";
        //　同じ処理画面チェック
        tInitSearch::checkCurrentScreen(Constants::B00);
    }

    #######################
    ## 一覧画面
    #######################
    
    /**
     * 一覧画面のデータを表示
     * @param  object $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj, $userFlg = False ){

        $isStaffPage = Request::is("result/{$this->displayObj->page}/each-staff*");
        $showData = null;
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"車検年月");
        // チェック問題がない場合、データを取得
        if ($check == Constants::CONS_OK ) {
            $showData = $this->dispatch(new SumBaseCommand($search, $requestObj, "syaken"));
        }

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

        // ダウンロード設定
        $downloadCsv = false;
        $arrRankKeikaku = $arrRankTougetu = $arrRankBouei = $arrRankJissi = $arrRankShoKaiLogin = array();
        if($showData != null && !$showData->isEmpty()){
            $downloadCsv = true;

            // 列の色設定
            List($arrKeikaku, $arrTougetu, $arrBouei, $arrJissi, $arrShoKaiLogin ) = $this->getArrayForColorSetting($showData, $userFlg);
            $arrRankKeikaku = CodeUtil::getRankOfArray($arrKeikaku);
            $arrRankTougetu = CodeUtil::getRankOfArray($arrTougetu);
            $arrRankBouei = CodeUtil::getRankOfArray($arrBouei);
            $arrRankJissi = CodeUtil::getRankOfArray($arrJissi);
            $arrRankShoKaiLogin = CodeUtil::getRankOfArray($arrShoKaiLogin);
        }

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'runTime',
                'downloadCsv',
                'isStaffPage',
                'userFlg',
                'arrRankKeikaku',
                'arrRankTougetu',
                'arrRankBouei',
                'arrRankJissi',
                'arrRankShoKaiLogin'
            )
        )
        ->with( "title", $this->title )
        ->with( 'displayObj', $this->displayObj )
        ->withErrors($validator);
    }
        
    /**
     * 実績分析の各画面で担当者ごとに表示
     * @param  SearchRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function getEachStaff( SearchRequest $requestObj, $userFlg = False ) {

        $isStaffPage = Request::is("result/{$this->displayObj->page}/each-staff*");
        // 検索の値を取得
        $search = $requestObj->all();
        // 検索値を登録(セッション)
        SessionUtil::putSearch( $search );

        $showData = null;
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"車検年月");
        // チェック問題がない場合、データを取得
        if ($check == Constants::CONS_OK ) {
            $showData = $this->dispatch(new SumUserCommand($search, $requestObj, "syaken"));
        }
        
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

        // ダウンロード設定
        $downloadCsv = false;
        if($showData != null && !$showData->isEmpty()){
            $downloadCsv = true;

            // 列の色設定
            List($arrKeikaku, $arrTougetu, $arrBouei, $arrJissi, $arrShoKaiLogin ) = $this->getArrayForColorSetting($showData, $userFlg);
            $arrRankKeikaku = CodeUtil::getRankOfArray($arrKeikaku);
            $arrRankTougetu = CodeUtil::getRankOfArray($arrTougetu);
            $arrRankBouei = CodeUtil::getRankOfArray($arrBouei);
            $arrRankJissi = CodeUtil::getRankOfArray($arrJissi);
            $arrRankShoKaiLogin = CodeUtil::getRankOfArray($arrShoKaiLogin);
        }

        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'downloadCsv',
                'isStaffPage',
                'userFlg',
                'arrRankKeikaku',
                'arrRankTougetu',
                'arrRankBouei',
                'arrRankJissi',
                'arrRankShoKaiLogin'
            )
        )
        ->with( "title", $this->title )
        ->with( 'displayObj', $this->displayObj )
        ->withErrors($validator);
    }
    
    /**
     * 入力された値を登録
     * @param  [type]      $id         [description]
     * @param  SearchRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function postUpdate( SearchRequest $requestObj ){
        //計画値の更新
        $this->updatePlan();
        // 更新のセッションを用意
        Session::put('update', 1);

        return redirect( action( $this->displayObj->ctl . '@getSearch' ) );
    }

    /**
     * 計画値の更新処理
     * @param  [type]      $id         [description]
     * @param  InfoRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function updatePlan(){

        $req = Request::all();

        // 不要な項目削除
        unset($req['_token']);
        
        $upData = array();
        foreach($req as $key => $val){
            $tmp = explode( "/", $key);
            $list = array();
            
            //拠点コードが未設定の場合スキップ
            if(empty($tmp[0])){
                continue;
            }else{
                $list['plan_base_id'] = $tmp[0];
                if(empty($tmp[1])){
                    $tmp[1] = null;
                }
                $list['plan_user_id'] = $tmp[1];
                $list['plan_ym'] = $tmp[2];
                $list['plan_data'] = $val;
                
                
            }
            $upData[] = $list;
        }
        
        //計画値更新
        foreach($upData as $list){
            Plan::merge( $list );
        }

    }
    
    /**
     * CSVダウンロード機能
     * @param  SearchRequest $requestObj [description]
     * @return [type]                 [description]
     */
    public function getCsv( SearchRequest $requestObj ){

        // 検索の値を取得
        $search = $requestObj->all();
        // 検索値を登録(セッション)
        SessionUtil::putSearch( $search );

        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"車検年月");
        if ($check == Constants::CONS_ERROR) {
            return Redirect::to('/result/syaken/search')
                ->withInput()
                ->withErrors($validator);
        }

        //CSVファイル名
        $filename="実績分析_拠点・担当者別実施率(車検)_".date("Ymd_His").".csv";

        $csv = $this->dispatch(
            new SyakenCsvCommand(
                $requestObj,
                $filename
            )
        );

        return $csv;
    }

    /**
     * 対象配列の値を取得する。
     * @param array $showData 表示データ
     * @param bool $userFlg ユーザーフラグ
     * @return array $data
     */
    public function getArrayForColorSetting($showData, $userFlg)
    {
        $arrKeikaku = array();
        $arrTougetu = array();
        $arrBouei = array();
        $arrJissi = array();
        $arrShoKaiLogin = array();

        foreach ($showData as $key => $value) {
            // 法人営業部拠点とＵＳ広瀬拠点を除く、法人営業部は75のコードを変換する。
            //拠点のみ場合
            if (!$userFlg && in_array($value->base_code, array('70', '75', ''))) {
                continue;
            }

            $num = $value->senkou_jissi_count + $value->tougetu_jissi_count + $value->tougetu_keika_jissi_count;
            $sum = $value->daigae_count + $value->tougetu_taisyo_jissi_count;

            // 計画実施率
            if ($num > 0 && $value->plan_data > 0) {
                array_push($arrKeikaku, round($num / $value->plan_data * 100, 1));
            } else {
                array_push($arrKeikaku, 0);
            }

            // 当月対象実施率
            if ($value->target_count > 0 && $value->tougetu_taisyo_jissi_count > 0) {
                array_push($arrTougetu, round($value->tougetu_taisyo_jissi_count / $value->target_count * 100, 1));
            } else {
                array_push($arrTougetu, 0);
            }

            // 防衛率
            if ($sum > 0 && $value->target_count > 0) {
                array_push($arrBouei, round($sum / $value->target_count * 100, 1));
            } else {
                array_push($arrBouei, 0);
            }

            // 実施率
            if ($value->togetu_recall_jisshi_count > 0 && $value->togetu_recall_target_count > 0) {
                array_push($arrJissi, round($value->togetu_recall_jisshi_count / $value->togetu_recall_target_count * 100, 1));
            } else {
                array_push($arrJissi, 0);
            }

            // 初回ログイン率
            if ($value->togetu_mi_htc_login_flg_count > 0 && $value->target_count > 0) {
                array_push($arrShoKaiLogin, round($value->togetu_mi_htc_login_flg_count / $value->target_count * 100, 1));
            } else {
                array_push($arrShoKaiLogin, 0);
            }

        }
        sort($arrKeikaku);
        sort($arrTougetu);
        sort($arrBouei);
        sort($arrJissi);
        sort($arrShoKaiLogin);
        return array($arrKeikaku, $arrTougetu, $arrBouei, $arrJissi, $arrShoKaiLogin);
    }
}