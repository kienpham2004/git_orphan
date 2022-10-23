<?php

namespace App\Http\Controllers;

use App\Lib\Util\DateUtil;
use App\Lib\Codes\RowNumCodes;
use App\Original\Util\SessionUtil;
use App\Http\Requests\SearchRequest;
use App\Models\UserAccount;
use Request;
use App\Lib\Util\Constants;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Session;

/**
 * 検索画面用初期化処理トレイト
 * 検索処理の関数(検索・ソート)
 *
 * 【概要】
 * getSearchParamsやgetSortParamsを呼ぶと共通となっている
 * 初期化処理を行います。
 * またextendSearchParamsやextendSortParamsをオーバーライドすることで
 * 画面内固有の初期化処理を行うことができます。
 *
 * 【使用方法】
 * useしたクラス内でextendSearchParamsやextendSortParamsを実装してください。
 *
 * 【注意事項】
 * extendSearchParamsでは追加されていきますが、
 * extendSortParamsは名前通り上書きます。
 *
 * 【例】
 *  public function extendSearchParams() {
 *      return ['account_level' => 3, 'base_code' => '01'];
 *  }
 *
 *  public function extendSortParams() {
 *      return ['account_level' => 'asc'];
 *  }
 *
 */
trait tInitSearch {

    protected $search;
    protected $sort;

//    private static  $targetMinData = "";
//    private static  $targetMaxData = "";

    #######################
    ## 検索部分のメソッド
    #######################

    /**
     * 検索部分のデフォルト値を指定
     * ※継承先で主に定義
     * @return [type] [description]
     */
    public function extendSearchParams() {
        return array();
    }

    /**
     * 検索部分の値を格納した配列を取得
     */
    public function getSearchParams() {
        // 検索部分の値を格納する配列
        // ※継承先で定義された検索値を取得
        $this->search = $this->extendSearchParams();

        // デフォルトの表示件数を指定
        $this->search['row_num'] = RowNumCodes::getDefault();

        return $this->search;
    }

    #######################
    ## 並び順のメソッド
    #######################

    /**
     * 並び順のデフォルト値を指定
     * ※継承先で主に定義
     * @return [type] [description]
     */
    public function extendSortParams() {
        return array();
    }

    /**
     * @return unknown[]
     */
    public function getSortParams() {
        // 並び順の値を格納する配列
        // ※継承先で定義された検索値を取得
        $customSortLink = $this->extendSortParams();

        // 継承先の並び順を取得
        if( !empty( $customSortLink ) == True ) {
            $this->sort['sort'] = $customSortLink;

        }else{
            // デフォルトの並び順を取得
            $this->sort = $this->initSortValue();
        }

        return $this->sort;
    }

    #######################
    ## 検索用のメソッド
    #######################

    /**
     * カレント日付のYM値をデフォルト選択値とする
     *
     * @return string
     */
    public function selectedYm() {
        return DateUtil::currentYm();
    }

	/**
     * カレント日付をデフォルト選択値とする
     *
     * @return string
     */
    public function selectedYmd() {
        return DateUtil::currentDay();
    }
    
    /**
     *
     */
    public function selectedUserId() {
        // ユーザー情報を取得(セッション)
        $loginAccountObj = SessionUtil::getUser();

        return $loginAccountObj->defaultSelectedUserId();
    }

    /**
     *
     */
    public function selectedBaseCode() {
        // ユーザー情報を取得(セッション)
        $loginAccountObj = SessionUtil::getUser();

        return $loginAccountObj->defaultSelectedBaseCode();
    }

    /**
     *
     */
    public function selectedUserName() {
        // ユーザー情報を取得(セッション)
        $loginAccountObj = SessionUtil::getUser();

        return $loginAccountObj->getUserName();
    }

    #######################
    ## 並び替え用のメソッド
    #######################

    /**
     * デフォルトの並び順を取得
     * @param  array  $sortValues [description]
     * @return [type]           [description]
     */
    public function initSortValue( $sortValues = array() ){
        // 並び順を削除(セッション)
        SessionUtil::removeSort();

        // 並び替えのデフォルト値を取得
        if( empty( $sortValues ) == True ){
            $sortValues = ['id' => 'asc'];
        }

        // 並び替え情報を取得
        $sort = ['sort' => $sortValues];

        return $sort;
    }

    /**
     * 並び順を取得(セッション)
     * @return [type] [description]
     */
    public function getSortValue() {
        // 並び順を取得(セッション)
        $sort = SessionUtil::getSort();

        return $sort;
    }

