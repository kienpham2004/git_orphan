<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Append\Tmr;
use App\Original\Util\CodeUtil;

/**
 * TMR CSV用データ
 *
 * @author yhatsutori
 *
 */
class TmrData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    private $_cols = [
        3     => 'tmr_base_code_init',          // 拠点CD
        5     => 'tmr_user_code_init',          // 担当者CD
        7     => 'tmr_customer_code',           // 顧客CD
        8     => 'tmr_custmer_name',            // 顧客氏名
        24    => 'tmr_manage_number',		// 統合車輌管理番号
        50  => 'tmr_syaken_next_date',	// 次回車検日
        82  => 'tmr_last_process_status',     // 最終処理状況
        83  => 'tmr_last_call_result',        // 最終架電結果
        84  => 'tmr_call_times',              // 架電回数
        86  => 'tmr_last_call_date',// 最終架電日
        92  => 'tmr_to_base_comment',         // 拠点への申送事項
        104 => 'tmr_in_sub_intention',        // 入庫/代替意向
        105 => 'tmr_in_sub_detail',           // 入庫/代替詳細
        138 => 'tmr_call_1_status',           // 1コール目処理状況
        139 => 'tmr_call_1_result',           // 1コール目架電結果
        140 => 'tmr_call_1_date',             // 1コール目架電日
        146 => 'tmr_call_2_status',           // 2コール目処理状況
        147 => 'tmr_call_2_result',           // 2コール目架電結果
        148 => 'tmr_call_2_date',             // 2コール目架電日
        154 => 'tmr_call_3_status',           // 3コール目処理状況
        155 => 'tmr_call_3_result',           // 3コール目架電結果
        156 => 'tmr_call_3_date',             // 3コール目架電日
        162 => 'tmr_call_4_status',           // 4コール目処理状況
        163 => 'tmr_call_4_result',           // 4コール目架電結果
        164 => 'tmr_call_4_date',             // 4コール目架電日
        170 => 'tmr_call_5_status',           // 5コール目処理状況
        171 => 'tmr_call_5_result',           // 5コール目架電結果
        172 => 'tmr_call_5_date',             // 5コール目架電日
        178 => 'tmr_call_6_status',           // 6コール目処理状況
        179 => 'tmr_call_6_result',           // 6コール目架電結果
        180 => 'tmr_call_6_date'              // 6コール目架電日

    ];
    
    private $_validate_rules = [
        'tmr_manage_number' => 'required',      // 統合車輌管理番号
        //'tmr_syaken_next_date' => 'required'	// 次回点検日
        'tmr_last_call_date' => 'required'	// 最終架電日
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
    public function store( $storeData ){
        // 拠点
        $storeData['tmr_base_code_init'] = $this->convertBaseCode( $storeData['tmr_base_code_init'] );
        // 担当者
        $storeData['tmr_user_code_init'] = $this->convertUserId( $storeData['tmr_user_code_init'] );
        $storeData['tmr_user_id'] = CodeUtil::getUserIdByCode( $storeData['tmr_user_code_init'] );


        $storeData['tmr_manage_number'] = $this->convertCarManageNumber( $storeData['tmr_manage_number'] );
        $storeData['tmr_customer_code'] = $this->convertCustomerCode( $storeData['tmr_customer_code'] );
        
        //YY/MM/DD形式の日付データをYYYY/MM/DD形式に変換
        $storeData['tmr_syaken_next_date'] = $this->convertData( $storeData['tmr_syaken_next_date'] );
        $storeData['tmr_last_call_date'] = $this->convertData( $storeData['tmr_last_call_date'] );
        $storeData['tmr_call_1_date'] = $this->convertData( $storeData['tmr_call_1_date'] );
        $storeData['tmr_call_2_date'] = $this->convertData( $storeData['tmr_call_2_date'] );
        $storeData['tmr_call_3_date'] = $this->convertData( $storeData['tmr_call_3_date'] );
        $storeData['tmr_call_4_date'] = $this->convertData( $storeData['tmr_call_4_date'] );
        $storeData['tmr_call_5_date'] = $this->convertData( $storeData['tmr_call_5_date'] );
        $storeData['tmr_call_6_date'] = $this->convertData( $storeData['tmr_call_6_date'] );
        
        // データ登録
        try{
            // 統合車両管理Noの値があるときにmerge
            if( isset( $storeData['tmr_manage_number'] ) == True && !empty( $storeData['tmr_manage_number'] ) == True ){
                Tmr::merge($storeData);
            }
            
        }catch( \Exception $e ){
                throw new \Exception($e);
//            $e->getMessage();
//
//            info('---------------- '.'Tmr::merge');
//            info('発生場所 ------- '.__METHOD__);
//            info('クラス名 ------- '.__CLASS__);
//            info('エラー内容 ----- '."\n".$e->getMessage()."\n");
        }
        
    }

    /**
     * CSVカラム定義を返送する
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
     * CSV項目数を返送する
     *
     * @return number
     */
    public function getItemNum() {
        return 209;
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
