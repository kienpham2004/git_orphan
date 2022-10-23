<?php

namespace App\Models\Append;

use App\Models\AbstractModel;

/**
 * 代替車検推進管理に関するモデル
 */
class DaigaeSyaken extends AbstractModel {
    // テーブル名
    protected $table = 'tb_daigae_syaken';

    // 変更可能なカラム
    protected $fillable = [
        'dsya_base_code_init',  // 拠点 コード
        'dsya_base_name_csv',  // 拠点 名
        'dsya_user_id',  // 担当者 Id
        'dsya_user_code_init',  // 担当者 コード
        'dsya_user_name_csv',  // 担当者 名
        'dsya_customer_code',  // 顧客 コード
        'dsya_customer_name_kanji',  // 顧客 名
        'dsya_gensen_code_name',  // 対象時源泉
        'dsya_customer_tel',  // 自宅TEL
        'dsya_keitai_tel',  // 携帯TEL
        'service_yuchi_fuyou',  // サービス誘致不要
        'dsya_car_manage_number',  // 統合車両管理No.
        'dsya_car_name',  // 車名
        'dsya_car_base_number',  // 登録No.
        'dsya_car_new_old_kbn_name',  // 新中区分
        'dsya_car_buy_type',  // 自他販区分
        'dsya_customer_kouryaku_flg',  // 攻車
        'dsya_first_regist_date_ym',  // 初度登録
        'dsya_syaken_times',  // 車検回数
        'dsya_syaken_next_date',  // 車検期日
        'dsya_inspection_ym',  // 車検年月yyyymm
        'dsya_abc_zone',  // ゾーン区分
        'dsya_daigae_car_name',  // 代替予定車
        'dsya_kounyu_car_name',  // 購入予定車
        'dsya_ciao_course',  // 現チャオコース/プラン
        'dsya_ciao_end_date',  // 会員証有効終期
        'dsya_zankure',  // 残クレ
        'dsya_zankure_manki',  // 残クレ満期
        'dsya_customer_insurance_type',  // 任保加入区分
        'dsya_customer_insurance_type_code',  // 任保加入区分コード
        'dsya_customer_insurance_company',  // 任保社名
        'dsya_customer_insurance_end_date',  // 任保満期
        'contact_6m',  // 6M接触日
        'satei_6m',  // 6M査定
        'shijo_6m',  // 6M試乗
        'syodan_6m',  // 6M商談
        'contact_5m',  // 5M接触日
        'satei_5m',  // 5M査定
        'shijo_5m',  // 5M試乗
        'syodan_5m',  // 5M商談
        'contact_4m',  // 4M接触日
        'satei_4m',  // 4M査定
        'shijo_4m',  // 4M試乗
        'syodan_4m',  // 4M商談
        'contact_3m',  // 3M接触日
        'satei_3m',  // 3M査定
        'shijo_3m',  // 3M試乗
        'syodan_3m',  // 3M商談
        'contact_2m',  // 2M接触日
        'satei_2m',  // 2M査定
        'shijo_2m',  // 2M試乗
        'syodan_2m',  // 2M商談
        'contact_1m',  // 1M接触日
        'satei_1m',  // 1M査定
        'shijo_1m',  // 1M試乗
        'syodan_1m',  // 1M商談
        'contact_0m',  // 車検当月接触日
        'dsya_tmr_kaden',  // TMR架電
        'dsya_tmr_kekka',  // TMR結果
        'dsya_status_katsudo_csv',  // 意向区分（活動日報）
        'dsya_status_katsudo_code',  // 意向区分コード（活動日報）
        'dsya_status_katsudo_date',  // 意向確認日（活動日報）
        'dsya_status_csv',  // 意向区分
        'dsya_status_code',  // 意向区分コード
        'dsya_status_date',  // 意向確認日
        'dsya_syaken_reserve_date',  // 車検予約日
        'dsya_hot_date',  // HOT発生日
        'dsya_hot_car',  // HOT発生車名
        'dsya_keiyaku_car',  // 契約確定車名
        'dsya_syaken_jisshi_date',  // 車検実施日
        'dsya_tasya_syaken',  // 他社車検
        'dsya_daigae_car',  // 自法人代替車名
        'dsya_shiyozumi_kaitori',  // 使用済/買取
        'dsya_massyo_reason',  // 抹消理由
        'dsya_massyo_reason_code',  // 抹消理由コード
        'dsya_massyo_date',  // 顧客抹消日
        'dsya_tenkyo_flg',  // 転居
//        'dsya_csv_umu_flg',  // CSV有無フラグ

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
     * @todo   車点検種別がある場合は基本的に必須項目とする
     */
    public static function merge( $values ) {
        \Log::debug("========================================================adddd");
        \Log::debug( $values );
        
        DaigaeSyaken::updateOrCreate(
            [
                'dsya_customer_code' => $values['dsya_customer_code'],  //顧客コード
                'dsya_car_manage_number' => $values['dsya_car_manage_number'],  //統合車両管理No.
                'dsya_inspection_ym' => $values['dsya_inspection_ym']   //車検年月yyyymm
            ],
            $values
        );
        
    }

}
