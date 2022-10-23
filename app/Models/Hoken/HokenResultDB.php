<?php

namespace App\Models\Hoken;

use DB;

/**
 * 実績集計に関するDB
 */
class HokenResultDB{
    
    #########################
    ## ベースとなるSQL
    #########################

    /**
     * 当月実施を取得するSQL
     * @return [type] [description]
     */
    public static function getStatusSql( $search ){
        // 2017-11-24 初鳥 追加
        // 自社か他社かの検索条件
        $insu_jisya_tasya = "";
        
        // 自社か他社かにより、条件を変更
        if( $search->insu_jisya_tasya == "jisya" ){
            $insu_jisya_tasya = " insu_jisya_tasya = '自社分' AND ";
            
        }else if( $search->insu_jisya_tasya == "tasya" ){
            $insu_jisya_tasya = "
                (
                    insu_jisya_tasya = '他社分' OR
                    insu_jisya_tasya = '純新規'
                ) AND ";
        }
        
        //
        $sql = "    WITH tmp_tb_user_account AS (
                        SELECT 
                            account.id as user_id, account.user_code, account.user_name,account.deleted_at, 
                            account.base_id, base.base_code, base.base_name, base.base_short_name
                        FROM 
                            tb_user_account account
                        LEFT JOIN tb_base base ON
                            base.id = account.base_id AND
                            base.deleted_at IS NULL  
                        --WHERE
                        --    account.deleted_at IS NULL
                    ),          
                     target_base AS(
                        SELECT
                            *,                            
                            -- 計上月か、保険対象月を対象とする
                            CASE
                                WHEN
                                    insu_contact_keijyo_ym IS NOT NULL
                                    THEN insu_contact_keijyo_ym
                                ELSE
                                    -- 主に自社分対応ですが、対象年月と、満期年月が同じ時人を対象とする為
                                    -- 対象年月軸とする
                                    insu_inspection_ym
                            END AS insu_inspection_make_ym
                            
                        FROM
                            tb_insurance insu

                        WHERE
                            -- 非表示は除外
                            insu.deleted_at IS NULL
                            -- AND insu_inspection_target_ym > '201806'
                            -- AND insu_inspection_target_ym > to_char( (current_timestamp - interval '6 months'), 'yyyymm'::text)
                    ),
                    -- 保険の意向の台数を取得
                    tb_status AS(
                        SELECT
                            account.base_id,                        
                            account.base_code,
                            account.base_short_name,
                            account.user_id,                             
                            --account.user_name,
                            CASE
                                WHEN account.deleted_at IS NOT NULL 
                                THEN account.user_name || ' (退職者)'
                                ELSE account.user_name
                            END AS user_name,                                                        

                            1 as target,
                            
                            -- 未接触
                            CASE
                                WHEN insu.insu_status IS NULL THEN 1
                                ELSE 0
                            END as status_0,

                            -- 見込度:30％
                            CASE
                                WHEN insu.insu_status = 1 THEN 1
                                ELSE 0
                            END as status_1,

                            -- 見込度:50％
                            CASE
                                WHEN insu.insu_status = 2 THEN 1
                                ELSE 0
                            END as status_2,

                            -- 見込度:70％
                            CASE
                                WHEN insu.insu_status = 3 THEN 1
                                ELSE 0
                            END as status_3,

                            -- 見込度:90％
                            CASE
                                WHEN insu.insu_status = 4 THEN 1
                                ELSE 0
                            END as status_4,

                            -- 敗戦
                            CASE
                                WHEN insu.insu_status = 5 THEN 1
                                ELSE 0
                            END as status_5,

                            -- 獲得済
                            CASE
                                WHEN insu.insu_status = 6 THEN 1
                                ELSE 0
                            END as status_6,

                            -- 未接触敗戦
                            CASE
                                WHEN insu.insu_status = 99 THEN 1
                                ELSE 0
                            END as status_99,

                            -- 更新済（同条件）
                            CASE
                                WHEN insu.insu_status = 21 THEN 1
                                ELSE 0
                            END as status_21,

                            -- 更新済（変更あり）
                            CASE
                                WHEN insu.insu_status = 22 THEN 1
                                ELSE 0
                            END as status_22,

                            -- 更新予定
                            CASE
                                WHEN insu.insu_status = 23 THEN 1
                                ELSE 0
                            END as status_23,

                            -- 未継続見込
                            CASE
                                WHEN insu.insu_status = 24 THEN 1
                                ELSE 0
                            END as status_24,

                            -- 未継続確定
                            CASE
                                WHEN insu.insu_status = 25 THEN 1
                                ELSE 0
                            END as status_25,

                           -- 長期の確認
                            CASE
                                WHEN insu.insu_status = 26 THEN 1
                                ELSE 0
                            END as status_26,

                            -- 受注時
                            CASE
                                WHEN
                                    insu.insu_status = 6 AND
                                    insu.insu_status_gensen = 1
                                    THEN 1
                                ELSE 0
                            END as status_gensen_1,

                            -- サービス入庫時
                            CASE
                                WHEN
                                    insu.insu_status = 6 AND
                                    insu.insu_status_gensen = 2
                                    THEN 1
                                ELSE 0
                            END as status_gensen_2,

                            -- その他
                            CASE
                                WHEN
                                    insu.insu_status = 6 AND
                                    insu.insu_status_gensen = 3
                                    THEN 1
                                ELSE 0
                            END as status_gensen_3,

                            -- 保有期接触時
                            CASE
                                WHEN
                                    insu.insu_status = 6 AND
                                    insu.insu_status_gensen = 4
                                    THEN 1
                                ELSE 0
                            END as status_gensen_4,

                            -- 満期獲得済
                            CASE
                                WHEN
                                    insu.insu_status = 6 AND
                                    insu_jisya_tasya = '他社分'
                                    THEN 1
                                ELSE 0
                            END as status_6_manki,

                            -- 30日前継続
                            CASE
                                WHEN
                                    -- 保険満了日の30日の以前に接触をされているか
                                    insu.insu_contact_date < to_date( to_char( insu_insurance_end_date + '-30 days'::interval, 'yyyy-mm-dd'::text), 'yyyy-mm-dd' ) AND
                                    (
                                        insu.insu_status = 21 OR
                                        insu.insu_status = 22
                                    )
                                    THEN 1
                                ELSE 0
                            END as status_30mae,

                            -- HSS接触
                            CASE
                                WHEN
                                    -- 治具にHSSを選択している人(自加入)
                                    insu.insu_contact_jigu = 1 AND
                                    (
                                        insu.insu_status = 21 OR
                                        insu.insu_status = 22
                                    )
                                    THEN 1

                                WHEN
                                    -- 治具にHSSを選択している人(他加入)
                                    insu.insu_contact_jigu = 1 AND
                                    insu.insu_status = 6
                                    THEN 1

                                ELSE 0
                            END as status_hss,

                            -- 接触車両保険付帯
                            CASE
                                WHEN
                                    -- 車両保険付帯が有りの人
                                    insu.insu_contact_syaryo_type IS NOT NULL AND
                                    (
                                        insu.insu_status = 21 OR
                                        insu.insu_status = 22
                                    )
                                    THEN 1

                                ELSE 0
                            END as status_syaryo,

                            -- 代車特約付帯
                            CASE
                                WHEN
                                    -- 代車特約付帯が有りの人(自加入)
                                    insu.insu_contact_daisya = 1 AND
                                    (
                                        insu.insu_status = 21 OR
                                        insu.insu_status = 22
                                    )
                                    THEN 1

                                WHEN
                                    -- 代車特約付帯が有りの人(他加入)
                                    insu.insu_contact_daisya = 1 AND
                                    insu.insu_status = 6
                                    THEN 1

                                ELSE 0
                            END as status_daisya,

                            -- トス
                            CASE
                                WHEN
                                    insu.insu_staff_info_toss = 1 AND
                                    insu.insu_status = 6
                                    THEN 1
                                ELSE 0
                            END as status_toss,

                            -- ペアフリ
                            CASE
                                WHEN
                                    insu.insu_pair_fleet = 1 AND
                                    insu.insu_status = 6
                                    THEN 1
                                ELSE 0
                            END as status_pair_fleet

                        FROM
                            target_base insu
                            
                        LEFT JOIN tmp_tb_user_account account ON
                            account.user_id = insu.insu_user_id                        
                            
                        WHERE
                            -- 主に自社分対応ですが、対象年月と、満期年月が同じ時人を対象とする
                            insu.insu_inspection_make_ym = '{$search->inspection_ym_from}' AND

                            {$insu_jisya_tasya}

                            -- 対象外のステータスのお客様は、対象から除外
                            coalesce( insu.insu_status, 0 ) <> 100 AND

                            -- 非表示は除外
                            insu.deleted_at IS NULL AND

                            -- 拠点コードのない値は除外
                            account.base_code IS NOT NULL
                    ) ";
        
