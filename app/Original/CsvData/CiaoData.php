<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Append\Ciao;
use App\Original\Util\CodeUtil;

/**
 * チャオ用CSVデータ
 *
 * @author yhatsutori
 *
 */
class CiaoData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    private $_cols = [
        3 => 'ciao_base_code_init',         // 拠点コード
        4 => 'ciao_base_name',              // 拠点屋号名称
        5 => 'ciao_user_code_init',         // 営業コード
        6 => 'ciao_user_name',              // 担当営業
        7 => 'ciao_customer_code',          // 顧客コード
        8 => 'ciao_customer_name',          // お客様名
        //9 => 'ciao_car_manage_number',    // 統合車両管理ＮＯ
        9 => 'ciao_car_name',               // 車名コード
        10 => 'ciao_car_base_number_min',   // ＊車両基本登録No(番号のみ)
        11 => 'ciao_car_base_number',       // ＊車両基本登録No
        12 => 'ciao_number',                // 申込番号
        13 => 'ciao_course',                // 加入コース
        14 => 'ciao_first_regist_date_ym',  // 初度登録年月日
        15 => 'ciao_syaken_manryo_date',    // 車検起算日
        16 => 'ciao_syaken_next_date',      // 車検満了日
        17 => 'ciao_money',                 // 加入金額
        18 => 'ciao_start_date',            // 会員証有効期間始期
        19 => 'ciao_end_date',              // 会員証有効期間終期

        40 => 'ciao_jisshi_type',           // 最終点検種別
        41 => 'ciao_jisshi_yotei',          // 最終点検実施予定
        42 => 'ciao_jisshi',                // 最終点検実施
        43 => 'ciao_jisshi_flg',            // 実施フラグ
        44 => 'ciao_course_keizoku',        // 継続加入継続加入コース
        45 => 'ciao_kaiinsyou_hakkou_date'  // 継続加入会員証発行日
    ];

    private $_validate_rules = [
        //'ciao_car_manage_number' => 'required',
        //'ciao_customer_code' => 'required',
        //'ciao_car_base_number' => 'required',
        'ciao_number' => 'required',
        'ciao_end_date' => 'required'
    ];
    
    /**
     * CSV外の値を埋め込む
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
    public function store( $storeData ) {
        // 拠点
        $storeData['ciao_base_code_init'] = $this->convertBaseCode( $storeData['ciao_base_code_init'] );
        // 担当者
        $storeData['ciao_user_code_init'] = $this->convertUserId( $storeData['ciao_user_code_init'] );
        $storeData['ciao_user_id'] = CodeUtil::getUserIdByCode($storeData['ciao_user_code_init'] );


        $storeData['ciao_customer_code'] = $this->convertCustomerCode( $storeData['ciao_customer_code'] );
        
        $storeData['ciao_jisshi'] = $this->convertCiaoJissi( $storeData['ciao_jisshi'] );
        $storeData['ciao_car_base_number'] = $this->filterCarNumberSpace( $storeData['ciao_car_base_number'] );
        
        // 日付が正しいかを確認
        if( 19700101 == intval( date('Ymd', strtotime( $storeData['ciao_first_regist_date_ym'] ) ) ) ){
            $storeData['ciao_first_regist_date_ym'] = "1970/01/01";
        }
        
        //　データ登録
        try{
            Ciao::merge( $storeData );
            
        }catch( \Exception $e ){
            throw new \Exception($e);
//            $e->getMessage();
//
//            info('---------------- '.'Pit::merge');
//            info('発生場所 ------- '.__METHOD__);
//            info('クラス名 ------- '.__CLASS__);
//            info('エラー内容 ----- '."\n".$e->getMessage()."\n");
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
     */
    public function getValidateRules() {
        return $this->_validate_rules;
    }

    /**
     * CSVの項目数を返送する
     *
     * @return number
     */
    public function getItemNum() {
        return 45;
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
