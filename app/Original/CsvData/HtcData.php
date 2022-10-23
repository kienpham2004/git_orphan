<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Append\Htc;
use DB;
use App\Original\Codes\Insurance\HtcMemberRegistStatus;
use App\Original\Codes\Insurance\HtcLoginStatus;

/**
 * 代替車検推進管理用CSVデータ
 *
 * @author ルック
 *
 */
class HtcData{

    use tCsvImport;
    public static $fistImportRecord = false; // 初期行目導入
    public  $_arrLogMessage; // システムログ用
    public $_functionName = 'HOT客管理CSVデータ';

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    protected $_cols = [
        1 =>  'htc_number',               // HTC会員番号
        2 =>  'htc_customer_number',      // 顧客No.
        3 =>  'htc_customer_name',        // お客様名
        4 =>  'htc_syadai_number',        // 車台番号
        5 =>  'htc_model_name',           // 車種名
        6 =>  'htc_appication_date',      // HTC申込日
        7 =>  'htc_car_regist_date',      // HTC車両登録日
        8 =>  'htc_my_dealer',            // Myディーラー
        9 =>  'htc_member_regist_status', // HTC会員サイト登録状況
        10 => 'htc_not_regist_reson',     // 登録不要理由
        11 => 'htc_login_status',         // HTC会員サイトお客様初回ログイン状況
        12 => 'htc_user',                 // 担当営業
    ];

    /**
     * バリデーションルール
     */
    private $_validate_rules = [
        'htc_number' => 'required',          // HTC会員番号
        'htc_customer_number' => 'required', // 顧客No.
    ];

    /**
     * 項目名リスト
     */
    private $_column_name = [
        
    ];

    /**
     * CSV外の値を埋め込む
     * 画面の選択値とうを埋め込む
     *
     * @param unknown $storeData
     * @param unknown $value
     * @return unknown
     */
    public function inject( $storeData, $value ){
        return $storeData;
    }

    /**
     * DBへの登録更新処理を行う
     *
     * @param unknown $storeData
     */
    public function store( $storeData ){

        $storeData['htc_member_regist_status'] = (new HtcMemberRegistStatus())->getCode(trim($storeData['htc_member_regist_status']));
        $storeData['htc_login_status'] = (new HtcLoginStatus())->getCode(trim($storeData['htc_login_status']));

        //　データ登録
        try {
            Htc::merge($storeData);
        }catch( \Exception $e ){
            throw new \Exception($e);
        }
    }

    /**
     * CSVカラムの定義を返送する
     *
     * @return string[]
     */
    public function getColumns() {
        return $this->_cols;
    }

    /**
     * バリデーションルールを返送する
     *
     * @return string[]
     */
    public function getValidateRules() {
        return $this->_validate_rules;
    }

    /**
     * 項目名を返送する
     *
     * @return string[]
     */
    public function getColumnName() {
        return $this->_column_name;
    }

    /**
     * CSV項目数を返送する
     *
     * @return number
     */
    public function getItemNum() {
        return 12;
    }
    
    /*
     * エラーログ用にクラス名を返送する
     * 
     * @return string
     */
    public function getClassName() {
        $check_target = __CLASS__;
        $check_place  = strrpos($check_target, '\\') + 1;
        $class_name = substr($check_target, $check_place);
        
        return $class_name;
    }
}
