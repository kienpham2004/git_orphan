<?php

namespace App\Commands\Extract;

use App\Models\Dm;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 主に車点検リスト用
 * tb_contactだと同じ月に何件か接触をしている可能性があるため、
 * 1件だけを取得
 */
class ExtractDmTenkenCommand extends Command implements ShouldBeQueued{

    /**
     * コンストラクタ
     * @param string $targetYm 対象月
     */
    public function __construct( $targetYm="" ){
        // 対象月の取得
        $this->targetYm = orgIsset( $targetYm, date('Ym') );
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 対象月のDM対象者一覧を取得
        $dmValues = $this->getDmValues( $this->targetYm );

        /**
         * DM対象者情報を登録、もしくは更新する処理
         * 次の値が同じ場合に更新します
         * ・統合車両管理No
         * ・対象車点検区分
         * ・対象月
         */
        foreach( $dmValues as $data ){
            // $dataがarrayではなくobjectなのでキャストしてます。
            $data = (array)$data;
                        
            // 値の登録または更新
            Dm::merge( $data );
        }
    }

    /**
     * 対象月の最新接触一覧を取得
     * @param  string $targetYm [description]
     * @return [type]             [description]
     */
    private function getDmValues( $targetYm="" ){
        // 対象月が空の時は当月を指定
        if( empty( $targetYm ) == True ){
            return [];
        }
        
        // DMを取得できる最大の月
        $maxYm = date( "Ym", strtotime( date("Y-m-01") . '+3 month' ) );

        // 指定された月が、DMを取得できる最大の月を超えていたら処理はしない
        if( intval( $targetYm ) > intval( $maxYm ) ){
            return [];
        }
        
        // 月の指定の箇所を見直す
        $sql = "    SELECT
                        CASE
                            WHEN
                                tgc_inspection_id = 2 THEN 21
                            ELSE
                                tgc_inspection_id
                        END,
                        tgc_inspection_ym,
                        tgc_customer_id,
                        tgc_car_manage_number,
                        tgc_base_code,
                        tgc_base_name,
                        tgc_user_id,
                        tgc_user_name,
                        tgc_customer_code,
                        tgc_customer_name_kanji,
                        tgc_gensen_code_name,
                        tgc_customer_category_code_name,
                        tgc_customer_postal_code,
                        tgc_customer_address,
                        tgc_customer_tel,
                        tgc_customer_office_tel,
                        tgc_car_name,
                        tgc_car_year_type,
                        tgc_first_regist_date_ym,
                        tgc_cust_reg_date,
                        tgc_car_base_number,
                        tgc_car_model,
                        tgc_car_service_code,
                        tgc_car_frame_number,
                        tgc_car_buy_type,
                        tgc_car_new_old_kbn_name,
                        tgc_syaken_times,
                        tgc_syaken_next_date,
                        tgc_customer_insurance_type,
                        tgc_customer_insurance_company,
                        tgc_customer_insurance_end_date,
                        tgc_customer_kouryaku_flg,
                        tgc_customer_dm_flg,
                        tgc_customer_kojin_hojin_flg,
                        tgc_status,
                        tgc_car_type,
                        tgc_abc_zone,
                        tgc_htc_number,
                        tgc_htc_car

                    FROM
                        tb_target_cars tgc
                        
                    WHERE
                        tgc.tgc_inspection_ym = '{$targetYm}' AND
                        tgc.tgc_inspection_id != 4 AND
                        tgc.tgc_inspection_ym != to_char( tgc.tgc_syaken_next_date + '-6 mons'::interval, 'yyyymm') AND
                        ( tgc.tgc_dm_flg != '1' OR tgc.tgc_dm_flg IS NULL ) AND
                        tgc.deleted_at IS null AND
                        
                        -- csvファイルの存在する顧客のみ
                        (
                            EXISTS 
                            (
                                SELECT
                                    umu_csv_flg
                                FROM
                                    tb_customer_umu
                                WHERE
                                    tgc.tgc_customer_code = tb_customer_umu.umu_customer_code AND
                                    tgc.tgc_car_manage_number = tb_customer_umu.umu_car_manage_number
                            )
                        )
                        
                    ORDER BY
                        tgc_car_manage_number ";

        return \DB::select( $sql );
    }

}
