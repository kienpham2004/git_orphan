<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Append\DaigaeSyaken;
use App\Models\Append\Ikou;
use DB;
use App\Original\Util\CodeUtil;
use App\Original\Codes\Intent\IntentSyatenkenCodes;
use App\Lib\Codes\MassyoReasonCodes;
use App\Original\Codes\Inspect\InspectInsuTypes;
use App\Original\Codes\Insurance\InsuJisyaTasyaCodes;

/**
 * 代替車検推進管理用CSVデータ
 *
 * @author ルック
 *
 */
class DaigaeSyakenData{

    use tCsvImport;
    public static $fistImportRecord = false; // 初期行目導入
    public  $_arrLogMessage; // システムログ用
    public $_functionName = '代替車検推進管理用CSVデータ';

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    protected $_cols = [
        1  => 'dsya_base_code_init',  // 拠点 コード
        2  => 'dsya_base_name_csv',  // 拠点 名
        3  => 'dsya_user_code_init',  // 担当者 コード
        4  => 'dsya_user_name_csv',  // 担当者 名
        5  => 'dsya_customer_code',  // 顧客 コード
        6  => 'dsya_customer_name_kanji',  // 顧客 名
        7  => 'dsya_gensen_code_name',  // 対象時源泉
        8  => 'dsya_customer_tel',  // 自宅TEL
        9  => 'dsya_keitai_tel',  // 携帯TEL
        10  => 'service_yuchi_fuyou',  // サービス誘致不要
        11  => 'dsya_car_manage_number',  // 統合車両管理No.
        12  => 'dsya_car_name',  // 車名
        13  => 'dsya_car_base_number',  // 登録No.
        14  => 'dsya_car_new_old_kbn_name',  // 新中区分
        15  => 'dsya_car_buy_type',  // 自他販区分
        16  => 'dsya_customer_kouryaku_flg',  // 攻車
        17  => 'dsya_first_regist_date_ym',  // 初度登録
        18  => 'dsya_syaken_times',  // 車検回数
        19  => 'dsya_syaken_next_date',  // 車検期日
        20  => 'dsya_abc_zone',  // ゾーン区分
        21  => 'dsya_daigae_car_name',  // 代替予定車
        22  => 'dsya_kounyu_car_name',  // 購入予定車
        23  => 'dsya_ciao_course',  // 現チャオコース/プラン
        24  => 'dsya_ciao_end_date',  // 会員証有効終期
        25  => 'dsya_zankure',  // 残クレ
        26  => 'dsya_zankure_manki',  // 残クレ満期
        // 27  => 'dsya_customer_insurance_type',  // 任保加入区分
        // 28  => 'dsya_customer_insurance_company',  // 任保社名
        // 29  => 'dsya_customer_insurance_end_date',  // 任保満期
        // 30  => 'contact_6m',  // 6M接触日
        // 31  => 'satei_6m',  // 6M査定
        // 32  => 'shijo_6m',  // 6M試乗
        // 33  => 'syodan_6m',  // 6M商談
        // 34  => 'contact_5m',  // 5M接触日
        // 35  => 'satei_5m',  // 5M査定
        // 36  => 'shijo_5m',  // 5M試乗
        // 37  => 'syodan_5m',  // 5M商談
        // 38  => 'contact_4m',  // 4M接触日
        // 39  => 'satei_4m',  // 4M査定
        // 40  => 'shijo_4m',  // 4M試乗
        // 41  => 'syodan_4m',  // 4M商談
        // 42  => 'contact_3m',  // 3M接触日
        // 43  => 'satei_3m',  // 3M査定
        // 44  => 'shijo_3m',  // 3M試乗
        // 45  => 'syodan_3m',  // 3M商談
        // 46  => 'contact_2m',  // 2M接触日
        // 47  => 'satei_2m',  // 2M査定
        // 48  => 'shijo_2m',  // 2M試乗
        // 49  => 'syodan_2m',  // 2M商談
        // 50  => 'contact_1m',  // 1M接触日
        // 51  => 'satei_1m',  // 1M査定
        // 52  => 'shijo_1m',  // 1M試乗
        // 53  => 'syodan_1m',  // 1M商談
        // 54  => 'contact_0m',  // 車検当月接触日
        // 55  => 'dsya_tmr_kaden',  // TMR架電
        // 56  => 'dsya_tmr_kekka',  // TMR結果
        // 57  => 'dsya_status_katsudo_csv',  // 意向区分（活動日報）
        // 58  => 'dsya_status_katsudo_date',  // 意向確認日（活動日報）
        // 59  => 'dsya_status_csv',  // 意向区分
        // 60  => 'dsya_status_date',  // 意向確認日
        // 61  => 'dsya_syaken_reserve_date',  // 車検予約日
        // 62  => 'dsya_hot_date',  // HOT発生日
        // 63  => 'dsya_hot_car',  // HOT発生車名
        // 64  => 'dsya_keiyaku_car',  // 契約確定車名
        // 65  => 'dsya_syaken_jisshi_date',  // 車検実施日
        // 66  => 'dsya_tasya_syaken',  // 他社車検
        // 67  => 'dsya_daigae_car',  // 自法人代替車名
        // 68  => 'dsya_shiyozumi_kaitori',  // 使用済/買取
        // 69  => 'dsya_massyo_reason',  // 抹消理由
        // 70  => 'dsya_tenkyo_flg',  // 転居
        // 71  => 'dsya_massyo_date'  // 顧客抹消日
        28  => 'dsya_customer_insurance_type',  // 任保加入区分
        29  => 'dsya_customer_insurance_company',  // 任保社名
        30  => 'dsya_customer_insurance_end_date',  // 任保満期
        31  => 'contact_6m',  // 6M接触日
        32  => 'satei_6m',  // 6M査定
        33  => 'shijo_6m',  // 6M試乗
        34  => 'syodan_6m',  // 6M商談
        35  => 'contact_5m',  // 5M接触日
        36  => 'satei_5m',  // 5M査定
        37  => 'shijo_5m',  // 5M試乗
        38  => 'syodan_5m',  // 5M商談
        39  => 'contact_4m',  // 4M接触日
        40  => 'satei_4m',  // 4M査定
        41  => 'shijo_4m',  // 4M試乗
        42  => 'syodan_4m',  // 4M商談
        43  => 'contact_3m',  // 3M接触日
        44  => 'satei_3m',  // 3M査定
        45  => 'shijo_3m',  // 3M試乗
        46  => 'syodan_3m',  // 3M商談
        47  => 'contact_2m',  // 2M接触日
        48  => 'satei_2m',  // 2M査定
        49  => 'shijo_2m',  // 2M試乗
        50  => 'syodan_2m',  // 2M商談
        51  => 'contact_1m',  // 1M接触日
        52  => 'satei_1m',  // 1M査定
        53  => 'shijo_1m',  // 1M試乗
        54  => 'syodan_1m',  // 1M商談
        55  => 'contact_0m',  // 車検当月接触日
        56  => 'dsya_tmr_kaden',  // TMR架電
        57  => 'dsya_tmr_kekka',  // TMR結果
        58  => 'dsya_status_katsudo_csv',  // 意向区分（活動日報）
        59  => 'dsya_status_katsudo_date',  // 意向確認日（活動日報）
        60  => 'dsya_status_csv',  // 意向区分
        61  => 'dsya_status_date',  // 意向確認日
        62  => 'dsya_syaken_reserve_date',  // 車検予約日
        63  => 'dsya_hot_date',  // HOT発生日
        64  => 'dsya_hot_car',  // HOT発生車名
        65  => 'dsya_keiyaku_car',  // 契約確定車名
        66  => 'dsya_syaken_jisshi_date',  // 車検実施日
        67  => 'dsya_tasya_syaken',  // 他社車検
        68  => 'dsya_daigae_car',  // 自法人代替車名
        69  => 'dsya_shiyozumi_kaitori',  // 使用済/買取
        70  => 'dsya_massyo_reason',  // 抹消理由
        71  => 'dsya_tenkyo_flg',  // 転居
        72  => 'dsya_massyo_date'  // 顧客抹消日

    ];

