<?php

namespace App\Original\Util;

// コード
use App\Lib\Codes\CheckCodes;
// ○×
use App\Lib\Codes\Code;
use App\Lib\Codes\FinishedCodes;
use App\Lib\Codes\MaruBatsuCodes;
use App\Lib\Util\Constants;
use App\Models\Base;
use App\Models\UserAccount;
use App\Original\Codes\IkouLatestCodes;
use App\Original\Codes\MaruCodes;

// モーダル関連
use App\Original\Codes\Modal\ActionCodes;
// 保険関連
use App\Original\Codes\Inspect\InspectInsuTypes;

// 保険関連
use App\Original\Codes\Insurance\InsuStatusCodes;
use App\Original\Codes\Insurance\InsuActionCodes;
use App\Original\Codes\Insurance\InsuSyaryoCodes;
use App\Original\Codes\Insurance\InsuJiguCodes;
use App\Original\Codes\Insurance\InsuContactTaisyoCodes;
use App\Original\Codes\Insurance\InsuStatusGensenCodes;
use App\Original\Codes\Insurance\InsuNaiyouCodes;

// クレジット関連
use App\Original\Codes\Inspect\InspectCreditTypes;
use App\Original\Codes\Inspect\InspectCreditTextToTexts;

// Intent
use App\Original\Codes\Intent\IntentSyatenkenCodes;
use App\Original\Codes\Intent\IntentCreditCodes;
use App\Original\Codes\Intent\IntentInsuranceCodes;
use App\Original\Codes\Intent\IntentNousyaCodes;
use App\Original\Codes\Intent\IntentKeisanHousikiCodes;
use App\Original\Codes\Intent\IntentKojinHojinCodes;

// CSV
use App\Original\Codes\CsvDirCodes;
use App\Original\Codes\CsvJudgeCodes;
use App\Original\Codes\CsvImportCodes;
use App\Original\Codes\CsvModelCountCodes;
use App\Original\Codes\Inspect\InspectDmTypes;

use App\Original\Codes\TenkenIkouLatestCodes;
use DateTime;
use phpDocumentor\Reflection\Types\Integer;
use DB;
use ZipArchive;

/**
 * エイリアスに登録しています
 */
class CodeUtil {

    static $hasUser = [], $hasBase = [];
    /**
     * 車点検の意向結果を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]    ※return string　新旧コード混在の為、新コードでの表示用＠20180912
     */
    public static function getIntentSyatenkenName( $code, $default='' ) {
        $name = ( new IntentSyatenkenCodes() )->getValue( $code );
        if( !empty( $name ) ) {
            if( $code == '11' || $code == '12' || $code == '13' || $code == '14' || $code == '15' || $code == '16' || $code == '17' || $code == '20' ){
                return $name;
            }
        }

        return $default;
    }

    /**
     * クレジットの意向結果を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getIntentCreditName( $code, $default='' ) {
        $name = ( new IntentCreditCodes() )->getValue( $code );
        if( !empty( $name ) ) {
            return $name;
        }

        return $default;
    }

    /**
     * 保険の意向結果を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getIntentInsuranceName( $code, $default='' ) {
        $name = ( new IntentInsuranceCodes() )->getValue( $code );
        if( !empty( $name ) ) {
            return $name;
        }

        return $default;
    }

    /**
     * 納車の意向結果を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getIntentNousyaName( $code, $default='' ) {
        $name = ( new IntentNousyaCodes() )->getValue( $code );
        if( !empty( $name ) ) {
            return $name;
        }

        return $default;
    }

    /**
     * 指定された意向結果(保険)を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getInsuStatusName($code, $default='')
    {
        $name = (new InsuStatusCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }

    /**
     * 指定された活動名を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getActCodeName($code, $default='')
    {
        $name = (new ActionCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }
    
    public static function getInsuType($code, $default='')
    {
        $name = (new InspectInsuTypes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $code;
        //return $default;
    }

    public static function getCreditType($code, $default='')
    {
        $name = (new InspectCreditTypes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $code;
        //return $default;
    }

    public static function getCreditTextToText($code, $default='')
    {
        $name = (new InspectCreditTextToTexts())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $code;
        //return $default;
    }
    
    public static function getKeisanHousikiCode($code, $default='')
    {
        $name = (new IntentKeisanHousikiCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $code;
        //return $default;
    }
    
    public static function getKojinHojinCode($code, $default='')
    {
        $name = (new IntentKojinHojinCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $code;
        //return $default;
    }
    
    
    public static function getMaruBatsuType($code, $default='')
    {
        $name = (new MaruBatsuCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }

    /**
     * 指定された○を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getMaruType($code, $default='')
    {
        $name = (new MaruCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }

    /**
     * 指定された有無を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getCheckType($code, $default='')
    {
        $name = (new CheckCodes())->getValue($code);
        
        if( !empty($code) || $code == 0 ){
            return $name;
        }
        
        return $default;
    }
    
    /**
     * 指定された活動内容(保険)を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getInsuActionType($code, $default='')
    {
        $name = (new InsuActionCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }
    
    /**
     * 指定された車両(保険)を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getInsuSyaryoType($code, $default='')
    {
        $name = (new InsuSyaryoCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }

    /**
     * 指定された治具(保険)を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getInsuJiguType($code, $default='')
    {
        $name = (new InsuJiguCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }

    /**
     * 指定された接触対象(保険)を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getInsuContactTaisyoType($code, $default='')
    {
        $name = (new InsuContactTaisyoCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }

    /**
     * 指定された獲得源泉(保険)を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getInsuStatusGensen($code, $default='')
    {
        $name = (new InsuStatusGensenCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }

    /**
     * 指定された処理内容を取得
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getInsuNaiyouCodes($code, $default='')
    {
        $name = (new InsuNaiyouCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }
    
    /**
     * 指定したcsvファイルのディレクトリを取得する
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getCsvDirCode( $code, $default='' )
    {
        $name = (new CsvDirCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }

    /**
     * 指定したcsvファイルのディレクトリの一覧を配列で取得する
     * @return [type] [description]
     */
    public static function getCsvDirCodes()
    {
        return (new CsvDirCodes())->getOptions();
    }
    
