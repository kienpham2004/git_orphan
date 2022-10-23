<?php

namespace App\Commands\Modal\Hoken;

use App\Models\Insurance;
use App\Commands\Command;
use App\Http\Requests\SearchRequest;

/**
 * 指定IDの保険内容取得コマンド
 *
 * @author yhatsutori
 */
class InsuranceFindByIdCommand extends Command{

    /** @var integer 検索対象のid */
    protected $id;

    /** @var array 取得カラム*/
    protected $columns = [
        "tb_insurance.id as number",
        "insu_inspection_target_ym",
        "insu_inspection_ym",
        "insu_jisya_tasya",
        "base_code",
        "base_name",
//        "tb_base.base_code",        // 拠点コード(マスタ)
        "base_short_name",  // 拠点略称(マスタ)
        "insu_insurance_end_date",
        "insu_insurance_start_date",
//        "insu_user_id",
//        "insu_user_name",
        "user_code",  // 担当者コード(マスタ)
        "user_name",// 担当者名(マスタ)
        "tb_user_account.deleted_at",
        "insu_kikan",
        "insu_syumoku_code",
        "insu_syumoku",
        "insu_company_code",
        "insu_company_name",
        "insu_uketuke_kbn_code",
        "insu_uketuke_kbn",
        "insu_customer_name",
        "insu_tel_no",
        "insu_syoken_number",
        "insu_syadai_number",
        "insu_car_base_number",
        "insu_syaryo_type",
        "insu_jisseki_hokenryo",
        "insu_daisya",
        "insu_tokyu",
        "insu_jinsin_syogai",
        "insu_cashless_flg",
        "insu_kanyu_dairiten",
        "insu_syoken_sindan_date",
        "insu_keiyaku_kekka",
        
        "insu_syoken_extract_number",
        "insu_syoken_suishin_1",
        "insu_syoken_suishin_2",
        "insu_syoken_source",
        
        "insu_status",
        "insu_action",
        "insu_memo",
        "insu_contact_plan_date",
        "insu_contact_date",
        "insu_contact_mail_tuika",
        "insu_contact_jigu",
        "insu_contact_taisyo",
        "insu_contact_taisyo_name",
        "insu_contact_syaryo_type",
        "insu_contact_daisya",
        "insu_status_gensen",
        
        "insu_contact_period",
        "insu_contact_daigae_car_name",
        "insu_contact_keijyo_ym",
        "insu_updated_history",
        "insu_kakutoku_company_name",
        
        "insu_add_tetsuduki_date",
        "insu_add_tetsuduki_detail",
        "insu_add_keijyo_date",
        "insu_add_keijyo_ym",
        
        "insu_staff_info_toss",  //スタッフからの情報トス
        "insu_toss_staff_name",  //情報トススタッフ名
        "insu_pair_fleet"      ,  //ペアフリート
        "insu_alert_memo"         //伝達事項

    ];

    public function __construct( $id ){
        $this->id = $id;
    }
    
    public function handle(){
        return Insurance::joinBase()
                        //->joinSales()
                        ->find( $this->id, $this->columns );
    }

}
