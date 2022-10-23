<?php

namespace App\Models\Append;

use App\Models\AbstractModel;

/**
 * 顧客データに関するモデル
 */
class Ikou extends AbstractModel {
    // テーブル名
    protected $table = 'tb_ikou';

    // 変更可能なカラム
    protected $fillable = [
        'ik_dealer_code', // 販売店コード
        'ik_dealer_name', // 販売店名
        'ik_base_code_init', // 拠点コード
        'ik_base_name_csv', // 拠点名
        'ik_user_id', // 担当者Id
        'ik_user_code_init', // 初期担当者コード
        'ik_user_name_csv', // 担当者氏名（漢字）
        'ik_customer_code', // 顧客コード
        'ik_customer_name_kanji', // 顧客漢字氏名
        'ik_first_regist_date_ym', // ＊初度登録年月　YYYYMM
        'ik_car_name', // 車種
        'ik_car_base_number', // 車両番号
        'ik_abc', // ABCゾーン
        'ik_purchase_form', // 購入形態
        'ik_ciao_course', // ﾁｬｵ
        'ik_customer_insurance_type', // 任意保険加入区分名称
        'ik_syaken_next_date', // ＊次回車検日　YYYY/MM/DD
        'ik_add_year', // 追加年月
        'ik_customer_kouryaku_flg', // 攻略対象
        'ik_latest_ikou', // 最新意向
        'ik_come_syaken_6', // 来店（車検6ヶ月前）
        'ik_visit_syaken_6', // 訪問（車検6ヶ月前）
        'ik_phone_syaken_6', // 電話（車検6ヶ月前）
        'ik_mail_syaken_6', // メール（車検6ヶ月前）
        'ik_hot_syaken_6', // HOT（車検6ヶ月前）
        'ik_shodan_memo_syaken_6', // 商メモ（車検6ヶ月前）
        'ik_credit_syaken_6', // 残クレ（車検6ヶ月前）
        'ik_assessment_syaken_6', // 査定（車検6ヶ月前）
        'ik_test_drive_syaken_6', // 試乗（車検6ヶ月前）
        'ik_estimate_syaken_6', // 車検見積（車検6ヶ月前）
        'ik_ikou_syaken_6', // 意向（車検6ヶ月前）
        'ik_come_syaken_5', // 来店（車検5ヶ月前）
        'ik_visit_syaken_5', // 訪問（車検5ヶ月前）
        'ik_phone_syaken_5', // 電話（車検5ヶ月前）
        'ik_mail_syaken_5', // メール（車検5ヶ月前）
        'ik_hot_syaken_5', // HOT（車検5ヶ月前）
        'ik_shodan_memo_syaken_5', // 商メモ（車検5ヶ月前）
        'ik_credit_syaken_5', // 残クレ（車検5ヶ月前）
        'ik_assessment_syaken_5', // 査定（車検5ヶ月前）
        'ik_test_drive_syaken_5', // 試乗（車検5ヶ月前）
        'ik_estimate_syaken_5', // 車検見積（車検5ヶ月前）
        'ik_ikou_syaken_5', // 意向（車検5ヶ月前）
        'ik_come_syaken_4', // 来店（車検4ヶ月前）
        'ik_visit_syaken_4', // 訪問（車検4ヶ月前）
        'ik_phone_syaken_4', // 電話（車検4ヶ月前）
        'ik_mail_syaken_4', // メール（車検4ヶ月前）
        'ik_hot_syaken_4', // HOT（車検4ヶ月前）
        'ik_shodan_memo_syaken_4', // 商メモ（車検4ヶ月前）
        'ik_credit_syaken_4', // 残クレ（車検4ヶ月前）
        'ik_assessment_syaken_4', // 査定（車検4ヶ月前）
        'ik_test_drive_syaken_4', // 試乗（車検4ヶ月前）
        'ik_estimate_syaken_4', // 車検見積（車検4ヶ月前）
        'ik_ikou_syaken_4', // 意向（車検4ヶ月前）
        'ik_come_syaken_3', // 来店（車検3ヶ月前）
        'ik_visit_syaken_3', // 訪問（車検3ヶ月前）
        'ik_phone_syaken_3', // 電話（車検3ヶ月前）
        'ik_mail_syaken_3', // メール（車検3ヶ月前）
        'ik_hot_syaken_3', // HOT（車検3ヶ月前）
        'ik_shodan_memo_syaken_3', // 商メモ（車検3ヶ月前）
        'ik_credit_syaken_3', // 残クレ（車検3ヶ月前）
        'ik_assessment_syaken_3', // 査定（車検3ヶ月前）
        'ik_test_drive_syaken_3', // 試乗（車検3ヶ月前）
        'ik_estimate_syaken_3', // 車検見積（車検3ヶ月前）
        'ik_ikou_syaken_3', // 意向（車検3ヶ月前）
        'ik_come_syaken_2', // 来店（車検2ヶ月前）
        'ik_visit_syaken_2', // 訪問（車検2ヶ月前）
        'ik_phone_syaken_2', // 電話（車検2ヶ月前）
        'ik_mail_syaken_2', // メール（車検2ヶ月前）
        'ik_hot_syaken_2', // HOT（車検2ヶ月前）
        'ik_shodan_memo_syaken_2', // 商メモ（車検2ヶ月前）
        'ik_credit_syaken_2', // 残クレ（車検2ヶ月前）
        'ik_assessment_syaken_2', // 査定（車検2ヶ月前）
        'ik_test_drive_syaken_2', // 試乗（車検2ヶ月前）
        'ik_estimate_syaken_2', // 車検見積（車検2ヶ月前）
        'ik_ikou_syaken_2', // 意向（車検2ヶ月前）
        'ik_come_syaken_1', // 来店（車検1ヶ月前）
        'ik_visit_syaken_1', // 訪問（車検1ヶ月前）
        'ik_phone_syaken_1', // 電話（車検1ヶ月前）
        'ik_mail_syaken_1', // メール（車検1ヶ月前）
        'ik_hot_syaken_1', // HOT（車検1ヶ月前）
        'ik_shodan_memo_syaken_1', // 商メモ（車検1ヶ月前）
        'ik_credit_syaken_1', // 残クレ（車検1ヶ月前）
        'ik_assessment_syaken_1', // 査定（車検1ヶ月前）
        'ik_test_drive_syaken_1', // 試乗（車検1ヶ月前）
        'ik_estimate_syaken_1', // 車検見積（車検1ヶ月前）
        'ik_ikou_syaken_1', // 意向（車検1ヶ月前）
        'ik_final_achievement', // 最終実績
        'ik_transfer_base_code', // 異動拠点コード
        'ik_transfer_base_name', // 異動拠点名
        'ik_transfer_user_id', // 異動担当者コード
        'ik_transfer_user_name', // 異動担当者名
        'ik_transfer_ym', // 異動年月
        'ik_latest_ikou_history', //最終意向更新履歴
        'ik_latest_ikou_update', //最終意向更新日時
        'ik_ikou_lock_flg',
        "created_by",
        "updated_by"
    ];

    ###########################
    ## CSVの処理
    ###########################
    
    /**
     * データの登録と更新
     * @param  [type] $values [description]
     * @return [type]         [description]
     */
    public static function merge( $values ) {
        //\Log::debug( $values );
        
        Ikou::updateOrCreate(
            [
                'ik_customer_code' => $values['ik_customer_code'],  //顧客コード
                'ik_syaken_next_date' => $values['ik_syaken_next_date'],  //次回車検日
                'ik_car_base_number' => $values['ik_car_base_number']   //車両番号
            ],
            $values
        );
        
    }

}