    /**
     * 指定したcsvファイルの名称を取得する
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getCsvImportName( $code, $default='該当なし' )
    {
        $name = (new CsvImportCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }
    
    /**
     * 指定したcsvファイル名の一覧を配列で取得する
     * @return [type] [description]
     */
    public static function getCsvImportCodes()
    {
        return (new CsvImportCodes())->getOptions();
    }
    
    /**
     * 指定したcsvファイルのDBの総数を取得する
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getCsvModelCount( $code, $default='' )
    {
        $name = (new CsvModelCountCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }

    /**
     * DMを含めた、車点検区分名を取得する
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getInspectDmTypeName( $code, $default='' )
    {
        $name = (new InspectDmTypes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }
        
        return $default;
    }

    /**
     * 指定したcsvファイルの名称を取得する
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getJudgeCsvtName( $code, $default='該当なし' )
    {
        $name = (new CsvJudgeCodes())->getValue($code);
        if (! empty($code)) {
            return $name;
        }

        return $default;
    }

    /**
     * CSV更新の状況チェック
     * @param $code
     */
    public static function checkUpdateStatus($code){

        // ファイルを格納するディレクトリ名を取得
        $csvDirName = CodeUtil::getCsvDirCode( $code );
        // コピー先のディレクトリのパス
        $checkDir = storage_path() . '/upload/' . $csvDirName;

        $countFiles = 0;
        //データのファイルの一覧を取得
        $cntFiles = scandir( $checkDir );

        // ファイル数が空でない時に処理
        if( !empty( $cntFiles ) == True ){
            foreach( $cntFiles as $key => $value ) {
                // 一部ファイルを除外
                if( strpos( $value, "heck_",0) == 1  ){
                    // 除外ファイル以外を配列に格納
                    $countFiles = 1;
                    break;
                }
            }
        }
        return $countFiles;
    }

    /**
     * チャオコースとチャオ会員終期の表示のチェックを行る。
     * @param $ciaoCourse チャオコース
     * @param $syakenNextDate　	次回車検日
     * @param $ciaoEndDate　チャオ会員終期
     * @param $typeReturn 1: チャオコース 2: チャオ会員終期
     * @return 表示戻り値
     */
    public static function displayCiaoCourseAndCiaoEndDate($ciaoCourse, $syakenNextDate, $ciaoEndDate, $typeReturn)
    {
        //戻り値値設定
        $result = $ciaoCourse;
        if ($typeReturn != 1) {
            $result = $ciaoEndDate;
        }

        // すべて値は空無しの場合
        if (! empty($ciaoCourse) && ! empty($syakenNextDate)  && ! empty($ciaoEndDate) ) {
            $nextDate = new DateTime($syakenNextDate);
            $endDate = new DateTime($ciaoEndDate);

            //■ciao_course == 'SS'　の場合
            //　・syaken_next_date < ciao_end_date　→　表示
            if( $ciaoCourse == 'SS' &&  ! ($nextDate < $endDate)) {
                $result = "";
            }
            //■ciao_course != 'SS'　の場合
            //　・syaken_next_date <= ciao_end_date　→　表示
            if( $ciaoCourse != 'SS' &&  ! ($nextDate <= $endDate)) {
                $result = "";
            }
        }
        return $result;
    }

