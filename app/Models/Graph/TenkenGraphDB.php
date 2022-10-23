<?php

namespace App\Models\Graph;

use App\Lib\Util\DateUtil;
use App\Original\Util\SessionUtil;
use DB;
use App\Lib\Util\QueryUtil;
use App\Lib\Util\Constants;

/**
 * トレジャーボード車検に関するDB
 */
class TenkenGraphDB{
    
    #############################
    ## トレジャーボード(点検)
    #############################

    /**
     * トレジャーボード(点検)のデータを取得する
     * 処理速度の問題で、クエリビルダーやEloquentを使わずに
     * ほぼ生のSQLを利用（with句を使いたいので）
     * これにより、次のメソッドを変更した
     *
     * private static function subQueryTargetCarsSummary
     *
     * @param $search
     */
    public static function getTenkenIkou( $search ){
        $s = microtime(true);
        
        // 対象反映(月数)
        $target_ym = 6;

        // 対象年月の指定がなければ現時刻のYM
        $from = date('Ym');
        if( !empty( $search->inspection_ym_from ) OR $search->inspection_ym_from != "" ){
            $from = $search->inspection_ym_from;
        }

        // 対象年月の指定がなければ現時刻から７ヶ月とし、
        // 対象年月が指定されている場合は、指定日時から７ヶ月とする
        if ( !empty( $search->inspection_ym_from ) OR $search->inspection_ym_from != "" ) {
            $to = DateUtil::monthLater( $search->inspection_ym_from . "01", $target_ym, 'Ym' );
        } else {
            $to = DateUtil::monthLater( date( 'Ymd' ), $target_ym, 'Ym' );
        }
        
        // 担当者の絞り
        if( isset( $search->user_id ) == True && !empty( $search->user_id ) == True ){
            if ($search->user_id == Constants::CONS_TAISHOKUSHA_CODE) {
                $queryUser = " account.deleted_at is not null AND ";
            } else {
                $queryUser = " account.user_id = '{$search->user_id}' AND ";
            }
        }else{
          $queryUser = "";
        }
        
//        // チャオ
//        $queryCiao = "";
        
//        if( !empty($search->ciao) ) {
//            // 両方にチェック
//            if( in_array("0", $search->ciao) && in_array("1", $search->ciao) ){
//                $queryCiao = "(tgc_ciao_course is null OR tgc_ciao_course is not null) AND ";
//            }
//            // 無にチェック
//            elseif( in_array("0", $search->ciao) ){
//                $queryCiao = "tgc_ciao_course is null AND ";
//            }
//            // 有にチェック
//            elseif( in_array("1", $search->ciao) ){
//                $queryCiao = "tgc_ciao_course is not null AND ";
//                //ciao_course == 'SS'　の場合
//                //　・syaken_next_date < ciao_end_date　→　有り
//                $queryCiao .= "((tgc_ciao_course = 'SS' AND tgc_syaken_next_date < tgc_ciao_end_date) OR ";
//                //ciao_course != 'SS'　の場合
//                //　・syaken_next_date <= ciao_end_date　→　有り
//                $queryCiao .= "(tgc_ciao_course != 'SS' AND tgc_syaken_next_date <= tgc_ciao_end_date)) AND";
//            }
//        }
        // チャオ
        $queryCiao = "";
        if( !empty($search->ciao) ) {
            $queryCiao = QueryUtil::ciaoSqlCheckCondition($search->ciao);
        }

        // ログイン者の拠点のデータのみ表示
        $querLoginBase = "";
        
        // ログイン情報を取得
        $loginAccountObj = SessionUtil::getUser();
        
        // 店長, 工場長, 営業担当, CAの人の時は、所属拠点のみ表示
        //if( in_array( $loginAccountObj->getRolePriority(), [4,5,6,7] ) ){
        if( in_array( $loginAccountObj->getRolePriority(), [6,7] ) ){
            // 拠点IDを取得
            $base_id = $loginAccountObj->getBaseId();
            $querLoginBase = " account.base_id = '{$base_id}' AND ";
        }
        
        // 点検区分が空の時
        if( empty( $search->inspect_divs ) ){
          $search->inspect_divs = '3';
        }

        // メインのSQL
         $sql  = "  
                    WITH tmp_tb_user_account AS (
                        SELECT 
                            account.id as user_id, account.user_code, account.user_name, 
                            account.base_id, base.base_code, base.base_name, base.base_short_name,
                            account.deleted_at
                        FROM 
                            tb_user_account account
                        LEFT JOIN tb_base base ON
                            base.id = account.base_id AND
                            base.deleted_at IS NULL  
                        --WHERE
                        --    account.deleted_at IS NULL
                    ),            
                    tmp_tb_target_cars AS (
                      SELECT
                        tb_target_cars.id as target_id,
                        --tb_target_cars.tgc_base_id,
                        tgc_customer_name_kanji,
                        tgc_customer_kouryaku_flg,
                        tgc_syaken_times,
                        tgc_car_name,
                        tgc_car_manage_number,
                        tgc_syaken_next_date,
                        tgc_inspection_ym,
                        tgc_user_id,
                        tgc_status_update,

                        CASE
                          WHEN info.mi_htc_login_flg = '1' THEN 7
                          WHEN info.mi_tmr_in_sub_intention = '入庫意向有' THEN 1
                          WHEN info.mi_tmr_in_sub_intention = '自社予約済' THEN 2
                          WHEN info.mi_tmr_in_sub_intention = '代替意向有' THEN 3
                          WHEN info.mi_tmr_in_sub_intention = '他社予約済' THEN 4
                          WHEN info.mi_tmr_in_sub_intention = '代替意向無' THEN 5
                          WHEN info.mi_tmr_in_sub_intention = '入庫意向無' THEN 6
                          ELSE 0
                        END as tmr_status,


                        CASE
                            -- 入庫済
                            WHEN
                                info.mi_rstc_reserve_status is not null
                                AND info.mi_rstc_reserve_status = '出荷済'
                            THEN 102

                            -- 予約済
                            WHEN
                                info.mi_rstc_reserve_status is not null
                                AND info.mi_rstc_reserve_status <> '出荷済'
                            THEN 101
                            -- その他
                            WHEN
                                tb_target_cars.tgc_status IN ('21', '22')
                            THEN 104
                            
                            -- 自社代替
                            WHEN
                                tb_target_cars.tgc_status = '23'
                            THEN 103

                            -- 入庫意向
                            WHEN
                                tb_target_cars.tgc_status = '2'
                            THEN 11
                            -- 代替意向
                            WHEN
                                tb_target_cars.tgc_status = '3'
                            THEN 12
                            -- 点検意向無
                            WHEN
                                tb_target_cars.tgc_status = '4'
                            THEN 13
                            -- 抹消・転居
                            WHEN
                                tb_target_cars.tgc_status = '5'
                            THEN 14
                            -- 未確認
                            WHEN
                                (tb_target_cars.tgc_status = '1' OR tb_target_cars.tgc_status IS NULL)
                            THEN 15
                            ELSE 0
                        END as target_status,

                        tb_target_cars.tgc_alert_memo,

                        -- 2022/03/15 add field
                        info.mi_ctcshi_contact_date,
                        info.mi_ctcsho_contact_date,
                        tgc_credit_manryo_date_ym,
                        tgc_credit_hensaihouhou_name,
                        info.mi_rstc_reserve_status,
                        
                        info.mi_smart_day,
                        info.mi_ik_final_achievement,
                        info.mi_rstc_delivered_date,
                        info.mi_rcl_recall_flg,
                        info.mi_rstc_web_reserv_flg,
                        info.mi_ctc_seiyaku_flg,
                        info.mi_dsya_syaken_jisshi_date,

                        -- 試乗
                        CASE
                            WHEN
                                info.mi_shijo_6m is not null
                                OR info.mi_shijo_5m is not null
                                OR info.mi_shijo_4m is not null
                                OR info.mi_shijo_3m is not null
                                OR info.mi_shijo_2m is not null
                                OR info.mi_shijo_1m is not null
                                THEN 1
                            ELSE 0
                        END AS mi_shijo_6m1m,

                        -- 商談
                        CASE
                            WHEN
                                info.mi_syodan_6m is not null
                                OR info.mi_syodan_5m is not null
                                OR info.mi_syodan_4m is not null
                                OR info.mi_syodan_3m is not null
                                OR info.mi_syodan_2m is not null
                                OR info.mi_syodan_1m is not null
                                THEN 1
                            ELSE 0
                        END AS mi_syodan_6m1m,

                        -- 査定
                        CASE
                            WHEN
                                info.mi_satei_6m is not null
                                OR info.mi_satei_5m is not null
                                OR info.mi_satei_4m is not null
                                OR info.mi_satei_3m is not null
                                OR info.mi_satei_2m is not null
                                OR info.mi_satei_1m is not null
                                THEN 1
                            ELSE 0
                        END AS mi_satei_6m1m,

                        tgc_ciao_course,
                        tgc_ciao_end_date,

                        umu_csv_flg,

                        -- 2022/02/17 add select field
                        CASE
                            WHEN tb_target_cars.tgc_sj_shukka_date is null AND tb_target_cars.tgc_syaken_times = 1 THEN 2
                            WHEN tb_target_cars.tgc_sj_shukka_date is not null THEN 1
                            WHEN tb_target_cars.tgc_sj_shukka_date is null AND tb_target_cars.tgc_syaken_times >= 2 THEN 3
                            ELSE 0
                        END AS jisshi_flg

                      FROM
                        tb_target_cars

                      LEFT JOIN tb_manage_info info ON
                          tb_target_cars.tgc_car_manage_number = info.mi_car_manage_number AND
                          tb_target_cars.tgc_inspection_ym = info.mi_inspection_ym AND
                          tb_target_cars.tgc_inspection_id = info.mi_inspection_id

                      --LEFT JOIN v_ciao ON
                      --  tb_target_cars.tgc_customer_code = v_ciao.ciao_customer_code AND
                      --  tb_target_cars.tgc_car_manage_number = v_ciao.ciao_car_manage_number AND
                      --  tb_target_cars.tgc_inspection_ym <= to_char( v_ciao.ciao_end_date, 'yyyymm' ) AND
                      --  v_ciao.deleted_at is null

                      LEFT JOIN tb_customer_umu ON
                        tb_target_cars.tgc_customer_code = tb_customer_umu.umu_customer_code AND
                        tb_target_cars.tgc_car_manage_number = tb_customer_umu.umu_car_manage_number AND
                        tb_customer_umu.deleted_at is null

                      WHERE
                        --tb_target_cars.tgc_base_code = '{$search->base_code}' AND
                        tgc_inspection_id = '{$search->inspect_divs}' AND
                        {$queryCiao}
                        tgc_inspection_ym between '{$from}' and '{$to}'                    
                    )
                    SELECT
                        account.user_id,
                        account.user_name,
                        account.base_id,
                        account.base_code,
                        target.tgc_user_id,
                        target.target_id,
                        --target.tgc_base_id,
                        target.tgc_customer_name_kanji,
                        target.tgc_customer_kouryaku_flg,
                        target.tgc_syaken_times,
                        target.tgc_car_name,
                        target.tgc_car_manage_number,
                        target.tgc_syaken_next_date,
                        target.tgc_inspection_ym,
                        target.tmr_status,
                        target.target_status,
                        target.tgc_status_update,

                        -- アラートメモ
                        target.tgc_alert_memo,

                        -- 査定データ
                        target.mi_smart_day,

                        -- チャオ
                        target.tgc_ciao_course,
                        target.tgc_ciao_end_date,

                        target.umu_csv_flg,
                        
                        target.mi_rcl_recall_flg,
                        target.mi_rstc_web_reserv_flg,
                        target.mi_ctc_seiyaku_flg,
                        -- 2022/02/17 add select field
                        target.mi_shijo_6m1m,
                        target.mi_syodan_6m1m,
                        target.mi_satei_6m1m,
                        target.mi_dsya_syaken_jisshi_date,
                        target.jisshi_flg,
                        -- 2022/03/15 add field
                        target.mi_ctcshi_contact_date,
                        target.mi_ctcsho_contact_date,
                        target.tgc_credit_manryo_date_ym,
                        target.tgc_credit_hensaihouhou_name,
                        target.mi_rstc_reserve_status,
                        target.mi_rstc_delivered_date
                    FROM
                        tmp_tb_user_account account

                    left join tmp_tb_target_cars as target on
                      account.user_id = target.tgc_user_id

                    WHERE
                      --tb_user_account.deleted_at is null AND
                      account.base_code = '{$search->base_code}' AND
                      {$queryUser}
                      {$querLoginBase}
                      target.target_id IS NOT NULL

                    GROUP BY
                        user_id,
                        user_name,      
                        account.base_id,
                        account.base_code,                                                                  
                        target.target_id,
                        target.tgc_user_id,
                        --target.tgc_base_id,
                        target.tgc_customer_name_kanji,
                        target.tgc_customer_kouryaku_flg,
                        target.tgc_syaken_times,
                        target.tgc_car_name,
                        target.tgc_car_manage_number,
                        target.tgc_syaken_next_date,
                        target.tgc_inspection_ym,
                        target.tmr_status,
                        target.target_status,
                        target.tgc_alert_memo,
                        target.mi_smart_day,
                        target.tgc_ciao_course,
                        target.tgc_ciao_end_date,
                        target.umu_csv_flg,
                        target.tgc_status_update,
                        target.mi_rcl_recall_flg,
                        target.mi_rstc_web_reserv_flg,
                        target.mi_ctc_seiyaku_flg,
                        target.mi_shijo_6m1m,
                        target.mi_syodan_6m1m,
                        target.mi_satei_6m1m,
                        target.mi_dsya_syaken_jisshi_date,
                        target.jisshi_flg,
                        target.mi_ctcshi_contact_date,
                        target.mi_ctcsho_contact_date,
                        target.tgc_credit_manryo_date_ym,
                        target.tgc_credit_hensaihouhou_name,
                        target.mi_rstc_reserve_status,
                        target.mi_rstc_delivered_date

                    ORDER BY
                        user_id asc,
                        target.umu_csv_flg asc,
                        target.tgc_inspection_ym asc,
                        target.target_status asc,
                        target.tmr_status asc,
                        target.tgc_syaken_next_date asc ";

        $record = DB::select( $sql );

        return $record;
    }

}
