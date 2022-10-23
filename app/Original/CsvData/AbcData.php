<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Append\Abc;

/**
 * ABC用CSVデータ
 *
 * @author yhatsutori
 *
 */
class AbcData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    private $_cols = [
        1 => 'abc_car_manage_number',
        2 => 'abc_abc'
    ];

    private $_validate_rules = [
            'abc_car_manage_number' => 'required'
    ];

    /**
     * CSV外からの値を埋め込む
     *
     * @param unknown $storeData
     * @param unknown $value
     * @return unknown
     */
    public function inject( $storeData, $value ){
        return $storeData;
    }

    /**
     * データベースへの登録更新処理を行う
     *
     * @param unknown $storeData
     */
    public function store( $storeData ){
        // 統合車両管理Noの前０を除去する
        $storeData['abc_car_manage_number'] = $this->convertCarManageNumber( $storeData['abc_car_manage_number'] );

        //　データ登録
        try{
            Abc::merge( $storeData );
        
        }catch( \Exception $e ){
            throw new \Exception($e);
//            $e->getMessage();
//
//            info('---------------- '.'Abc::merge');
//            info('発生場所 ------- '.__METHOD__);
//            info('クラス名 ------- '.__CLASS__);
//            info('エラー内容 ----- '."\n".$e->getMessage()."\n");
        }
    }
    
    /**
     * CSVカラムを返送する
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
     * CSVの項目数を返送する
     *
     * @return number
     */
    public function getItemNum() {
        return 2;
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