    /**
     * 翌月の年月から指定する。
     * @param $fromYYYYMM　年月から
     * @param $MonthNumber　翌月数
     * @param $returnType　1: ('YYYYMM','YYYYMM')  2:YYYYMM,YYYYMM
     * @return string 翌月の年月の指摘
     */
    public static function getNextYearMonth($fromYYYYMM, $monthNumber, $returnType){

        $next_syaken_ym = array();
        $yy = substr( $fromYYYYMM, 0, 4 );
        $mm = substr( $fromYYYYMM, 4, 2 );
        for($i=1;$i<=$monthNumber;$i++){
            //指定した年月の次の月を取得
            $mm += $i;
            if( $mm >= 13 ){
                $yy++;
                $mm = '1';
            }
            $next_syaken_ym[] = $yy . substr( '0'. $mm, -2 );
        }
        if ($returnType == 1 ) {
            return "('" . implode( "','", $next_syaken_ym ) . "')";
        } else {
            return  implode( ",", $next_syaken_ym );
        }
    }

    /**
     * データ集計
     * @param $data 集計データ
     * @param $initTotal　戻り値初期オブジェクト
     * @param string $checkKey　チェックキー
     * @param string $checkValue　チェック値
     * @return object
     */
    public static function getTotalOfAllRecord( $data, $initTotal, $checkKey = "", $checkValue = "" )
    {
        // データ無しの場合
        if ($data->isEmpty()){
            return NULL;
        }

        $arrField= array();
        foreach ($initTotal as $k => $v) {
            array_push($arrField,$k);
        }
        // 拠点数と値を代入
        foreach( $data as $listKey => $listValue ){
            // チェックキー１がある場合With
            if ($checkKey != "" && $listValue->{$checkKey} != $checkValue) {
                continue;
            }
            // 値の配列の中身の確認
            foreach ( $listValue as $key => $value ) {
                // キーとなる値は除外
                if( !in_array( $key, $arrField) ){
                    // キーがなければ追加
                    if( !isset( $initTotal[$key] ) == True ){
                        $initTotal[$key] = 0;
                    }
                    if (is_numeric($value)) {
                        $initTotal[$key] += $value;
                    }
                }
            }
        }
        return  (object)$initTotal;
    }

    /**
     * 店長, 工場長, 担当営業限でログイン時に表示
     * @param $loginInfo ログイン情報
     * @param $baseCode 拠点コード
     * @param $userId 担当者コード
     * @return bool
     */
    public static function getEditFlag($loginInfo, $baseCode, $userId)
    {
        // 編集できるフラグをoffにする
        $editFlg = False;

        // 権限により、挙動を変更
        if( in_array( $loginInfo->getRolePriority(), [1] ) == True ){
            // mut管理の時はok
            $editFlg = True;
        }elseif( in_array( $loginInfo->getRolePriority(), [4,5] ) == True && $loginInfo->getBaseCode() == $baseCode ){
            // 店長権限で拠点が合致する時＋工場長権限も同様（20180911）
            $editFlg = True;
         //}elseif( in_array( $loginInfo->getRolePriority(), [5] ) == True &&
            // ( "S" . $loginInfo->getBaseCode() == $value->tgc_user_id || $loginInfo->getUserId() == $value->tgc_user_id ) ){
            // 工場長権限で担当者または、サービス管理が合致する時
            //$editFlg = True;
        }elseif( in_array( $loginInfo->getRolePriority(), [6] ) == True && $loginInfo->getUserId() == $userId ){
            // 営業担当者権限で担当者が合致する時
            $editFlg = True;
        }
        return $editFlg;
    }

    /**
     * 担当者権限チェック
     * @return bool
     */
    public static function checkTantousyaPriority(){
        $loginInfo = SessionUtil::getUser();
        //担当者の権限
        if($loginInfo->getRolePriority() == Constants::P06){
            return true;
        }
        return false;
    }

    /**
     * マスター権限チェック
     * @return bool
     */
    public static function checkMasterPriority(){
        $loginInfo = SessionUtil::getUser();
        //管理者の権限
        if($loginInfo->getRolePriority() == Constants::P01){
            return true;
        }
        return false;
    }

