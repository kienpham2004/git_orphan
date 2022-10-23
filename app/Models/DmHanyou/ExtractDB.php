<?php

namespace App\Models\DmHanyou;

use DB;

/**
 * 対象を抽出するDB
 */
class ExtractDB{
    
    #########################
    ## 抽出
    #########################
    
    /**
     * DM対象を取得するSQL
     * @return [type] [description]
     */
    public static function getTargetCustomer(){
        // DM対象者を抽出するSQL
        $sql = "    INSERT INTO tb_customer_dm_1
                    (
                        id,
                        inspection_ym,
                        car_manage_number,
                        base_code,
                        base_name,
                        user_id,
                        user_name,
                        customer_code,
                        customer_name_kanji,
                        gensen_code_name,
                        customer_category_code_name,
                        customer_postal_code,
                        customer_address,
                        customer_tel,
                        customer_office_tel,
                        car_name,
                        car_year_type,
                        first_regist_date_ym,
                        cust_reg_date,
                        car_base_number,
                        car_model,
                        car_service_code,
                        car_frame_number,
                        car_buy_type,
                        car_new_old_kbn_name,
                        syaken_times,
                        syaken_next_date,
                        customer_insurance_type,
                        customer_insurance_company,
                        customer_insurance_end_date,
                        customer_kouryaku_flg,
                        customer_dm_flg,
                        customer_name_kata,
                        credit_hensaihouhou,
                        credit_hensaihouhou_name,
                        first_shiharai_date_ym,
                        keisan_housiki_kbn,
                        credit_card_select_kbn,
                        memo_syubetsu,
                        shiharai_count,
                        sueoki_zankaritsu,
                        last_shiharaikin,
                        customer_kojin_hojin_flg,
                        original_dm_flg,
                        created_at,
                        updated_at,
                        deleted_at,
                        created_by,
                        updated_by
                    )
                    SELECT
                        id,
                        to_char( now(), 'yyyymm' ),
                        car_manage_number,
                        base_code,
                        base_name,
                        user_id,
                        user_name,
                        customer_code,
                        customer_name_kanji,
                        gensen_code_name,
                        customer_category_code_name,
                        customer_postal_code,
                        customer_address,
                        customer_tel,
                        customer_office_tel,
                        car_name,
                        car_year_type,
                        first_regist_date_ym,
                        cust_reg_date,
                        car_base_number,
                        car_model,
                        car_service_code,
                        car_frame_number,
                        car_buy_type,
                        car_new_old_kbn_name,
                        syaken_times,
                        syaken_next_date,
                        customer_insurance_type,
                        customer_insurance_company,
                        customer_insurance_end_date,
                        customer_kouryaku_flg,
                        customer_dm_flg,
                        customer_name_kata,
                        credit_hensaihouhou,
                        credit_hensaihouhou_name,
                        first_shiharai_date_ym,
                        keisan_housiki_kbn,
                        credit_card_select_kbn,
                        memo_syubetsu,
                        shiharai_count,
                        sueoki_zankaritsu,
                        last_shiharaikin,
                        customer_kojin_hojin_flg,
                        customer_dm_flg,
                        created_at,
                        updated_at,
                        deleted_at,
                        created_by,
                        updated_by

                    FROM
                        tb_customer
                        
                    WHERE
                        to_char( updated_at + '+1 month'::interval, 'yyyymm') > to_char( now(), 'yyyymm' ) AND

                        -- csvファイルの存在する顧客のみ
                        (
                            EXISTS 
                            (
                                SELECT
                                    umu_csv_flg
                                FROM
                                    tb_customer_umu
                                WHERE
                                    tb_customer.customer_code = tb_customer_umu.umu_customer_code AND
                                    tb_customer.car_manage_number = tb_customer_umu.umu_car_manage_number
                            )
                        ) ";

        return DB::select( $sql );
    }

}