    /**
     * 並び替え情報を登録して取得(セッション)
     * @return [type] [description]
     */
    public static function setSortValue() {
        //
        $sort = array();

        // 検索条件を取得
        $query = Request::query();

        // 並び順のキーと値を指定
        if( isset( $query['sort_key'] ) == True && isset( $query['sort_by'] ) == True ) {
            $sort['sort'][$query['sort_key']] = $query['sort_by'];

        }else{
            // 並び順を取得(セッション)
            $sort = SessionUtil::getSort();

        }

        // 並び順を登録(セッション)
        SessionUtil::putSort( $sort );

        return $sort;
    }

    #######################
    ## Controller method
    #######################

    #######################
    ## indexの処理
    #######################

    /**
    * Index
    * @return デフォルト画面に画面遷移
    */
    public function getIndex() {
        // 検索情報と並び替え情報を削除
        SessionUtil::removeSession();

        // search画面に画面遷移
        return redirect( action( $this->displayObj->ctl . '@getSearch' ) );
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
        echo "None output page.";
    }

    /**
     * 検索部分を操作時の処理
     * @param  SearchRequest $requestObj [description]
     * @return [type]                    [description]
     */
    public function getSearch( SearchRequest $requestObj ){
        // 並び順が格納されている配列を取得
        $sort = $this->getSortParams();
        // 検索の値を取得
        $search = $requestObj->all();

        // 検索の値が空の時
        if( empty( $search ) == True ){
            SessionUtil::removeSearch();
            // 検索部分の値を格納した配列を取得
            $search = $this->getSearchParams();
            // リクエストオブジェクトを取得
            $requestObj = SearchRequest::getInstance( $search );
        }

        // 検索値を登録(セッション)
        SessionUtil::putSearch( $search );
        // 並び順を登録(セッション)
        SessionUtil::putSort( $sort );

        // 一覧画面のデータを表示
        return $this->showListData( $search, $sort, $requestObj );
    }

    /**
     * ペジネートの処理
     * @return [type] [description]
     */
    public function getPager() {
        // 検索値を取得(セッション)
        $search = SessionUtil::getSearch();

        // 並び順を取得(セッション)
        $sort = $this->getSortValue();

        // リクエストオブジェクトを取得
        $requestObj = SearchRequest::getInstance( $search );

        // 一覧画面のデータを表示
        return $this->showListData( $search, $sort, $requestObj );
    }

    /**
     * ソートの処理
     * @return [type] [description]
     */
    public function getSort() {
        // 検索値を取得(セッション)
        $search = SessionUtil::getSearch();

        // 並び替え情報を登録して取得(セッション)
        $sort = $this->setSortValue();

        // リクエストオブジェクトを取得
        $requestObj = SearchRequest::getInstance( $search );

        // 一覧画面のデータを表示
        return $this->showListData( $search, $sort, $requestObj );
    }

    /**
     * 選択された拠点に属するスタッフを取得するメソッド
     * @param  string $base_code 拠点コード
     * @param  string $type user_code | user_id
     * @return json スタッフ一覧
     */
    public function postStaffEachBase( $base_code='', $type = 'code' ){
        // 拠点コードが空の時は、ログインしているユーザの拠点コードを取得する
        // 店長、営業担当者、工場長の時だけ動く
        if( empty( $base_code ) ){
            $loginAccountObj = SessionUtil::getUser();
            
            // 管理者、本社、部長以外はデフォルトの拠点コードを取得
            if ( !in_array( $loginAccountObj->getRolePriority(), [1, 2, 3] ) ){
                $base_code = $loginAccountObj->defaultSelectedBaseCode();
            }
        }
        $accoutList = null;
        if ($type == "code") {
            $accoutList = UserAccount::staffOptions( $base_code );
        }else{
            $accoutList = UserAccount::staffOptionsId( $base_code );
        }

        // 退職者を取得する。
        $deletedAccountList = UserAccount::staffOptionsDeleted( $base_code );
        if (!$deletedAccountList->isEmpty()){
            array_add($accoutList, Constants::CONS_TAISHOKUSHA_CODE ,"退職者");
        }
        return $accoutList;
    }

