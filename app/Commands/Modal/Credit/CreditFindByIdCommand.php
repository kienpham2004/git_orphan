<?php

namespace App\Commands\Modal\Credit;

use App\Models\Credit;
use App\Commands\Command;
use App\Http\Requests\SearchRequest;

/**
 * 指定IDの車点検内容取得コマンド
 *
 * @author yhatsutori
 */
class CreditFindByIdCommand extends Command{

    /** @var integer 検索対象のid */
    protected $id;

    /** @var array 取得カラム */
    protected $columns = [
        "tb_credit.id as number",
        "cre_inspection_ym",
        "cre_customer_id",
        "cre_car_manage_number",
        "cre_base_code",
        "base_short_name",
        "cre_user_id",
        "user_name",
        "cre_customer_code",
        "cre_customer_name_kanji",
        //"cre_gensen_code_name",
        //"cre_customer_category_code_name",
        //"cre_action_pattern_code",
        "cre_customer_postal_code",
        "cre_customer_address",
        "cre_customer_tel",
        "cre_customer_office_tel",
        "cre_car_name",
        "cre_car_year_type",
        "cre_first_regist_date_ym",
        "cre_cust_reg_date",
        "cre_car_base_number",
        "cre_syaken_times",
        "cre_syaken_next_date",
        "cre_customer_kouryaku_flg",
        
        "cre_first_shiharai_date_ym",
        "cre_shiharai_count",
        "cre_credit_hensaihouhou_name",
        "cre_keisan_housiki_kbn",
        "cre_credit_card_select_kbn",
        "cre_credit_manryo_date_ym",
        "cre_sueoki_zankaritsu",
        "cre_last_shiharaikin",
        //"cre_memo_syubetsu",
        
        "cre_status",
        "cre_action",
        "cre_satei_flg",
        "cre_memo",

        "ciao_course",
        "ciao_money",
        "ciao_start_date",
        "ciao_end_date",

        'v_credit_info.tgci_abc_abc',
        'v_credit_info.tgci_ctcsa_contact_date',
        'v_credit_info.tgci_ctcmi_contact_date',
        'v_credit_info.tgci_ctcshi_contact_date'
    ];
    
    public function __construct( $id ){
        $this->id = $id;
    }
    
    public function handle(){
        return Credit::joinBase()
                    ->joinSales()
                    ->joinCiao()
                    ->joinInfo()
                    ->find( $this->id, $this->columns );
    }

}
