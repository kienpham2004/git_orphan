<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Append\Ikou;
use DB;
use App\Original\Util\CodeUtil;

/**
 * 顧客用CSVデータ
 *
 * @author yhatsutori
 *
 */
class IkouData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    protected $_cols = [
        1     => 'ik_dealer_code', // 販売店コード
        2     => 'ik_dealer_name', // 販売店名
        3     => 'ik_base_code_init', // 初期拠点コード
        4     => 'ik_base_name_csv', // 拠点名
        5     => 'ik_user_code_init', // 初期担当者コード
        6     => 'ik_user_name_csv', // 担当者氏名（漢字）
        7     => 'ik_customer_code', // 顧客コード
        8     => 'ik_customer_name_kanji', // 顧客漢字氏名
        9     => 'ik_first_regist_date_ym', // ＊初度登録年月　YYYYMM
        10    => 'ik_car_name', // 車種
        11    => 'ik_car_base_number', // 車両番号
        12    => 'ik_abc', // ABCゾーン
        13    => 'ik_purchase_form', // 購入形態
        14    => 'ik_ciao_course', // ﾁｬｵ
        15    => 'ik_customer_insurance_type', // 任意保険加入区分名称
        16    => 'ik_syaken_next_date', // ＊次回車検日　YYYY/MM/DD
        17    => 'ik_add_year', // 追加年月
        18    => 'ik_customer_kouryaku_flg', // 攻略対象
        19    => 'ik_latest_ikou', // 最新意向
        20    => 'ik_come_syaken_6', // 来店（車検6ヶ月前）
        21    => 'ik_visit_syaken_6', // 訪問（車検6ヶ月前）
        22    => 'ik_phone_syaken_6', // 電話（車検6ヶ月前）
        23    => 'ik_mail_syaken_6', // メール（車検6ヶ月前）
        24    => 'ik_hot_syaken_6', // HOT（車検6ヶ月前）
        25    => 'ik_shodan_memo_syaken_6', // 商メモ（車検6ヶ月前）
        26    => 'ik_credit_syaken_6', // 残クレ（車検6ヶ月前）
        27    => 'ik_assessment_syaken_6', // 査定（車検6ヶ月前）
        28    => 'ik_test_drive_syaken_6', // 試乗（車検6ヶ月前）
        29    => 'ik_estimate_syaken_6', // 車検見積（車検6ヶ月前）
        30    => 'ik_ikou_syaken_6', // 意向（車検6ヶ月前）
        31    => 'ik_come_syaken_5', // 来店（車検5ヶ月前）
        32    => 'ik_visit_syaken_5', // 訪問（車検5ヶ月前）
        33    => 'ik_phone_syaken_5', // 電話（車検5ヶ月前）
        34    => 'ik_mail_syaken_5', // メール（車検5ヶ月前）
        35    => 'ik_hot_syaken_5', // HOT（車検5ヶ月前）
        36    => 'ik_shodan_memo_syaken_5', // 商メモ（車検5ヶ月前）
        37    => 'ik_credit_syaken_5', // 残クレ（車検5ヶ月前）
        38    => 'ik_assessment_syaken_5', // 査定（車検5ヶ月前）
        39    => 'ik_test_drive_syaken_5', // 試乗（車検5ヶ月前）
        40    => 'ik_estimate_syaken_5', // 車検見積（車検5ヶ月前）
        41    => 'ik_ikou_syaken_5', // 意向（車検5ヶ月前）
        42    => 'ik_come_syaken_4', // 来店（車検4ヶ月前）
        43    => 'ik_visit_syaken_4', // 訪問（車検4ヶ月前）
        44    => 'ik_phone_syaken_4', // 電話（車検4ヶ月前）
        45    => 'ik_mail_syaken_4', // メール（車検4ヶ月前）
        46    => 'ik_hot_syaken_4', // HOT（車検4ヶ月前）
        47    => 'ik_shodan_memo_syaken_4', // 商メモ（車検4ヶ月前）
        48    => 'ik_credit_syaken_4', // 残クレ（車検4ヶ月前）
        49    => 'ik_assessment_syaken_4', // 査定（車検4ヶ月前）
        50    => 'ik_test_drive_syaken_4', // 試乗（車検4ヶ月前）
        51    => 'ik_estimate_syaken_4', // 車検見積（車検4ヶ月前）
        52    => 'ik_ikou_syaken_4', // 意向（車検4ヶ月前）
        53    => 'ik_come_syaken_3', // 来店（車検3ヶ月前）
        54    => 'ik_visit_syaken_3', // 訪問（車検3ヶ月前）
        55    => 'ik_phone_syaken_3', // 電話（車検3ヶ月前）
        56    => 'ik_mail_syaken_3', // メール（車検3ヶ月前）
        57    => 'ik_hot_syaken_3', // HOT（車検3ヶ月前）
        58    => 'ik_shodan_memo_syaken_3', // 商メモ（車検3ヶ月前）
        59    => 'ik_credit_syaken_3', // 残クレ（車検3ヶ月前）
        60    => 'ik_assessment_syaken_3', // 査定（車検3ヶ月前）
        61    => 'ik_test_drive_syaken_3', // 試乗（車検3ヶ月前）
        62    => 'ik_estimate_syaken_3', // 車検見積（車検3ヶ月前）
        63    => 'ik_ikou_syaken_3', // 意向（車検3ヶ月前）
        64    => 'ik_come_syaken_2', // 来店（車検2ヶ月前）
        65    => 'ik_visit_syaken_2', // 訪問（車検2ヶ月前）
        66    => 'ik_phone_syaken_2', // 電話（車検2ヶ月前）
        67    => 'ik_mail_syaken_2', // メール（車検2ヶ月前）
        68    => 'ik_hot_syaken_2', // HOT（車検2ヶ月前）
        69    => 'ik_shodan_memo_syaken_2', // 商メモ（車検2ヶ月前）
        70    => 'ik_credit_syaken_2', // 残クレ（車検2ヶ月前）
        71    => 'ik_assessment_syaken_2', // 査定（車検2ヶ月前）
        72    => 'ik_test_drive_syaken_2', // 試乗（車検2ヶ月前）
        73    => 'ik_estimate_syaken_2', // 車検見積（車検2ヶ月前）
        74    => 'ik_ikou_syaken_2', // 意向（車検2ヶ月前）
        75    => 'ik_come_syaken_1', // 来店（車検1ヶ月前）
        76    => 'ik_visit_syaken_1', // 訪問（車検1ヶ月前）
        77    => 'ik_phone_syaken_1', // 電話（車検1ヶ月前）
        78    => 'ik_mail_syaken_1', // メール（車検1ヶ月前）
        79    => 'ik_hot_syaken_1', // HOT（車検1ヶ月前）
        80    => 'ik_shodan_memo_syaken_1', // 商メモ（車検1ヶ月前）
        81    => 'ik_credit_syaken_1', // 残クレ（車検1ヶ月前）
        82    => 'ik_assessment_syaken_1', // 査定（車検1ヶ月前）
        83    => 'ik_test_drive_syaken_1', // 試乗（車検1ヶ月前）
        84    => 'ik_estimate_syaken_1', // 車検見積（車検1ヶ月前）
        85    => 'ik_ikou_syaken_1', // 意向（車検1ヶ月前）
        86    => 'ik_final_achievement', // 最終実績
        87    => 'ik_transfer_base_code', // 異動拠点コード
        88    => 'ik_transfer_base_name', // 異動拠点名
        89    => 'ik_transfer_user_id', // 異動担当者コード
        90    => 'ik_transfer_user_name', // 異動担当者名
        91    => 'ik_transfer_ym' // 異動年月
    ];

    /**
     * バリデーションルール
     */
    private $_validate_rules = [
        'ik_customer_code' => 'required',
        'ik_syaken_next_date' => 'required'
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
        //最新意向の更新日を設定
        // 意向確認から最新意向を取得
        $latest_ikou = DB::table('tb_ikou')->select( 'ik_latest_ikou' )
                                        ->where( 'ik_customer_code', $this->convertCustomerCode( $storeData['ik_customer_code'] ))
                                        ->where( 'ik_syaken_next_date', $storeData['ik_syaken_next_date'] )
                                        ->where( 'ik_car_base_number', $this->filterCarNumberSpace( $storeData['ik_car_base_number']) )
                                        ->value( 'ik_latest_ikou' );
        
        //CSVの最新意向とDBの最新意向が違う場合、最新意向更新日に現在の日付を設定
        if($storeData["ik_latest_ikou"] != $latest_ikou){
            $storeData["ik_latest_ikou_update"] = date("Y/m/d");
            $storeData["ik_latest_ikou_history"] = $latest_ikou;
        }
        
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
        $storeData['ik_base_code_init'] = $this->convertBaseCode( $storeData['ik_base_code_init'] );
        // 担当者
        $storeData['ik_user_code_init'] = $this->convertUserId( $storeData['ik_user_code_init'] );
        $storeData['ik_user_id'] = CodeUtil::getUserIdByCode($storeData['ik_user_code_init'] );


        $storeData['ik_customer_code'] = $this->convertCustomerCode( $storeData['ik_customer_code'] );
        $storeData['ik_car_base_number'] = $this->filterCarNumberSpace( $storeData['ik_car_base_number'] );
        
        //　データ登録
        try{
            Ikou::merge( $storeData );
            
        }catch( \Exception $e ){
            throw new \Exception($e);
//            $e->getMessage();
//
//            info('---------------- '.'Ikou::merge');
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
        return 91;
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