    /**
     * 入力チェック
     * @param $validator 戻り値エラー
     * @param $search チェックオブジェクト
     * @param $itemName 対象項目名
     * @return NULL | validator
     */
    public static function checkValidate(&$validator, $search, $yearMonthName = ""){
        // 必須チェックを行う
        $validator = null;
        $validator = App::make('App\Http\Requests\ValidateRequest');
        $validator = $validator->validate($search, $yearMonthName);
        if ($validator->fails()) {
            SessionUtil::putValidation(true);
            return Constants::CONS_ERROR;
        }
        SessionUtil::removeValidation();
        return Constants::CONS_OK;
    }

    /**
     * データ範囲を取得する
     * @param $tableName 対象テーブル
     * @param $fieldName 対象項目
     * @param $minData 戻り値最初から月数
     * @param $maxData 戻り最後から月数
     */
    public static function getMaxMinData($tableName, $fieldName, &$minData, &$maxData){
        $minData = 0;
        $maxData = 0;
        $today = date("Y-m-d");
        //最初データの月
        $sql = "SELECT MIN($fieldName) FROM $tableName ";
        $data = \DB::select( $sql );
        if( !empty($data)){
            $dateMin = $data[0]-> min;
            if($tableName == Constants::TB_CUSTOMER) {
                $minData = self::getMonthsTwoDate($dateMin, $today);
            } elseif($fieldName == Constants::TGC_CUSTOMER_INSURANCE_END_DATE ){
                $minData = self::getMonthsTwoDate($dateMin, $today);
            }else{
                $minData = self::getMonthsTwoDate(date($dateMin . "01"), $today);
            }
        }
        //最大データの月
        $sql = "SELECT MAX($fieldName) FROM $tableName ";
        $data = \DB::select( $sql );
        if( !empty($data)){
            $dateMax = $data[0]-> max;
            if($tableName == Constants::TB_CUSTOMER){
                $maxData = self::getMonthsTwoDate($today, $dateMax);
            }elseif( $fieldName == Constants::TGC_CUSTOMER_INSURANCE_END_DATE ){
                $maxYear = substr($dateMax, 0, 4);
                $currentYear = date('Y', strtotime($today)) ;
                // 将来3年間設定
                if ($maxYear > $currentYear + 3 ) {
                    $dateMax = date('Y-m-d', strtotime('+3 years'));
                }
                $maxData = self::getMonthsTwoDate($today, $dateMax);
            }else{
                $maxData = self::getMonthsTwoDate($today, date($dateMax . "01"));
            }
        }
    }

    /**
     * 保険の自社・他社・新規の満期年月を取得
     * @param $minData 戻り値最初から月数
     * @param $maxData 戻り最後から月数
     * @param $where 取得の条件
     */
    public static function getInsuranceContactMinData(&$minData, &$maxData, $where = ""){
        $minData = 0;
        $maxData = 0;
        $today = date("Y-m-d");
        //最初データの月
        $sql = "SELECT	MIN(CASE WHEN insu_contact_keijyo_ym IS NOT NULL THEN insu_contact_keijyo_ym 
			                ELSE insu_inspection_ym END)
                FROM tb_insurance
                $where ";
        $data = \DB::select( $sql );
        if( !empty($data)){
            $dateMin = $data[0]-> min;
            $minData = self::getMonthsTwoDate(date($dateMin . "01"), $today);
        }
        //最大データの月
        $sql = "SELECT	MAX(CASE WHEN insu_contact_keijyo_ym IS NOT NULL THEN insu_contact_keijyo_ym 
			                ELSE insu_inspection_ym END)
                FROM tb_insurance
                $where ";
        $data = \DB::select( $sql );
        if( !empty($data)){
            $dateMax = $data[0]-> max;
            $maxData = self::getMonthsTwoDate($today, date($dateMax . "01"));
        }
    }

    /**
     * チェック同じ画面の処理を行る
     * @param $screenId 画面Id
     */
    public static function checkCurrentScreen($screenId){
        $currentID = SessionUtil::get(Constants::SEC_PROCESS_SCREEN_ID);
        if(!isset($currentID) || (isset($currentID) && $currentID != $screenId )){
            SessionUtil::removeValidation();
            SessionUtil::put(Constants::SEC_PROCESS_SCREEN_ID, $screenId);
        }
    }

    /**
     * 月数を取得する
     * @param $date1 日付１
     * @param $date2 日付２
     */
    private static function getMonthsTwoDate($date1, $date2){
        $ts1 = strtotime($date1);
        $ts2 = strtotime($date2);

        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);

        return (($year2 - $year1) * 12) + ($month2 - $month1);
    }
}