    /**
     * 店長と工場長の権限チェック
     * @param string $baseCode 行の拠点
     * @return bool
     */
    public static function checkTenchoAndKouzyochoPriority($baseCode = "",$userId = ""){
        $loginInfo = SessionUtil::getUser();
        $loginBaseCode = $loginInfo->getBaseCode();
        $loginUserID = $loginInfo->getUserId();

        // 店長と工場長の自拠点
        if($baseCode != "" &&
            in_array( $loginInfo->getRolePriority(), [Constants::P04, Constants::P05]) &&
            $baseCode == $loginBaseCode){
            return true;
        }
        // 店長と工場長の権限
        elseif($baseCode == "" &&
            in_array( $loginInfo->getRolePriority(), [Constants::P04, Constants::P05])){
            return true;
        }
        // 営業担当の権限
        elseif($baseCode == $loginBaseCode &&  $userId == $loginUserID &&
                $loginInfo->getRolePriority() == Constants::P06){
            return true;
        }
        return false;
    }

    /**
     * 管理権限チェック
     * @return bool
     */
    public static function checkKanriPriority(){
        $loginInfo = SessionUtil::getUser();
        //管理者と部長と本社の権限
        if(in_array( $loginInfo->getRolePriority(), [Constants::P01, Constants::P02, Constants::P03] )){
            return true;
        }
        return false;
    }


    /**
     * 自拠点チェック
     * @param string $flg チェックケース
     * @return bool
     */
    public static function checkJikyoTen($flg = "1"){
        $loginInfo = SessionUtil::getUser();

        // 自拠点チェック、店長以降自拠点。
        //if($flg == "1" && in_array( $loginInfo->getRolePriority(), [Constants::P04, Constants::P05, Constants::P06, Constants::P07] )){
        if($flg == "1" && in_array( $loginInfo->getRolePriority(), [Constants::P06, Constants::P07] )){
            return true;
        }
        // 担当者画面の無効項目チェック
        elseif($flg == "2"){
            // 営業担当とサービスの権限は検索項目が入力するのは無効です。
            if (in_array( $loginInfo->getRolePriority(), [Constants::P06, Constants::P07] )){
                return false;
            }
            return true;
        }elseif($flg == "3" &&
            in_array( $loginInfo->getRolePriority(), [Constants::P04, Constants::P05, Constants::P06, Constants::P07] )){
                return true;
        }

//        elseif($flg != "1" && in_array( $loginInfo->getRolePriority(), [Constants::P06, Constants::P07] )){
//            return true;
//        }
        return false;
    }

