<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Lib\Util\DateUtil;
use App\Models\Append\SmartPro;
use App\Original\Util\CodeUtil;

/**
 * スマートプロ査定 CSV用データ
 * @author yhatsutori
 *
 */
class SmartProData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    private $_cols = [
        2 => 'smart_base_code_init',           // 初期拠点コード
        3 => 'smart_user_name_csv',                 // 担当者氏名(漢字)
        5 => 'smart_day',                       // 査定日
        18 => 'smart_car_base_number',          // 登録番号
        24 => 'smart_nintei_type',              // 認定型式
        26 => 'smart_car_num',                  // 車台番号
        34 => 'smart_syaken_yuukou_day',        // 車検有効期間
        65 => 'smart_client_name',              // 依頼者氏名
        66 => 'smart_syoyuusya_name',           // 所有者氏名
        67 => 'smart_siyousya_name',            // 使用者氏名
        69 => 'smart_nego_kbn',                 // 商談区分
        70 => 'smart_nego_cartype',             // 商談車種
        117 => 'smart_irai_datetime',           // 依頼日時
        118 => 'smart_irai_comment',            // 依頼コメント
        120 => 'smart_user_code_init'           // 初期担当者コード
    ];

    private $_validate_rules = [
        'smart_car_base_number' => 'required',  // 登録番号
        'smart_nintei_type' => 'required',      // 認定型式
        'smart_day' => 'required'               // 査定日
    ];
    



    /**
     * CSV外の値を埋め込む
     *
     * @param unknown $storeData
     * @param unknown $value
     * @return unknown
     */
    public function inject($storeData, $value) {
        return $storeData;
    }
    /**
     * DBへの登録更新処理を行う
     *
     * @param unknown $storeData
     */
    public function store( $storeData ) {
        $storeData['smart_base_code_init'] = $this->convertBaseCode( $storeData['smart_base_code_init'] );

        $storeData['smart_user_code_init'] = $this->convertUserId( $storeData['smart_user_code_init'] );
        $storeData['smart_user_id'] = CodeUtil::getUserIdByCode( $storeData['smart_user_code_init'] );

        $storeData['smart_day'] = DateUtil::toTimestamp( $storeData['smart_day'] );
        $storeData['smart_car_base_number'] = $this->filterCarNumberSpace( $storeData['smart_car_base_number'] );

        // 統合車両管理Noの値があるときにmerge
        if( isset( $storeData['smart_car_base_number'] ) == True && !empty( $storeData['smart_car_base_number'] ) == True ){
            // 型式の値があるときにmerge
            if( isset( $storeData['smart_nintei_type'] ) == True && !empty( $storeData['smart_nintei_type'] ) == True ){
                // 次回車検日の値が不明でない時
                if( $storeData['smart_syaken_yuukou_day'] != "----/--/--" ){
                    //　データ登録
                    try{
                        SmartPro::merge( $storeData );

                    }catch( \Exception $e ){
                        throw new \Exception($e);
//                        $e->getMessage();
//
//                        info('---------------- '.'SmartPro::merge');
//                        info('発生場所 ------- '.__METHOD__);
//                        info('クラス名 ------- '.__CLASS__);
//                        info('エラー内容 ----- '."\n".$e->getMessage()."\n");
                    }
                }
            }
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
        return 121;
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
