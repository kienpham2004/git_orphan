<?php

namespace App\Commands\Modal\Syatenken;

use App\Models\Targetcars;
use App\Commands\Command;
use App\Http\Requests\SearchRequest;

/**
 * 指定IDの車点検内容取得コマンド
 *
 * @author yhatsutori
 */
class SyatenkenFindByIdCommand extends Command{

    /** @var integer 検索対象のid */
    protected $id;

    /** @var array 取得カラム */
    protected $columns = [
        "tb_target_cars.id as number",
        "tgc_inspection_id",
        "tgc_inspection_ym",
        "tgc_customer_id",
        "tgc_car_manage_number",
        "base_id",
        "base_short_name",
        "tgc_user_id",
        "user_name",
        "tb_user_account.deleted_at",
        "tgc_customer_code",
        "tgc_customer_name_kanji",
        "tgc_customer_postal_code",
        "tgc_customer_address",
        "tgc_customer_tel",
        "tgc_customer_office_tel",
        "tgc_car_name",
        "tgc_car_year_type",
        "tgc_first_regist_date_ym",
        "tgc_cust_reg_date",
        "tgc_car_base_number", 
        "tgc_syaken_times",
        "tgc_syaken_next_date",
        "tgc_customer_kouryaku_flg",

        "tgc_customer_insurance_type",
        "tgc_customer_insurance_company",
        "tgc_customer_insurance_end_date",
        
        "tgc_credit_hensaihouhou",
        "tgc_credit_hensaihouhou_name",
        "tgc_first_shiharai_date_ym",
        "tgc_keisan_housiki_kbn",
        "tgc_credit_card_select_kbn",
        "tgc_memo_syubetsu",
        "tgc_shiharai_count",
        "tgc_credit_manryo_date_ym",
        "tgc_sueoki_zankaritsu",
        "tgc_last_shiharaikin",

        "tgc_status",
        "tgc_status_update",
        "tgc_memo",
        "tgc_daigae_car_name",
        "tgc_alert_memo",
        "tgc_htc_number",
        "tgc_htc_car",

        "tgc_ciao_course",
        "tgc_ciao_end_date",
        
        "tb_target_cars.tgc_abc_zone",
        "tb_target_cars.tgc_sj_shukka_date",

        "tb_manage_info.mi_tmr_last_call_result",
        "tb_manage_info.mi_tmr_last_call_date",
        "tb_manage_info.mi_tmr_to_base_comment",
        "tb_manage_info.mi_tmr_in_sub_intention",
        "tb_manage_info.mi_tmr_call_1_status",
        "tb_manage_info.mi_tmr_call_1_result",
        "tb_manage_info.mi_tmr_call_1_date",
        "tb_manage_info.mi_tmr_call_2_status",
        "tb_manage_info.mi_tmr_call_2_result",
        "tb_manage_info.mi_tmr_call_2_date",
        "tb_manage_info.mi_tmr_call_3_status",
        "tb_manage_info.mi_tmr_call_3_result",
        "tb_manage_info.mi_tmr_call_3_date",
        "tb_manage_info.mi_tmr_call_4_status",
        "tb_manage_info.mi_tmr_call_4_result",
        "tb_manage_info.mi_tmr_call_4_date",
        "tb_manage_info.mi_tmr_call_5_status",
        "tb_manage_info.mi_tmr_call_5_result",
        "tb_manage_info.mi_tmr_call_5_date",
        "tb_manage_info.mi_tmr_call_6_status",
        "tb_manage_info.mi_tmr_call_6_result",
        "tb_manage_info.mi_tmr_call_6_date",
        
        'tb_manage_info.mi_rstc_reserve_commit_date',
        'tb_manage_info.mi_rstc_start_date',
        'tb_manage_info.mi_rstc_put_in_date',
        'tb_manage_info.mi_rstc_get_out_date',
        'tb_manage_info.mi_rstc_delivered_date',
        'tb_manage_info.mi_rstc_reserve_status',
        'tb_manage_info.mi_rstc_web_reserv_flg',
        
        'tb_manage_info.mi_ctcmi_contact_date',
        'tb_manage_info.mi_ctcsa_contact_date',
        'tb_manage_info.mi_ctcshi_contact_date',
        'tb_manage_info.mi_ctcsho_contact_date',
        
        'tb_manage_info.mi_rstc_reserve_status',
        'tb_manage_info.mi_ik_final_achievement',
        
        'tb_manage_info.mi_ctcmi_contact_date',
        
        'tb_manage_info.mi_smart_day',
        
        'tb_manage_info.mi_ik_final_achievement',
        
        'tb_manage_info.mi_ctcview_date_tel',          // 最新日時：電話
        'tb_manage_info.mi_ctcview_date_home',         // 最新日時：訪問
        'tb_manage_info.mi_ctcview_date_shop',          // 最新日時：来店
        'tb_manage_info.mi_htc_login_flg',          // HTCログインフラグ
        'tb_target_cars.tgc_mobile_tel',          // 携帯電話番号
        'tgc_lock_flg6', // 6ヶ月ロック
        'tgc_customer_update',     // 最終更新日

        // 2022/02/25 add daigae
        'tb_manage_info.mi_dsya_keiyaku_car',
        'tb_manage_info.mi_dsya_syaken_jisshi_date',
        'tb_manage_info.mi_dsya_syaken_reserve_date',
        'tb_manage_info.mi_satei_max',
        'tb_manage_info.mi_shijo_max',
        'tb_manage_info.mi_syodan_max'
    ];

    public function __construct( $id ){
        $this->id = $id;
    }

    public function handle(){
        $tgcMObj = TargetCars::joinBase()
                              //->JoinSalesAll()
                              ->joinInfo()
                              ->find( $this->id, $this->columns );
        
        return $tgcMObj;
    }

}