    /**
     * バリデーションルール
     */
    private $_validate_rules = [
        'dsya_customer_code' => 'required',      // 顧客 コード
        'dsya_car_manage_number' => 'required',  // 統合車両管理No.
        'dsya_syaken_next_date' => 'required'       // 車検期日
    ];

    /**
     * 項目名リスト
     */
    private $_column_name = [
        'dsya_customer_code' => '顧客コード',
        'dsya_car_manage_number' => '統合車両管理No',
        'dsya_syaken_next_date' => '車検期日'
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

        // 拠点
        $storeData['dsya_base_code_init'] = $this->convertBaseCode( $storeData['dsya_base_code_init'] );
        // 担当者
        $storeData['dsya_user_code_init'] = $this->convertUserId( $storeData['dsya_user_code_init'] );
        $storeData['dsya_user_id'] = CodeUtil::getUserIdByCode($storeData['dsya_user_code_init'] );
        // 担当者コードチェック
        $checkUser = $this->checkUserCode($storeData['dsya_user_code_init'], $storeData['dsya_user_id']);
        if(!empty($checkUser)){
            $this->_arrLogMessage[] =  $checkUser;
        }

        $storeData['dsya_customer_code'] = $this->convertCustomerCode( $storeData['dsya_customer_code'] );
        $storeData['dsya_car_manage_number'] = $this->convertCarManageNumber( $storeData['dsya_car_manage_number'] );

        // 携帯TEL
        $storeData['dsya_keitai_tel'] = trim($storeData['dsya_keitai_tel']);
        // 車検年月を変換
        if ($storeData['dsya_syaken_next_date'] != "" ){
            $storeData['dsya_inspection_ym'] = date( 'Ym', strtotime( $storeData['dsya_syaken_next_date']) );
        }
        // 転居フラグ
        if ($storeData['dsya_tenkyo_flg'] == "○" ){
            $storeData['dsya_tenkyo_flg'] = 1;
        }else{
            $storeData['dsya_tenkyo_flg'] = null;
        }

        // 使用済/買取
        if ($storeData['dsya_shiyozumi_kaitori'] == "○" ){
            $storeData['dsya_shiyozumi_kaitori'] = 1;
        }else{
            $storeData['dsya_shiyozumi_kaitori'] = null;
        }

        //　意向区分（活動日報）のコード変換
         $storeData['dsya_status_katsudo_code'] = (new IntentSyatenkenCodes())->getCode($storeData['dsya_status_katsudo_csv']);

        //　意向区分のコード変換
        $storeData['dsya_status_code'] = (new InsuJisyaTasyaCodes())->getCode($storeData['dsya_status_csv']);

        // 抹消理由のコード変換
        $storeData['dsya_massyo_reason_code'] = (new MassyoReasonCodes())->getCode($storeData['dsya_massyo_reason']);

        // 任保加入区分のコード変換
        $storeData['dsya_customer_insurance_type_code'] = (new InspectInsuTypes())->getCode($storeData['dsya_customer_insurance_type']);

        //　データ登録
        try {
            DaigaeSyaken::merge($storeData);
        }catch( \Exception $e ){
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
     * 項目名を返送する
     *
     * @return string[]
     */
    public function getColumnName() {
        return $this->_column_name;
    }

    /**
     * CSV項目数を返送する
     *
     * @return number
     */
    public function getItemNum() {
        return 72;
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