        return $sql;
    }
    
    #########################
    ## 検索条件をまとめる処理
    #########################

    /**
     * 検索条件を取得し、検索条件を作る
     * @param  [type] &$search [description]
     * @return [type]                [description]
     */
    public static function getWhereSql( $search, $groupName="" ){
        // 検索条件の取得
        $whereSql = "";
        $whereList = array();

        // 対象拠点
        if( !empty( $search->base_code ) == True ){
            if( $groupName == 3 ){
                $whereList[] = "base_code = '{$search->base_code}'";
            }else{
                $whereList[] = "status.base_code = '{$search->base_code}'";
            }
        }

        if( !empty( $whereList ) == True ){
            $whereSql = " WHERE " . implode( " AND ", $whereList );
        }

        return $whereSql;
    }

    #########################
    ## 各拠点の集計データの取得
    #########################

    /**
     * 拠点単位で実施率を取得する
     *
     * @param unknown $search
     */
    public static function summaryBase( $search, $outputFlg="" ) {
        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereSql = HokenResultDB::getWhereSql( $search );
        
        // 当月実施を取得するSQL
        $statusSql = HokenResultDB::getStatusSql( $search );

        $sql = "    {$statusSql},

                    -- 当月実績の総数を取得
                    tb_status_total AS (
                        SELECT
                            base_code,
                            base_short_name,

                            staff_num.plan_value,

                            COALESCE( sum( target ), 0 ) as target_count,

                            COALESCE( sum( status_0 ), 0 ) as status_0_count,
                            COALESCE( sum( status_1 ), 0 ) as status_1_count,
                            COALESCE( sum( status_2 ), 0 ) as status_2_count,
                            COALESCE( sum( status_3 ), 0 ) as status_3_count,
                            COALESCE( sum( status_4 ), 0 ) as status_4_count,
                            COALESCE( sum( status_5 ), 0 ) as status_5_count,
                            COALESCE( sum( status_6 ), 0 ) as status_6_count,
                            COALESCE( sum( status_99 ), 0 ) as status_99_count,

                            COALESCE( sum( status_21 ), 0 ) as status_21_count,
                            COALESCE( sum( status_22 ), 0 ) as status_22_count,
                            COALESCE( sum( status_23 ), 0 ) as status_23_count,
                            COALESCE( sum( status_24 ), 0 ) as status_24_count,
                            COALESCE( sum( status_25 ), 0 ) as status_25_count,
                            COALESCE( sum( status_26 ), 0 ) as status_26_count,

                            COALESCE( sum( status_gensen_1 ), 0 ) as status_gensen_1_count,
                            COALESCE( sum( status_gensen_2 ), 0 ) as status_gensen_2_count,
                            COALESCE( sum( status_gensen_3 ), 0 ) as status_gensen_3_count,
                            COALESCE( sum( status_gensen_4 ), 0 ) as status_gensen_4_count,
                            COALESCE( sum( status_6_manki ), 0 ) as status_6_manki_count,

                            COALESCE( sum( status_30mae ), 0 ) as status_30mae_count,
                            COALESCE( sum( status_hss ), 0 ) as status_hss_count,
                            COALESCE( sum( status_syaryo ), 0 ) as status_syaryo_count,
                            COALESCE( sum( status_daisya ), 0 ) as status_daisya_count,

                            COALESCE( sum( status_toss ), 0 ) as status_toss_count,
                            COALESCE( sum( status_pair_fleet ), 0 ) as status_pair_fleet_count

                        FROM
                            tb_status
                            
                        LEFT JOIN tb_insurance_staff_num staff_num on
                            staff_num.plan_base_code = tb_status.base_code::text AND
                            staff_num.plan_year || staff_num.plan_month =  '{$search->inspection_ym_from}'

                        GROUP BY
                            base_code,
                            base_short_name,
                            staff_num.plan_value
                    ) ";
        
        // 総数の時の処理
        if( $outputFlg == "total" ){
            // 総数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            '合計' as user_name
                            , '' as base_code
                            , '' as base_short_name
                            , sum ( plan_value ) as plan_value

                            , sum( target_count ) as target_count

                            , sum( status_0_count ) as status_0_count
                            , sum( status_1_count ) as status_1_count
                            , sum( status_2_count ) as status_2_count
                            , sum( status_3_count ) as status_3_count
                            , sum( status_4_count ) as status_4_count
                            , sum( status_5_count ) as status_5_count
                            , sum( status_6_count ) as status_6_count
                            , sum( status_99_count ) as status_99_count

                            , sum( status_21_count ) as status_21_count
                            , sum( status_22_count ) as status_22_count
                            , sum( status_23_count ) as status_23_count
                            , sum( status_24_count ) as status_24_count
                            , sum( status_25_count ) as status_25_count
                            , sum( status_26_count ) as status_26_count

                            , sum( status_gensen_1_count ) as status_gensen_1_count
                            , sum( status_gensen_2_count ) as status_gensen_2_count
                            , sum( status_gensen_3_count ) as status_gensen_3_count
                            , sum( status_gensen_4_count ) as status_gensen_4_count
                            , sum( status_6_manki_count ) as status_6_manki_count

                            , sum( status_30mae_count ) as status_30mae_count
                            , sum( status_hss_count ) as status_hss_count
                            , sum( status_syaryo_count ) as status_syaryo_count
                            , sum( status_daisya_count ) as status_daisya_count

                            , sum( status_toss_count ) as status_toss_count
                            , sum( status_pair_fleet_count ) as status_pair_fleet_count
                        FROM
                            tb_status_total status

                        {$whereSql} ";
        }else{
            // 台数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            '' as user_name
                            , status.base_code
                            , status.base_short_name
                            , plan_value

                            , COALESCE( target_count, 0 ) as target_count

                            , COALESCE( status_0_count, 0 ) as status_0_count
                            , COALESCE( status_1_count, 0 ) as status_1_count
                            , COALESCE( status_2_count, 0 ) as status_2_count
                            , COALESCE( status_3_count, 0 ) as status_3_count
                            , COALESCE( status_4_count, 0 ) as status_4_count
                            , COALESCE( status_5_count, 0 ) as status_5_count
                            , COALESCE( status_6_count, 0 ) as status_6_count
                            , COALESCE( status_99_count, 0 ) as status_99_count

                            , COALESCE( status_21_count, 0 ) as status_21_count
                            , COALESCE( status_22_count, 0 ) as status_22_count
                            , COALESCE( status_23_count, 0 ) as status_23_count
                            , COALESCE( status_24_count, 0 ) as status_24_count
                            , COALESCE( status_25_count, 0 ) as status_25_count
                            , COALESCE( status_26_count, 0 ) as status_26_count

                            , COALESCE( status_gensen_1_count, 0 ) as status_gensen_1_count
                            , COALESCE( status_gensen_2_count, 0 ) as status_gensen_2_count
                            , COALESCE( status_gensen_3_count, 0 ) as status_gensen_3_count
                            , COALESCE( status_gensen_4_count, 0 ) as status_gensen_4_count
                            , COALESCE( status_6_manki_count, 0 ) as status_6_manki_count

                            , COALESCE( status_30mae_count, 0 ) as status_30mae_count
                            , COALESCE( status_hss_count, 0 ) as status_hss_count
                            , COALESCE( status_syaryo_count, 0 ) as status_syaryo_count
                            , COALESCE( status_daisya_count, 0 ) as status_daisya_count

                            , COALESCE( status_toss_count, 0 ) as status_toss_count
                            , COALESCE( status_pair_fleet_count, 0 ) as status_pair_fleet_count
                        FROM
                            tb_status_total status

                        {$whereSql}

                        ORDER BY
                            status.base_code ASC ";
        }
        
        return DB::select( $sql );
    }

    #########################
    ## 各担当の集計データの取得
    #########################

    /**
     * 担当者単位で実施率を取得する
     * @param unknown $search
     */
    public static function summaryStaff( $search, $outputFlg="" ) {
        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereSql = HokenResultDB::getWhereSql( $search );

        if( !empty( $whereSql ) ){
            $whereSql .= " AND status.user_name IS NOT NULL ";
        }else{
            $whereSql = " WHERE status.user_name IS NOT NULL ";
        }

        // 当月実施を取得するSQL
        $statusSql = HokenResultDB::getStatusSql( $search );
        
        $sql = "    {$statusSql},

                    -- 当月実績の総数を取得
                    tb_status_total AS (
                        SELECT
                            user_name,
                            base_code,
                            base_short_name,

                            COALESCE( sum( target ), 0 ) as target_count,

                            COALESCE( sum( status_0 ), 0 ) as status_0_count,
                            COALESCE( sum( status_1 ), 0 ) as status_1_count,
                            COALESCE( sum( status_2 ), 0 ) as status_2_count,
                            COALESCE( sum( status_3 ), 0 ) as status_3_count,
                            COALESCE( sum( status_4 ), 0 ) as status_4_count,
                            COALESCE( sum( status_5 ), 0 ) as status_5_count,
                            COALESCE( sum( status_6 ), 0 ) as status_6_count,
                            COALESCE( sum( status_99 ), 0 ) as status_99_count,

                            COALESCE( sum( status_21 ), 0 ) as status_21_count,
                            COALESCE( sum( status_22 ), 0 ) as status_22_count,
                            COALESCE( sum( status_23 ), 0 ) as status_23_count,
                            COALESCE( sum( status_24 ), 0 ) as status_24_count,
                            COALESCE( sum( status_25 ), 0 ) as status_25_count,
                            COALESCE( sum( status_26 ), 0 ) as status_26_count,

                            COALESCE( sum( status_gensen_1 ), 0 ) as status_gensen_1_count,
                            COALESCE( sum( status_gensen_2 ), 0 ) as status_gensen_2_count,
                            COALESCE( sum( status_gensen_3 ), 0 ) as status_gensen_3_count,
                            COALESCE( sum( status_gensen_4 ), 0 ) as status_gensen_4_count,
                            COALESCE( sum( status_6_manki ), 0 ) as status_6_manki_count,
                            
                            COALESCE( sum( status_30mae ), 0 ) as status_30mae_count,
                            COALESCE( sum( status_hss ), 0 ) as status_hss_count,
                            COALESCE( sum( status_syaryo ), 0 ) as status_syaryo_count,
                            COALESCE( sum( status_daisya ), 0 ) as status_daisya_count,

                            COALESCE( sum( status_toss ), 0 ) as status_toss_count,
                            COALESCE( sum( status_pair_fleet ), 0 ) as status_pair_fleet_count
                        FROM
                            tb_status

                        GROUP BY
                            user_name,
                            base_code,
                            base_short_name
                    ) ";
        
        // 総数の時の処理
        if( $outputFlg == "total" ){
            // 総数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            '合計' as user_name
                            , '' as base_code
                            , '' as base_short_name
                            , sum( staff_num.plan_value ) as plan_value

                            , sum( target_count ) as target_count

                            , sum( status_0_count ) as status_0_count
                            , sum( status_1_count ) as status_1_count
                            , sum( status_2_count ) as status_2_count
                            , sum( status_3_count ) as status_3_count
                            , sum( status_4_count ) as status_4_count
                            , sum( status_5_count ) as status_5_count
                            , sum( status_6_count ) as status_6_count
                            , sum( status_99_count ) as status_99_count

                            , sum( status_21_count ) as status_21_count
                            , sum( status_22_count ) as status_22_count
                            , sum( status_23_count ) as status_23_count
                            , sum( status_24_count ) as status_24_count
                            , sum( status_25_count ) as status_25_count
                            , sum( status_26_count ) as status_26_count

                            , sum( status_gensen_1_count ) as status_gensen_1_count
                            , sum( status_gensen_2_count ) as status_gensen_2_count
                            , sum( status_gensen_3_count ) as status_gensen_3_count
                            , sum( status_gensen_4_count ) as status_gensen_4_count
                            , sum( status_6_manki_count ) as status_6_manki_count

                            , sum( status_30mae_count ) as status_30mae_count
                            , sum( status_hss_count ) as status_hss_count
                            , sum( status_syaryo_count ) as status_syaryo_count
                            , sum( status_daisya_count ) as status_daisya_count

                            , sum( status_toss_count ) as status_toss_count
                            , sum( status_pair_fleet_count ) as status_pair_fleet_count
                        FROM
                            tb_status_total status

                        LEFT JOIN tb_insurance_staff_num staff_num on
                            staff_num.plan_base_code = status.base_code::text AND
                            staff_num.plan_year || staff_num.plan_month =  '{$search->inspection_ym_from}'

                        {$whereSql} ";

        }else{
            // 台数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            status.user_name
                            , status.base_code
                            , status.base_short_name
                            , 0 as plan_value

                            , COALESCE( target_count, 0 ) as target_count

                            , COALESCE( status_0_count, 0 ) as status_0_count
                            , COALESCE( status_1_count, 0 ) as status_1_count
                            , COALESCE( status_2_count, 0 ) as status_2_count
                            , COALESCE( status_3_count, 0 ) as status_3_count
                            , COALESCE( status_4_count, 0 ) as status_4_count
                            , COALESCE( status_5_count, 0 ) as status_5_count
                            , COALESCE( status_6_count, 0 ) as status_6_count
                            , COALESCE( status_99_count, 0 ) as status_99_count
                            
                            , COALESCE( status_21_count, 0 ) as status_21_count
                            , COALESCE( status_22_count, 0 ) as status_22_count
                            , COALESCE( status_23_count, 0 ) as status_23_count
                            , COALESCE( status_24_count, 0 ) as status_24_count
                            , COALESCE( status_25_count, 0 ) as status_25_count
                            , COALESCE( status_26_count, 0 ) as status_26_count
                            
                            , COALESCE( status_gensen_1_count, 0 ) as status_gensen_1_count
                            , COALESCE( status_gensen_2_count, 0 ) as status_gensen_2_count
                            , COALESCE( status_gensen_3_count, 0 ) as status_gensen_3_count
                            , COALESCE( status_gensen_4_count, 0 ) as status_gensen_4_count
                            , COALESCE( status_6_manki_count, 0 ) as status_6_manki_count

                            , COALESCE( status_30mae_count, 0 ) as status_30mae_count
                            , COALESCE( status_hss_count, 0 ) as status_hss_count
                            , COALESCE( status_syaryo_count, 0 ) as status_syaryo_count
                            , COALESCE( status_daisya_count, 0 ) as status_daisya_count

                            , COALESCE( status_toss_count, 0 ) as status_toss_count
                            , COALESCE( status_pair_fleet_count, 0 ) as status_pair_fleet_count
                        FROM
                            tb_status_total status
                            
                        {$whereSql}
                            
                        ORDER BY
                            status.base_code ASC,
                            status.user_name ASC ";
        }
        
        return DB::select( $sql );
    }

    #########################
    ## 各担当の集計データの取得
    #########################
    
    /**
     * 担当者単位で実施率を取得する
     * @param unknown $search
     */
    public static function summaryStaffPlan( $search, $outputFlg="" ) {
        // 拠点コードの有無で処理を変更
        if( isset( $search->base_code ) == True && !empty( $search->base_code ) == True ){
            $sqlBase = " staff_num.plan_base_code = '" . $search->base_code . "' AND ";

        }else{
            $sqlBase = "";

        }

        $sql = "    SELECT
                        SUM( staff_num.plan_value ) as plan_value
                    FROM
                        tb_insurance_staff_num staff_num
                    WHERE
                        {$sqlBase}
                        staff_num.plan_year || staff_num.plan_month =  '{$search->inspection_ym_from}' ";
        
        return DB::select( $sql );
    }
    
}
