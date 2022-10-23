<?php

namespace App\Http\Controllers\Hoken;

use App\Original\Util\CodeUtil;
use App\Original\Util\SessionUtil;
use App\Http\Requests\SearchRequest;
use App\Commands\Hoken\Result\SumBaseCommand;
use App\Commands\Hoken\Result\SumUserCommand;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;
use App\Lib\Util\DateUtil;
use App\Lib\Util\Constants;

/**
  * 実績分析の車点検別のコントローラー
 */
class HokenResultController extends Controller {
    
    use tInitSearch;
    
    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
    }
    
    /**
     * 検索部分のデフォルト値を指定
     * @return [type] [description]
     */
    public function extendSearchParams(){
        // 検索の値を格納する配列
        $search = array();

        // 検索値のセッションの確認
        if ( SessionUtil::hasSearch() == True ){
            // 検索項目を保存したセッションを取得
            $search = SessionUtil::getSearch();

            // セッションのinspectoin_ym_fromが空ならデフォルトを設定
            if ( empty( $search['inspection_ym_from'] ) ) {
                $search['inspection_ym_from'] = $this->selectedYm();
            }

        }else{
            // ユーザー情報を取得(セッション)
            $loginAccountObj = SessionUtil::getUser();

            // 店長以下の権限の時
            if ( ! in_array( $loginAccountObj->getRolePriority(), [1,2,3] ) ) {
                $search['base_code'] = $this->selectedBaseCode();
                // 初回の時のフラグ
                $search['first_flg'] = 1;
            }
            // 対象月
            $search['inspection_ym_from'] = $this->selectedYm();

        }

        return $search;
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
        $this->displayObj->page = "hoken_result";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Hoken\HokenResultController";
        //　同じ処理画面チェック
        tInitSearch::checkCurrentScreen(Constants::H20);
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

        // セッションが存在ないの場合
        if(!SessionUtil::has(Constants::SEC_INSURANCE_RESULT_DATA_MIN)) {
            $minInsData = 0;
            $maxInsData = 0;
            tInitSearch::getInsuranceContactMinData($minInsData, $maxInsData);
            // 対象データのセッションに格納する
            SessionUtil::put(Constants::SEC_INSURANCE_RESULT_DATA_MIN, $minInsData);
            SessionUtil::put(Constants::SEC_INSURANCE_RESULT_DATA_MAX, $maxInsData);
        }


        $showJisyaData = null;
        $showTasyaData = null;
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"満期年月");
        // チェック問題がない場合、担当者毎のデータを取得
        if ($check == Constants::CONS_OK ) {
            // 店長以下で、初回の時のフラグがある時は、拠点の詳細を表示
            if (isset($search["first_flg"]) == True && $search["first_flg"] == "1") {
                // 自社
                list($showJisyaData, $plan_value) = $this->dispatch(new SumUserCommand($search, $requestObj, "jisya"));
                // 他社
                list($showTasyaData, $plan_value) = $this->dispatch(new SumUserCommand($search, $requestObj, "tasya"));
            } else {
                // 自社
                list($showJisyaData, $plan_value) = $this->dispatch(new SumBaseCommand($search, $requestObj, "jisya"));
                // 他社
                list($showTasyaData, $plan_value) = $this->dispatch(new SumBaseCommand($search, $requestObj, "tasya"));
            }
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

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
        $screenID = Constants::H20;

        // 列の色設定
        $arrRankKeizoku = $arrRank30mae = $arrRankHss = $arrRankSyaryo = $arrRankDaisya = array();
        if($showJisyaData != null && !$showJisyaData->isEmpty()){
            List($arrKeizoku, $arr30mae, $arrHss, $arrSyaryo, $arrDaisya ) = $this->getArrayForColorSettingJisya($showJisyaData, $userFlg);
            $arrRankKeizoku = CodeUtil::getRankOfArray($arrKeizoku);
            $arrRank30mae = CodeUtil::getRankOfArray($arr30mae);
            $arrRankHss = CodeUtil::getRankOfArray($arrHss);
            $arrRankSyaryo = CodeUtil::getRankOfArray($arrSyaryo);
            $arrRankDaisya = CodeUtil::getRankOfArray($arrDaisya);
        }
        $arrRankTasyaHss = $arrRankTasyaDaisya = array();
        if($showTasyaData != null && !$showTasyaData->isEmpty()){
            List($arrTasyaHss, $arrTasyaDaisya ) = $this->getArrayForColorSettingTasya($showTasyaData, $userFlg);
            $arrRankTasyaHss = CodeUtil::getRankOfArray($arrTasyaHss);
            $arrRankTasyaDaisya = CodeUtil::getRankOfArray($arrTasyaDaisya);
        }

        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showJisyaData',
                'showTasyaData',
                'plan_value',
                'runTime',
                'screenID',
                'userFlg',
                'arrRankKeizoku',
                'arrRank30mae',
                'arrRankHss',
                'arrRankSyaryo',
                'arrRankDaisya',
                'arrRankTasyaHss',
                'arrRankTasyaDaisya'
            )
        )
        ->with( "title", "拠点・担当者別 保険集計表" )
        ->with( 'displayObj', $this->displayObj )
        ->withErrors($validator);
    }
        
    /**
     * 実績分析の各画面で担当者ごとに表示
     * @param  SearchRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function getEachStaff( SearchRequest $requestObj, $userFlg = False ) {
        $search = $requestObj->all();
        // 検索値を登録(セッション)
        SessionUtil::putSearch( $search );

        $showJisyaData = null;
        $showTasyaData = null;
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"満期年月");
        // チェック問題がない場合、担当者毎のデータを取得
        if ($check == Constants::CONS_OK ) {
            //自社データを取得
            list($showJisyaData, $plan_value) = $this->dispatch(new SumUserCommand($search, $requestObj, "jisya"));
            //他社データを取得
            list($showTasyaData, $plan_value) = $this->dispatch(new SumUserCommand($search, $requestObj, "tasya"));
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

        $screenID = Constants::H21;

        // 列の色設定
        $arrRankKeizoku = $arrRank30mae = $arrRankHss = $arrRankSyaryo = $arrRankDaisya = array();
        if($showJisyaData != null && !$showJisyaData->isEmpty()){
            List($arrKeizoku, $arr30mae, $arrHss, $arrSyaryo, $arrDaisya ) = $this->getArrayForColorSettingJisya($showJisyaData, $userFlg);
            $arrRankKeizoku = CodeUtil::getRankOfArray($arrKeizoku);
            $arrRank30mae = CodeUtil::getRankOfArray($arr30mae);
            $arrRankHss = CodeUtil::getRankOfArray($arrHss);
            $arrRankSyaryo = CodeUtil::getRankOfArray($arrSyaryo);
            $arrRankDaisya = CodeUtil::getRankOfArray($arrDaisya);
        }
        $arrRankTasyaHss = $arrRankTasyaDaisya = array();
        if($showTasyaData != null && !$showTasyaData->isEmpty()){
            List($arrTasyaHss, $arrTasyaDaisya ) = $this->getArrayForColorSettingTasya($showTasyaData, $userFlg);
            $arrRankTasyaHss = CodeUtil::getRankOfArray($arrTasyaHss);
            $arrRankTasyaDaisya = CodeUtil::getRankOfArray($arrTasyaDaisya);
        }

        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showJisyaData',
                'showTasyaData',
                'plan_value',
                'screenID',
                'userFlg',
                'arrRankKeizoku',
                'arrRank30mae',
                'arrRankHss',
                'arrRankSyaryo',
                'arrRankDaisya',
                'arrRankTasyaHss',
                'arrRankTasyaDaisya'
            )
        )
        ->with( "title", "拠点・担当者別 保険集計表" )
        ->with( 'displayObj', $this->displayObj )
        ->withErrors($validator);
    }

    /**
     * 対象配列の値を取得する。
     * @param array $showJisyaData 表示データ
     * @param bool $userFlg ユーザーフラグ
     * @return array $data
     */
    public function getArrayForColorSettingJisya($showJisyaData, $userFlg)
    {
        $arrKeizoku = array();
        $arr30mae = array();
        $arrHss = array();
        $arrSyaryo = array();
        $arrDaisya = array();

        foreach ($showJisyaData as $key => $value) {
            // 法人営業部拠点とＵＳ広瀬拠点を除く、法人営業部は75のコードを変換する。
            //拠点のみ場合
            if (!$userFlg && in_array($value->base_code, array('70', '75', ''))) {
                continue;
            }

            // 継続率
            $num = $value->status_21_count + $value->status_22_count;
            if ($num > 0 && $value->target_count > 0) {
                array_push($arrKeizoku, round($num / $value->target_count * 100, 1));
            } else {
                array_push($arrKeizoku, 0);
            }

            // 30日前継続率
            if ($value->status_30mae_count == 0 || $num == 0) {
                array_push($arr30mae, 0);
            } elseif ($value->status_30mae_count == $num) {
                array_push($arr30mae, 100);
            } else {
                array_push($arr30mae, round($value->status_30mae_count / $num * 100, 1));
            }

            // HSS活用率
            if ($value->status_hss_count == 0 || $num == 0) {
                array_push($arrHss, 0);
            } elseif ($value->status_hss_count == $num) {
                array_push($arrHss, 100);
            } else {
                array_push($arrHss, round($value->status_hss_count / $num * 100, 1));
            }

            // 車両保険付帯率
            if ($value->status_syaryo_count == 0 || $num == 0) {
                array_push($arrSyaryo, 0);
            } elseif ($value->status_syaryo_count == $num) {
                array_push($arrSyaryo, 100);
            } else {
                array_push($arrSyaryo, round($value->status_syaryo_count / $num * 100, 1));
            }

            // 代車特約付帯率
            if ($value->status_daisya_count == 0 || $num == 0) {
                array_push($arrDaisya, 0);
            } elseif ($value->status_daisya_count == $num) {
                array_push($arrDaisya, 100);
            } else {
                array_push($arrDaisya, round($value->status_daisya_count / $num * 100, 1));
            }
        }
        sort($arrKeizoku);
        sort($arr30mae);
        sort($arrHss);
        sort($arrSyaryo);
        sort($arrDaisya);
        return array($arrKeizoku, $arr30mae, $arrHss, $arrSyaryo, $arrDaisya);
    }

    /**
     * 対象配列の値を取得する。
     * @param array $showTasyaData 表示データ
     * @param bool $userFlg ユーザーフラグ
     * @return array $data
     */
    public function getArrayForColorSettingTasya($showTasyaData, $userFlg)
    {
        $arrTasyaHss = array();
        $arrTasyaDaisya = array();

        foreach ($showTasyaData as $key => $value) {
            // 法人営業部拠点とＵＳ広瀬拠点を除く、法人営業部は75のコードを変換する。
            //拠点のみ場合
            if (!$userFlg && in_array($value->base_code, array('70', '75', ''))) {
                continue;
            }

            // HSS活用率
            if ($value->status_hss_count == 0 || $value->status_6_count == 0) {
                array_push($arrTasyaHss, 0);
            } elseif ($value->status_hss_count == $value->status_6_count) {
                array_push($arrTasyaHss, 100);
            } else {
                array_push($arrTasyaHss, round($value->status_hss_count / $value->status_6_count * 100, 1));
            }

            // 代車特約付帯率
            if ($value->status_daisya_count > 0 || $value->status_6_count > 0) {
                array_push($arrTasyaDaisya, round($value->status_daisya_count / $value->status_6_count * 100, 1));
            } else {
                array_push($arrTasyaDaisya, 0);
            }
        }
        sort($arrTasyaHss);
        sort($arrTasyaDaisya);
        return array($arrTasyaHss, $arrTasyaDaisya);
    }
}
