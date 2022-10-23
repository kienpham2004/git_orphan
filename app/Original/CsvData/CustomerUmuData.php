<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\CustomerUmu;
use App\Original\Util\CodeUtil;

/**
 * 顧客用CSVデータ
 *
 * @author yhatsutori
 *
 */
class CustomerUmuData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    protected $_cols = [
        1     => 'umu_base_code_init',
        3     => 'umu_user_code_init',
        6     => 'umu_customer_code',
        5     => 'umu_car_manage_number',
        10    => 'umu_customer_category_code_name',
        20    => 'umu_car_base_number',
    ];
    
    /**
     * バリデーションルール
     */
    private $_validate_rules = [
            'umu_car_manage_number' => 'required'
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
        // 拠点
        $storeData['umu_base_code_init'] = $this->convertBaseCode( $storeData['umu_base_code_init'] );
        // 担当者
        $storeData['umu_user_code_init'] = $this->convertUserId( $storeData['umu_user_code_init'] );
        $storeData['umu_user_id'] = CodeUtil::getUserIdByCode( $storeData['umu_user_code_init'] );


        $storeData['umu_car_manage_number'] = $this->convertCarManageNumber( $storeData['umu_car_manage_number'] );
        $storeData['umu_customer_code'] = $this->convertCustomerCode( $storeData['umu_customer_code'] );
        $storeData['umu_car_base_number'] = $this->filterCarNumberSpace( $storeData['umu_car_base_number'] );

        // 2017-12-12 add 車両Noが存在するものだけを取り込み
        if( !empty( $storeData['umu_car_base_number'] ) == True ){
            // 管理内Uのお客様だけ、登録
            // 管理内Uのお客様が準管理に変更してもデータは保持
            if( $storeData['umu_customer_category_code_name'] == "管理内Ｕ" ){
                //　データ登録
                try{
                    //CustomerUmu::merge( $storeData );
                    CustomerUmu::create($storeData );

                }catch( \Exception $e ){
                    throw new \Exception($e);
//                    $e->getMessage();
//
//                    info('---------------- '.'CustomerUmu::merge');
//                    info('発生場所 ------- '.__METHOD__);
//                    info('クラス名 ------- '.__CLASS__);
//                    info('エラー内容 ----- '."\n".$e->getMessage()."\n");
                }
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
        return 47;
    }
}