    /**
     * 権限を取得する
     * @param $screenID　画面ID
     * @param string $baseCode 行の拠点
     * @param string $userId 　行のユーザー
     * @return int 0：権限なし、1：表示と編集、2:表示のみ
     */
    public static function getPermision($screenID, $baseId = "", $userId = "")
    {
        $result = 0; //表示と編集の権限
        // 権限を調べ為の値を取得
        $loginInfo = SessionUtil::getUser();
        $loginBaseId = $loginInfo->getBaseId();
        $loginUserID = $loginInfo->getUserId();
        // ログインユーザー権限とデータ権限の比較
        $checkLevel = self::checkAccountLevel($loginInfo, $userId);

        // 部長、本社の画面
        $screenBuchoArr1 = array(Constants::R00, Constants::R10, Constants::J00, Constants::J10,
            Constants::B00, Constants::H00, Constants::H10, Constants::H30);
        $screenBuchoArr2 = array(Constants::R01, Constants::R10, Constants::J01, Constants::J11, Constants::H01, Constants::H11);

        // 店長の画面
        $screenTenchoArr1 = array(Constants::R00, Constants::R10, Constants::J00, Constants::J10,
            Constants::B00, Constants::H00, Constants::H01, Constants::H30);
        $screenTenchoArr2 = array(Constants::R01, Constants::R10, Constants::J01, Constants::J11, Constants::H01, Constants::H10, Constants::H11);

        // 工場長、サービスの画面
        $screenKochoArr1 = array(Constants::R00, Constants::R10, Constants::J00, Constants::J10,
            Constants::B00, Constants::H00, Constants::H10, Constants::H30);
        $screenKochoArr2 = array(Constants::R01, Constants::R10, Constants::J01, Constants::J11, Constants::H01, Constants::H10, Constants::H11);

        // 営業担当の画面
        $screenEigyoArr1 = array(Constants::R00, Constants::R10, Constants::J00, Constants::J10,
            Constants::B00, Constants::H00, Constants::H10, Constants::H30);
        $screenEigyoArr2 = array(Constants::R01, Constants::R10, Constants::J01, Constants::J11, Constants::H01, Constants::H10, Constants::H11);

        // SCの画面
        $screenScArr1 = array(Constants::R00, Constants::R10, Constants::J00, Constants::J10,
            Constants::B00, Constants::H00, Constants::H10, Constants::H30);
        $screenScArr2 = array(Constants::R01, Constants::R10, Constants::J01, Constants::J11, Constants::H01, Constants::H10, Constants::H11);


        // 管理者 -----------------------------------------------
        if ($loginInfo->getRolePriority() == Constants::P01) {
            $result = 1;
        }
        // 部長、本社 --------------------------------------------
        elseif (in_array($loginInfo->getRolePriority(), [Constants::P02, Constants::P03]) == True) {
            // トレジャーボード画面と車検、点検リスト画面
            if (in_array($screenID, $screenBuchoArr1)) {
                $result = 1;//表示と編集
            } elseif (in_array($screenID, $screenBuchoArr2)) {
                $result = 2; //表示のみ

            } elseif ($screenID == Constants::M20 && $checkLevel == true) {
                $result = 1; //修正
            }
        }
        //elseif( in_array( $loginInfo->getRolePriority(), [Constants::P03 ] ) == True ){ // 本社→部長の同じ
        //    $result = 0;
        //}
        // 店長 --------------------------------------------------
        elseif ($loginInfo->getRolePriority() == Constants::P04) {
            //自拠点
            if (in_array($screenID, $screenTenchoArr1) && $baseId == $loginBaseId) {
                $result = 1;//表示と編集
            }
            //自拠点
            elseif (in_array($screenID, $screenTenchoArr2)) {
                $result = 2;// 自拠点違いの場合、表示
                if($baseId == $loginBaseId) {
                    $result = 1; //表示と編集
                }
            }
            //担当者画面
            elseif ($screenID == Constants::M20 && $checkLevel == true) {
                $result = 1; //修正
            }
        }
        // 工場長、サービス ----------------------------------------
        elseif( $loginInfo->getRolePriority() == Constants::P05 ){
            //自拠点
            if (in_array($screenID, $screenKochoArr1) && $baseId == $loginBaseId) {
                $result = 1;//表示と編集
            }
            //自拠点　且つ　担当者
            elseif (in_array($screenID, $screenKochoArr2) && $baseId == $loginBaseId ) {
                $result = 2; // 自拠点違いの場合、表示
                if ( $loginUserID == $userId){
                    $result = 1; //表示と編集
                }
            }
            //担当者画面
            elseif ($screenID == Constants::M20 && $checkLevel == true) {
                $result = 1; //表示のみ
            }
        }
        // 営業担当 -----------------------------------------------
        elseif( $loginInfo->getRolePriority() == Constants::P06 ){
            //自拠点
            if (in_array($screenID, $screenEigyoArr1) && $baseId == $loginBaseId) {
                $result = 1;//表示と編集
            }
            //自拠点　且つ　担当者
            elseif (in_array($screenID, $screenEigyoArr2) && $baseId == $loginBaseId ) {
                $result = 2;//担当者違いの場合、表示
                if ( $loginUserID == $userId){
                    $result = 1; //表示と編集
                }
            }
            //担当者画面
            elseif ($screenID == Constants::M20) {
                $result = 2; //表示のみ
            }
        }
        // SC -----------------------------------------------
        elseif( $loginInfo->getRolePriority() == Constants::P07 ){
            //自拠点
            if (in_array($screenID, $screenScArr1) && $baseId == $loginBaseId) {
                $result = 1;//表示と編集
            }
            //自拠点　且つ　担当者
            elseif (in_array($screenID, $screenScArr2) && $baseId == $loginBaseId ) {
                $result = 2;//担当者違いの場合、表示
                if ( $loginUserID == $userId){
                    $result = 1; //表示と編集
                }
            }
            //担当者画面
            elseif ($screenID == Constants::M20) {
                $result = 2; //表示のみ
            }
        }
        return $result;
    }


    /**
     * 権限のチェックを行う
     * @param $loginInfo ログイン情報
     * @param $userId　データの担当者
     * @return bool
     */
    private static function checkAccountLevel($loginInfo, $rowUserId){
        if ($rowUserId == ""){
            return false;
        }

        // セッションから情報を取得する。
        $permisionList = SessionUtil::get(Constants::SEC_ACCOUT_PERMISION);

        if (!isset($permisionList[$rowUserId])){
            return false;
        }

        $rowLevel = $permisionList[$rowUserId];         // データのレベル
        $loginLevel = $loginInfo->getRolePriority(); // ログインのレベル

        // 管理者(１) <- 部長(２) <- 本社(３) <- 店長(４) <- 工場長/サービス(５) <- 営業担当(６) <- CS(７)
        // 管理者の場合
        if($loginLevel == Constants::P01){
            return true; // 管理者
        }
//        // 部長、本社（自権限）
//        elseif(($loginLevel == Constants::P02 ||$loginLevel == Constants::P03) &&
//            $rowUserId == $loginInfo->getUserId()){
//            return true; // 部長、本社
//        }
//        // 部長、本社のログインの上権限と同権限の場合
//        elseif(($loginLevel == Constants::P02 ||$loginLevel == Constants::P03) &&
//            $userId != $loginInfo->getUserId() && $rowLevel <= $loginLevel ){
//            return false;
//        }
        elseif (($loginLevel >= Constants::P02 ) && $rowLevel >= $loginLevel){
            return true; //
        }
        return false;
    }

