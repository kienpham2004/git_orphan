<?php

namespace App\Commands\Modal\Syatenken;

use App\Models\Targetcars;
use App\Commands\Command;
use App\Http\Requests\SearchRequest;

/**
 * 指定IDの車点検内容取得コマンド
 *
 * @author daidv
 */
class FindSyakenByTenkenIdCommand extends Command{

    /** @var integer 検索対象のid */
    protected $id;

    /** @var integer $tenkenFlg */
    protected $tenkenFlg;

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

        "tgc_htc_number",
        "tgc_htc_car",

        "tgc_ciao_course",
        "tgc_ciao_end_date",
        
        "tb_target_cars.tgc_abc_zone",
        "tb_target_cars.tgc_sj_shukka_date",
        
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
        'tb_manage_info.mi_sm_memo_date',
        
        'tb_manage_info.mi_rstc_reserve_status',
        'tb_manage_info.mi_ik_final_achievement',
        
        'tb_manage_info.mi_ctcmi_contact_date',
        
        'tb_manage_info.mi_smart_day',
        
        'tb_manage_info.mi_ik_final_achievement',
        'tb_manage_info.mi_htc_login_flg',

        // 2022/02/17 add field tgc_syaken_jisshi_flg
        'tb_target_cars.tgc_syaken_jisshi_flg',
        'tb_target_cars.tgc_haisen_status'            // 敗戦先
    ];

    public function __construct( $id, $tenkenFlg ){
        $this->id = $id;
        $this->tenkenFlg = $tenkenFlg;
    }

    public function handle(){
        $tgcMObj = TargetCars::joinBase()
                              ->joinInfo()
                              ->find( $this->id, $this->columns );

        $tgcChangeMObj = TargetCars::joinBase()
            ->joinInfo();
        if ($this->tenkenFlg == 1) {
            $tgcChangeMObj = $tgcChangeMObj->whereMatch( 'tgc_inspection_id', 2 );
        } else {
            $tgcChangeMObj = $tgcChangeMObj->whereMatch( 'tgc_inspection_id', 4 );
        }

        $tgcChangeMObj = $tgcChangeMObj->whereMatch( 'tgc_car_manage_number', $tgcMObj->tgc_car_manage_number )
            ->whereMatch( 'tgc_syaken_next_date', $tgcMObj->tgc_syaken_next_date )
            ->whereMatch( 'tgc_customer_code', $tgcMObj->tgc_customer_code )
            ->select($this->columns)
            ->first();

        return $tgcChangeMObj;
    }

}
