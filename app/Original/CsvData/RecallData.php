<?php
namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Append\Recall;

/**
 * リコールデータ
 */
class RecallData {

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    protected $_cols = [
       1 => 'recall_customer_code',     //顧客 コード
       3 => 'recall_car_manage_number', // 統合車両管理No.
       4 => 'recall_no',                // 管理番号
       5 => 'recall_division',          // 区分
       6 => 'recall_jisshibi',          // 実施日
       7 => 'recall_detail'             // 内容
    ];

    /**
     * バリデーションルール
     */
    private $_validate_rules = [
        'recall_car_manage_number' => 'required',
        'recall_no' => 'required'
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
        /**
         * 登録する値の加工
         */
        $storeData['recall_car_manage_number'] = $this->convertCarManageNumber( $storeData['recall_car_manage_number'] );

        //　データ登録
        try{
            Recall::merge( $storeData );
            
        }catch( \Exception $e ){
            throw new \Exception($e);
//            $e->getMessage();
//            info('---------------- '.'Recall::merge');
//            info('発生場所 ------- '.__METHOD__);
//            info('クラス名 ------- '.__CLASS__);
//            info('エラー内容 ----- '."\n".$e->getMessage()."\n");
//
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
        return 7;
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
