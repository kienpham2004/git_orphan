<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Contact\ContactComment;

/**
 * 活動日報実績用CSVデータ
 *
 * @author yhatsutori
 *
 */
class ContactCommentData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    protected $_cols = [
        3  => 'ctccom_customer_code',
        6  => 'ctccom_contact_date',
        7  => 'ctccom_contact_memo',
    ];

    /**
     * バリデーションルール
     */
    private $_validate_rules = [
            'ctccom_customer_code' => 'required'
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
     * @param unknown $storeData
     */
    public function store( $storeData ){
        /**
         * 登録する値の加工（ctc_contact_memoが存在する場合のみ）
         * tb_contact_comment
         */
        if( !empty( $storeData['ctccom_contact_memo'] ) === TRUE ){
            $storeData['ctccom_customer_code'] = $this->convertCustomerCode( $storeData['ctccom_customer_code'] );
            $storeData['ctccom_contact_date'] = $storeData['ctccom_contact_date'];
            $storeData['ctccom_contact_memo'] = $storeData['ctccom_contact_memo'];
        }
        
        // データ登録
        if( !empty( $storeData ) == TRUE && !empty( $storeData['ctccom_contact_memo'] ) === TRUE ){
            try{
                ContactComment::merge( $storeData );

            }catch( \Exception $e ){
                throw new \Exception($e);
//                $e->getMessage();
//
//                info('---------------- '.'ContactCommentData::merge');
//                info('発生場所 ------- '.__METHOD__);
//                info('クラス名 ------- '.__CLASS__);
//                info('エラー内容 ----- '."\n".$e->getMessage()."\n");
            }
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
        return 10;
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
