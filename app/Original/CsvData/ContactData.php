<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Contact\Contact;
use App\Models\Contact\ContactComment;
use App\Original\Codes\Modal\ActionCodes;
use App\Original\Util\CodeUtil;

/**
 * 活動日報実績用CSVデータ
 *
 * @author yhatsutori
 *
 */
class ContactData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    protected $_cols = [
        1  => 'ctc_user_code_init',     //初期担当者 コード
        3  => 'ctc_customer_code',      //顧客 コード
        5  => 'ctc_car_manage_number',  //統合車両管理No.
        6  => 'ctc_contact_date',       //接触年月日
        7  => 'ctc_contact_memo',       //コメント
        8  => 'ctc_way_code',           //接触方法
        10 => 'ctc_result_name'         //接触成果
    ];

    /**
     * バリデーションルール
     */
    private $_validate_rules = [
            'ctc_customer_code' => 'required'
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
         * 登録する値の加工
         * tb_contact
         */
        // 担当者
        $storeData['ctc_user_code_init'] = $this->convertUserId( $storeData['ctc_user_code_init'] );
        $storeData['ctc_user_id'] = CodeUtil::getUserIdByCode( $storeData['ctc_user_code_init'] );

        $storeData['ctc_customer_code'] = $this->convertCustomerCode( $storeData['ctc_customer_code'] );
        $storeData['ctc_contact_ym'] = date( 'Ym', strtotime( $storeData['ctc_contact_date'] ) );
        if( !empty($storeData['ctc_car_manage_number']) == TRUE ){
            $storeData['ctc_car_manage_number'] = $this->convertCarManageNumber( $storeData['ctc_car_manage_number'] );
        }
        
        // 接触方法コードの登録
        $actionCodes = (new ActionCodes())->getArray();
        
        if( !empty($storeData['ctc_way_code']) == TRUE ){
            $key = array_search($storeData['ctc_way_code'], $actionCodes);
            // 配列にない場合は99を代入
            if( $key == FALSE ){
                $storeData['ctc_way_code'] = 99;
            }// 存在する場合は値を代入
            else{
                $storeData['ctc_way_code'] = $key;
            }
        }
        
        // 接触成果コードの登録
        if( !empty( $storeData['ctc_result_name'] ) == TRUE ){
            $key = array_search($storeData['ctc_result_name'], config('ctcResultCode'));
            // 配列にない場合は99を代入
            if( $key == FALSE ){
                $storeData['ctc_result_code'] = 99;
            }// 存在する場合は値を代入
            else{
                $storeData['ctc_result_code'] = $key;
            }
        }
        
        // データ登録
        try{
            Contact::merge( $storeData );
            
        }catch( \Exception $e ){
            throw new \Exception($e);
//            $e->getMessage();
//
//            info('---------------- '.'Contact::merge');
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