    /**
     * リストに存在のIDをチェックする
     * @param $data リストデータ
     * @param $Id 対象
     * @return bool
     */
    public static function checkExistInList($data, $id)
    {
        foreach( $data as $baseNum => $baseData){
            if($baseNum == $id){
                return true;
            }
        }
        return false;
    }

    /**
     * collectの形から
     * [$colKey => $colVal]のような形のarrayを作成
     * @param $table
     * @param $colKey
     * @param $colVal
     * @return array
     */
    public function pluck($table, $colKey, $colVal)
    {
        $collect = collect(
            DB::table($table)
                ->whereNull('deleted_at')
                ->select($colKey, $colVal)
                ->groupBy('id')->get())->toArray();
        $pluck = [];
        foreach ($collect as $v) {
            $pluck[$v->$colKey] = $v->$colVal;
        }
        return $pluck;
    }

    /**
     * 担当者IDを取得する。
     * @param $userCode
     * @param $userAcc
     * @return string
     */
    public static function getUserIdByCode($userCode) {
        // 空文字の場合
        if ($userCode == "")
            return null;

        if ( count(CodeUtil::$hasUser) == 0 ) {
            $codeUtil = new CodeUtil();
            CodeUtil::$hasUser = $codeUtil->pluck('tb_user_account', 'user_code', 'id');
        }
        if (array_key_exists($userCode, CodeUtil::$hasUser)){
            return CodeUtil::$hasUser[$userCode];
        }
        return null;
    }

    /**
     * 拠点IDを取得する。
     * @param $userCode
     * @param $base
     * @return string
     */
    public static function getBaseIdByCode($baseCode) {
        // 空文字の場合
        if ($baseCode == "")
            return "";

        if ( count(CodeUtil::$hasBase) > 0 ) {
            $codeUtil = new CodeUtil();
            CodeUtil::$hasBase = $codeUtil->pluck('tb_base', 'base_code', 'id');
        }

        if (array_key_exists($baseCode, CodeUtil::$hasBase)){
            return CodeUtil::$hasBase[$baseCode];
        }
        return null;
    }

    /**
     * バッチダブル実行の防止
     * @param $checkFlag チェックと削除フラグ
     * @param $filePath　対象ファイル
     * @param $type
     * @return bool　チェック結果
     */
    public static function doubleRunCheck($checkFlag, $filePath, $type=null) {
        try{
            // チェックの場合
            if ($checkFlag) {
                if (file_exists($filePath)) {
                    //重複の場合には、メール送信
                    if ( $type != null ) {
                        $stage_title = config('original.title');
                        // 開始時間
                        $start_time = date('Y/m/d H:i:s');
                        $subject = 'WARNING - ' . $type . ': ' . $stage_title . ' ' .  "重複のお知らせ";
                        $body = '開始時間 ：' . $start_time . "\n"
                            . $filePath . ' ' . '重複のファイルがあります。';
                        CodeUtil::sendMail($subject, $body, $stage_title);
                    }
                    return true;
                }
                echo date('Y-m-d H:i:s') ." - 作成フィル : " . $filePath . PHP_EOL;
                $handle = fopen($filePath, 'a') or die('Cannot open file:  '.$filePath);
                fwrite($handle, date("Y-m-d H:i:s"));
                fclose($handle);

            }else{ // 仮ファイル削除の場合
                echo date('Y-m-d H:i:s') ." - 削除フィル : " . $filePath . PHP_EOL;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        } catch (\Exception $ex) {
            echo date('Y-m-d H:i:s') ." - doubleRunCheck : " . $ex . PHP_EOL;
        }
    }

    /**
     * ２つの日時を比較
     * @param datetime|string    $date_1   終了時間
     * @param datetime|string    $date_2   開始時間
     * @param string  [表示フォーマット]
     * @return string 処理時間（日時の差分）
     */
    public static function dateDifference($date_1 , $date_2 , $differenceFormat = '%s' )
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);

        $interval = date_diff($datetime1, $datetime2);

