<?php

namespace App\Http\Controllers\Result;

use App\Original\Util\CodeUtil;
use App\Original\Util\SessionUtil;
use App\Http\Requests\SearchRequest;
use App\Commands\Result\SumBaseCommand;
use App\Commands\Result\SumUserCommand;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Result\tResultSearch;
use App\Lib\Util\DateUtil;
use App\Http\Controllers\tInitSearch;
use Illuminate\Support\Facades\Redirect;
use App\Lib\Util\Constants;
Use Request;

/**
  * 実績分析の車点検別のコントローラー
 */
class TenkenController extends Controller {
    
    use tResultSearch;
    
    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
        $this->title = "拠点・担当者別実施率(点検)";
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
        $this->displayObj->page = "tenken";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Result\TenkenController";
        //　同じ処理画面チェック
        tInitSearch::checkCurrentScreen(Constants::B10);
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
        $check = tInitSearch::checkValidate($validator, $search,"点検年月");
        // チェック問題がない場合、データを取得
        if ($check == Constants::CONS_OK ) {
            $showData = $this->dispatch(new SumBaseCommand($search, $requestObj, "tenken"));
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
        $arrRankSouJissi = $arrRankTougetu = $arrRankJissi = $arrRankShoKaiLogin = array();
        if($showData != null && !$showData->isEmpty()){
            $downloadCsv = true;

            // 列の色設定
            List($arrSouJissi, $arrTougetu, $arrJissi, $arrShoKaiLogin ) = $this->getArrayForColorSetting($showData, $userFlg);
            $arrRankSouJissi = CodeUtil::getRankOfArray($arrSouJissi);
            $arrRankTougetu = CodeUtil::getRankOfArray($arrTougetu);
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
                'arrRankSouJissi',
                'arrRankTougetu',
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
        $search = $requestObj->all();
        // 検索値を登録(セッション)
        SessionUtil::putSearch( $search );

        $showData = null;
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"点検年月");
        // チェック問題がない場合、データを取得
        if ($check == Constants::CONS_OK ) {
            $showData = $this->dispatch(new SumUserCommand($search, $requestObj, "tenken"));
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
        $arrRankSouJissi = $arrRankTougetu = $arrRankJissi = $arrRankShoKaiLogin = array();
        if($showData != null && !$showData->isEmpty()){
            // 列の色設定
            List($arrSouJissi, $arrTougetu, $arrJissi, $arrShoKaiLogin ) = $this->getArrayForColorSetting($showData, $userFlg);
            $arrRankSouJissi = CodeUtil::getRankOfArray($arrSouJissi);
            $arrRankTougetu = CodeUtil::getRankOfArray($arrTougetu);
            $arrRankJissi = CodeUtil::getRankOfArray($arrJissi);
            $arrRankShoKaiLogin = CodeUtil::getRankOfArray($arrShoKaiLogin);
        }

        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'isStaffPage',
                'userFlg',
                'arrRankSouJissi',
                'arrRankTougetu',
                'arrRankJissi',
                'arrRankShoKaiLogin'
            )
        )
        ->with( "title", $this->title )
        ->with( 'displayObj', $this->displayObj )
        ->withErrors($validator);
    }

    /**
     * 対象配列の値を取得する。
     * @param array $showData 表示データ
     * @param bool $userFlg ユーザーフラグ
     * @return array $data
     */
    public function getArrayForColorSetting($showData, $userFlg)
    {
        $arrSouJissi = array();
        $arrTougetu = array();
        $arrJissi = array();
        $arrShoKaiLogin = array();

        foreach ($showData as $key => $value) {
            // 法人営業部拠点とＵＳ広瀬拠点を除く、法人営業部は75のコードを変換する。
            //拠点のみ場合
            if (!$userFlg && in_array($value->base_code, array('70', '75', ''))) {
                continue;
            }

            $num = $value->senkou_jissi_count + $value->tougetu_jissi_count + $value->tougetu_keika_jissi_count;

            // 総実施率
            // 20200316 add check senkou_jissi_count
            if ($num > 0 && $value->target_count > 0 ) {
                // 20220315: update 総実施率
                //array_push($arrSouJissi, round($num / $value->target_count * 100, 1));
                array_push($arrSouJissi, round($num / $value->target_count * 100, 1));
            } else {
                array_push($arrSouJissi, 0);
            }

            // 当月対象実施率
            if ($value->target_count > 0 ) {
                // 20220315: Update 当月対象実施率
                //array_push($arrTougetu, round($value->tougetu_jissi_count / $value->target_count * 100, 1));
                array_push($arrTougetu, round($value->tougetu_taisyo_jissi_count / $value->target_count * 100, 1));
            } else {
                array_push($arrTougetu, 0);
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
        sort($arrSouJissi);
        sort($arrTougetu);
        sort($arrJissi);
        sort($arrShoKaiLogin);
        return array($arrSouJissi, $arrTougetu, $arrJissi, $arrShoKaiLogin);
    }
}
