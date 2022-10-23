<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\SyakenJisshi;
use App\Original\Util\CodeUtil;

/**
 * 車検実施リスト用CSVデータ
 *
 * @author daidv
 *
 */
class SyakenJisshiData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    protected $_cols = [
        1 => 'sj_base_code_init',// 拠点 コード
        3 => 'sj_user_code_init',// 営業担当者 コード
        6 => 'sj_customer_code',// 顧客No.（IF）
        9 => 'sj_car_manage_number',// 統合車両管理No. IF
        14 => 'sj_syaken_next_date',// 車検満了日
        17 => 'sj_shukka_date',// 出荷報告日
    ];

    /**
     * バリデーションルール
     */
    private $_validate_rules = [
        'sj_car_manage_number' => 'required'
    ];

    /**
     * CSV外の値を埋め込む
     * 画面の選択値とうを埋め込む
     *
     * @param array $storeData
     * @param array $value
     * @return array
     */
    public function inject( $storeData, $value ){
        return $storeData;
    }

    /**
     * DBへの登録更新処理を行う
     *
     * @param array $storeData
     */
    public function store( $storeData ){
        /**
         * 登録する値の加工
         * tb_syaken_jisshi
         */
        // 拠点
        $storeData['sj_base_code_init'] = $this->convertBaseCode( $storeData['sj_base_code_init'] );


        $storeData['sj_user_code_init'] = $this->convertUserId( $storeData['sj_user_code_init'] );
        $storeData['sj_user_id'] = CodeUtil::getUserIdByCode( $storeData['sj_user_code_init'] );

        $storeData['sj_customer_code'] = $this->convertCustomerCode( $storeData['sj_customer_code'] );

        if( !empty($storeData['sj_car_manage_number']) == TRUE ){
            $storeData['sj_car_manage_number'] = $this->convertCarManageNumber( $storeData['sj_car_manage_number'] );
        }

        // 現在日時
        $storeData['created_at'] = $storeData['updated_at'] = date('Y-m-d H:i:s');
        $storeData['deleted_at'] = null;

        // 管理者で指定
        $storeData['created_by'] = $storeData['updated_by'] = '1';

        // データ登録
        try {
            SyakenJisshi::merge( $storeData );

        } catch( \Exception $e ) {
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
     * CSV項目数を返送する
     *
     * @return number
     */
    public function getItemNum() {
        return 25;
    }

    /*
     * エラーログ用にクラス名を返送する
     *
     * @return string
     */
    public function getClassName() {
        $check_target = __CLASS__;
        $check_place = strrpos($check_target, '\\') + 1;
        $class_name = substr($check_target, $check_place);

        return $class_name;
    }
}