        return $interval->format($differenceFormat);
    }

    /**
     * メール送信
     * @param $from
     * @param $subject
     * @param $body
     */
    public static function sendMail($subject, $body, $from)
    {
        mutSendMail(
            config('original.mail_to'),
            $subject,
            $body,
            config('original.mail_from'),
            $from
        );
    }

    /**
     * ファイル圧縮
     * @param $compress_file　圧縮ファイルパス
     * @param $originalDel 元ファイル削除デフォルトtrue
     */
    public static function zipFile($compress_file, $originalDel = true)
    {
        try {
            $file = $compress_file . '.zip';

            // 圧縮・解凍するためのオブジェクト生成
            $zip = new ZipArchive();

            $result = $zip->open($file, ZipArchive::CREATE);
            if ($result === true) {
                $zip->addFile($compress_file, pathinfo($compress_file, PATHINFO_BASENAME));
                $zip->close();// ファイルを生成
            }

            if ($originalDel) {
                unlink($compress_file); // 元ファイル削除
            }

        } catch (\Exception $ex) {
            \Log::error($ex);
        }
    }

    /**
     * フォルダの圧縮
     * @param $dir 対象フォルダ
     * @param string $fileExt 対象ファイル
     * @param $originalDel 元ファイル削除デフォルトtrue
     */
    public static function zipAllFileInFolder($dir, $fileExt = "csv", $originalDel = true)
    {
        try {
            // フォルダ存在チェック
            if (file_exists($dir) && is_dir($dir)) {

                // Get the files of the directory as an array
                $scan_arr = scandir($dir);
                $files_arr = array_diff($scan_arr, array('.', '..'));

                // Get each files of our directory with line break
                foreach ($files_arr as $file) {

                    $file_path = $dir . '/' . $file;// ファイルパス取得
                    $file_ext = pathinfo($file_path, PATHINFO_EXTENSION); // ファイル拡張
                    $file_name = pathinfo($file_path, PATHINFO_BASENAME); // ファイル名

                    // 対象ファイルの拡張とチェックファイル以外の場合
                    if (strtolower($file_ext) == $fileExt && strpos($file_name, "heck_", 0) != 1) {
                        CodeUtil::zipFile($file_path, $originalDel); // 対象ファイルの圧縮
                    }

                }
            }
        } catch (\Exception $ex) {
            \Log::error($ex);
        }
    }

    /**
     * 有無フラグ取得
     * @param $value
     * @return bool
     */
    public static function getUmuFlag($value)
    {
        // 値設定なし
        if (!isset($value)) {
            return 3;
        }
        $flagYes = false;
        $flagNo = false;
        foreach ($value as $v) {
            if ($v != '1') {
                $flagNo = true; // なし
            } elseif ($v == '1') {
                $flagYes = true; // 有り
            }
        }

        if ($flagNo == true && $flagYes == false) {
            return 0; // 無し
        } elseif ($flagNo == false && $flagYes == true) {
            return 1; // 有り
        }
        return 3;
    }

    /**
     * 最大値を取得
     * @param $value
     */
    public static function getMaxDate($date1, $date2, $date3, $date4, $date5, $date6,$date7=null ){
        if($date7 != null){
            $dateArr = array($date1, $date2 ,$date3 ,$date4 ,$date5 ,$date6,$date7 );
        }else{
            $dateArr = array($date1, $date2 ,$date3 ,$date4 ,$date5 ,$date6 );
        }
        return max($dateArr);
    }

    /**
     * HTCログインフラグ取得
     * @param string $code
     * @param string $default
     * @return mixed|string
     */
    public static function getFinishedType($code, $default = '未')
    {
        $name = (new FinishedCodes())->getValue($code);
        if (!empty($code)) {
            return $name;
        }

        return $default;
    }

    /**
     * get name of TenkenIkouLatestCodes
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getTenkenIkouLatestName( $code, $default='' )
    {
        $name = (new TenkenIkouLatestCodes())->getValue($code);
        if (!empty($code)) {
            return $name;
        }

        return $default;
    }

    /**
     * get name of IkouLatestCodes
     * @param  [type] $code    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getIkouLatestName( $code, $default='' )
    {
        $name = (new IkouLatestCodes())->getValue($code);
        if (!empty($code)){
            return $name;
        }

        return $default;
    }

    /**
     * get name of IkouLatestCodes
     * @param  [type] $name    [description]
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public static function getIkouLatestCode( $name, $default='' )
    {
        $code = (new IkouLatestCodes())->getCode($name);
        if (!empty($code)){
            return $code;
        }

        return $default;
    }

    /**
     * 配列を分ける。
     * @param array $numbers
     * @return array
     */
    public static function splitArray($numbers)
    {
        $arrTmp1 = array();
        $arrTmp2 = array();

        $arrlength = count($numbers);
        for ($x = 0; $x < $arrlength; $x++) {
            $avarage = (min($numbers) + max($numbers)) / 2;
            if ($numbers[$x] < $avarage)
                array_push($arrTmp1, $numbers[$x]);
            else
                array_push($arrTmp2, $numbers[$x]);
        }
        return array($arrTmp1, $arrTmp2);
    }

    /**
     * 集計表示の色を設定のため、配列のランキングを区別する。
     * @param array $numbers
     * @return array
     */
    public static function getRankOfArray($numbers)
    {
        if (empty($numbers)) {
            return array(0);
        }
        $average = round((min($numbers) + max($numbers)) / 2, 1); // 平均
        $arrResult = array($average);
        array_push($arrResult, min($numbers)); // 一番低い拠点
        array_push($arrResult, max($numbers)); // 一番高い拠点

        list ($arr1, $arr2) = CodeUtil::splitArray($numbers);

        if (!empty($arr1) && count($arr1) > 1) {
            $average = round((min($arr1) + max($arr1)) / 2, 1);
            array_push($arrResult, $average); // 低い中間拠点
        } elseif (!empty($arr1)) {
            array_push($arrResult, round(($average + max($arr1)) / 2, 1)); // 低い中間拠点
        }

        if (!empty($arr2) && count($arr2) > 1) {
            $average = round((min($arr2) + max($arr2)) / 2, 1);
            array_push($arrResult, $average); // 高い中間の拠点
        } elseif (!empty($arr2)) {
            array_push($arrResult, round(($average + max($arr2)) / 2, 1)); // 高い中間の拠点
        }

        sort($arrResult);

        return $arrResult;
    }

    /**
     * 集計表示の色を設定のため、配列のランキングを区別する。
     * @param array $arr
     * @param mixed $val
     * @return string
     */
    public static function getValueOfColorRank($arr, $val)
    {
        // 平均値調整設定
        $check = max($arr) - min($arr);
        if ($check >= 90 && $check <= 100) {
            $heikin = 15;
        } elseif ($check >= 80 && $check <= 90) {
            $heikin = 13;
        } elseif ($check >= 70 && $check <= 80) {
            $heikin = 11;
        } elseif ($check >= 60 && $check <= 70) {
            $heikin = 9;
        } elseif ($check >= 50 && $check <= 60) {
            $heikin = 7;
        } elseif ($check >= 40 && $check <= 50) {
            $heikin = 5.5;
        } elseif ($check >= 30 && $check <= 40) {
            $heikin = 4;
        } elseif ($check >= 20 && $check <= 29) {
            $heikin = 2.5;
        } elseif ($check >= 10 && $check <= 20) {
            $heikin = 1;
        } else {
            $heikin = 0.3;
        }

        //minとmaxが違う
        if ($check > 1) {
            if ($val <= $arr[0])
                $type = 0; // 一番低い
            elseif ($val > $arr[0] && $val <= $arr[1])
                $type = 1; // 低い中間1
            elseif ($val > $arr[1] && $val < $arr[2] - $heikin)
                $type = 2; // 低い中間2
            elseif ($val >= $arr[2] - $heikin && $val <= $arr[2] + $heikin)
                $type = 3; // 平均
            elseif ($val > $arr[2] + $heikin && $val <= $arr[3])
                $type = 4; // 高い中間1
            elseif ($val > $arr[3] && $val < $arr[4])
                $type = 5; // 高い中間２
            else
                $type = 6; // 一番高い
        } else {
            //minとmaxが同じ
            if ($val < 50) {
                $type = 0;
            } else {
                $type = 6;
            }
        }

        if ($type == 0) {
            $rankCss = "cellColor__cell-red3";
        } elseif ($type == 1) {
            $rankCss = "cellColor__cell-red2";
        } elseif ($type == 2) {
            $rankCss = "cellColor__cell-red1";
        } elseif ($type == 3) {
            $rankCss = "cellColor__cell-white";
        } elseif ($type == 4) {
            $rankCss = "cellColor__cell-blue1";
        } elseif ($type == 5) {
            $rankCss = "cellColor__cell-blue2";
        } else {
            $rankCss = "cellColor__cell-blue3";
        }

        return $rankCss;
    }

    /**
     * ログファイル圧縮
     * @param $compress_file　圧縮ファイルパス
     * @param $originalDel 元ファイル削除デフォルトtrue
     */
    public static function zipFileLog($compress_file)
    {
        try {
            $file = $compress_file . '.zip';

            // 圧縮・解凍するためのオブジェクト生成
            $zip = new ZipArchive();

            $result = $zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($result === true) {
                $zip->addFile($compress_file, pathinfo($compress_file, PATHINFO_BASENAME));
                $zip->close();// ファイルを生成
            }

        } catch (\Exception $ex) {
            \Log::error($ex);
        }
    }
 }
