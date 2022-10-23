<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Customer;
use App\Original\Util\CodeUtil;
//use App\Original\Codes\Intent\IntentKojinHojinCodes;
//use App\Original\Codes\Intent\IntentKeisanHousikiCodes;

/**
 * 顧客用CSVデータ
 *
 * @author yhatsutori
 *
 */
class CustomerData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    protected $_cols = [

        1   => 'base_code_init',               //初期拠点 コード
        2   => 'base_name_csv',                     //拠点 名
        3   => 'user_code_init',               //初期担当者 コード
        4   => 'user_name_csv',                     //担当者 名
        5   => 'car_manage_number',             //統合車両管理No.
        6   => 'customer_code',                 //顧客 コード
        7   => 'customer_name_kanji',           //顧客 名
        8   => 'customer_name_kata',            //顧客カナ
        9   => 'gensen_code_name',              //源泉
        10  => 'customer_category_code_name',   //顧客分類
        11  => 'customer_postal_code',          //自宅郵便番号
        12  => 'customer_address',              //自宅住所
        13  => 'customer_tel',                  //自宅TEL
        14  => 'mobile_tel',                    //携帯TEL
        15  => 'customer_office_tel',           //勤務先TEL
        16  => 'car_name',                      //車名
        17  => 'car_year_type',                 //年式
        18  => 'first_regist_date_ym',          //初度登録年月
        19  => 'cust_reg_date',                 //登録日
        20  => 'car_base_number',               //車両登録No.
        21  => 'car_model',                     //型式
        22  => 'car_service_code',              //サービス通称名
        23  => 'car_frame_number',              //フレームNo.
        24  => 'car_buy_type',                  //自販他販区分
        25  => 'car_new_old_kbn_name',          //新中区分
        26  => 'syaken_times',                  //車検回数
        27  => 'syaken_next_date',              //次回車検日
        28  => 'customer_insurance_type',       //任意保険加入区分
        29  => 'customer_insurance_company',    //任意保険会社
        30  => 'customer_insurance_end_date',   //任意保険終期
        31  => 'credit_hensaihouhou',           //クレジット返済方法
//            => 'credit_hensaihouhou_name'       //クレジット返済方法名称
        32  => 'first_shiharai_date_ym',        //選択プラン_初回支払年月
        33  => 'keisan_housiki_kbn',            //計算方式
        34  => 'credit_card_select_kbn',        //クレジット・カード選択区分
        35  => 'memo_syubetsu',                 //メモ種別
        36  => 'shiharai_count',                //支払回数
        37  => 'sueoki_zankaritsu',             //据置・残価率
        38  => 'last_shiharaikin',              //最終回支払額金
        39  => 'customer_kouryaku_flg',         //攻略対象車
        40  => 'customer_dm_flg',               //D/M不要区分
        41  => 'customer_kojin_hojin_flg',      //個人法人区分
        42  => 'car_type',                      //車種
        43  => 'abc_zone',                      //ABCゾーン
        44  => 'htc_number',                    //HTC会員番号
        45  => 'htc_car',                       //HTC契約車両
        // 20200304不足
        46  => 'ciao_course',                   //チャオコース/プラン
        47  => 'ciao_end_date',                 //会員証有効期間終期
    ];

    /**
     * バリデーションルール
     */
    private $_validate_rules = [
            'car_manage_number' => 'required'
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
        $storeData['base_code_init'] = $this->convertBaseCode( $storeData['base_code_init'] );
        // 担当者
        $storeData['user_code_init'] = $this->convertUserId( $storeData['user_code_init'] );
        $storeData['user_id'] = CodeUtil::getUserIdByCode( $storeData['user_code_init'] );

        $storeData['car_manage_number'] = $this->convertCarManageNumber( $storeData['car_manage_number'] );
        $storeData['customer_code'] = $this->convertCustomerCode( $storeData['customer_code'] );
        $storeData['customer_tel'] = $this->convertTel( $storeData['customer_tel'] );
        $storeData['customer_office_tel'] = $this->convertTel( $storeData['customer_office_tel'] );
        $storeData['mobile_tel'] = $this->convertTel( $storeData['mobile_tel'] );
        $storeData['car_base_number'] = $this->filterCarNumberSpace( $storeData['car_base_number'] );
        
        /////////// Aily後の変換処理＜コード ⇔ テキスト＞
        // クレジット返済方法名称
        if( !empty( $storeData['credit_hensaihouhou'] ) == True ){
            $key = array_search($storeData['credit_hensaihouhou'], config('creditHensaihouhouName'));
            // 配列にない場合は99を代入
            if( $key == FALSE ){
                $storeData['credit_hensaihouhou_name'] = '99';
            }// 存在する場合は値を代入
            else{
                $storeData['credit_hensaihouhou_name'] = $key;
            }
        }// 存在しない場合は空を代入
        else{
            $storeData['credit_hensaihouhou_name'] = '';
        }
        
        // 個人法人コード
        if( !empty( $storeData['customer_kojin_hojin_flg'] ) == True ){
            $key = array_search($storeData['customer_kojin_hojin_flg'], config('customerKojinHojinFlg'));
            // 配列にない場合は99を代入
            if( $key == FALSE ){
                $storeData['customer_kojin_hojin_flg'] = 99;
            }// 存在する場合は値を代入
            else{
                $storeData['customer_kojin_hojin_flg'] = $key;
            }
        }// 存在しない場合は0を代入
        else{
            $storeData['customer_kojin_hojin_flg'] = 0;
        }
        
        // 計算方式区分
        if( !empty( $storeData['keisan_housiki_kbn'] ) == True ){
            // 存在する場合は値を代入
            if( $storeData['keisan_housiki_kbn'] == "実質年率" ){
                $storeData['keisan_housiki_kbn'] = 1;
            }elseif( $storeData['keisan_housiki_kbn'] == "アドオン" ){
                $storeData['keisan_housiki_kbn'] = 2;
            }// それ以外の文字の時は99を代入
            else{
                $storeData['keisan_housiki_kbn'] = 99;
            }
        }// 存在しない場合は空を代入
        else{
            $storeData['keisan_housiki_kbn'] = 0;
        }
        
        // 2017-12-12 add 車両Noが存在するものだけを取り込み
        if( !empty( $storeData['car_base_number'] ) == True && !empty( $storeData['user_id'] ) == True ){
            //　データ登録
            try{
                // 管理内Uのお客様だけ、登録
                // 管理内Uのお客様が準管理に変更してもデータは保持
                if( $storeData['customer_category_code_name'] == "管理内Ｕ" ){
                    Customer::merge( $storeData );
                }
            }catch( \Exception $e ){
                throw new \Exception($e);
//                $e->getMessage();
//
//                info('---------------- '.'Customer::merge');
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
        return 47;
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
