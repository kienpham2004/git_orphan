<?php

namespace App\Models\Result;

use DB;

/**
 * 実績集計に関するDB
 */
class StatusDB{
    
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
        if( !empty( $search->base_id ) == True ){
            $whereList[] = "base_id = '{$search->base_id}'";
        }
        
        if( !empty( $whereList ) == True ){
            $whereSql = " WHERE " . implode( " AND ", $whereList );
        }

        return $whereSql;
    }

    #########################
    ## ベースとなるSQL
    #########################
    
    /**
     * 車点検の値を取得する基本のSQLを取得
     * @param  [type] $search [description]
     * @return [type]               [description]
     */
    public static function getBaseSql( $search ){
        //
        $sql = "    
                    WITH tmp_tb_user_account AS (
                        SELECT 
                            account.id as user_id, account.user_code, account.user_name, 
                            -- account.base_id, base.base_code, base.base_short_name,
                            CASE 
                                WHEN base.deleted_at IS NOT NULL 
                                THEN NULL 
                            ELSE base.id 
                            END base_id,
                            CASE 
                                WHEN base.deleted_at IS NOT NULL 
                                THEN NULL 
                            ELSE base.base_code 
                            END base_code,
                            CASE 
                                WHEN base.deleted_at IS NOT NULL 
                                THEN NULL 
                            ELSE base.base_short_name
                            END base_short_name,
                            base.base_name,
                            base.block_base_code, account.deleted_at, base.deleted_at as base_deleted_at
                        FROM 
                            tb_user_account account
                        LEFT JOIN tb_base base ON
                            base.id = account.base_id AND
                            base.deleted_at IS NULL  
                        --WHERE
                        --    account.deleted_at IS NULL -- 退職者も含める
                    ),           
                    -- 先行実績の台数を取得
                    tb_target AS (
                        SELECT
                            tgc.tgc_car_name,
                            
                            account.base_id,
                            account.base_code,
                            --account.base_short_name, 
                            CASE
                                WHEN
                                    account.base_short_name IS NULL
                                    THEN 'その他'
                                ELSE
                                    account.base_short_name
                            END base_short_name,                          
                            account.block_base_code,

                            account.user_id,
                            account.user_code,
                            CASE
                                WHEN account.deleted_at IS NOT NULL 
                                THEN account.user_name || ' (退職者)'
                                ELSE account.user_name
                            END AS user_name,
                            
                            1 as target,

                            CASE
                                WHEN (tgc.tgc_status = '20' OR tgc.tgc_status IS NULL) THEN 1
                                ELSE 0
                            END AS mikakutei,
                            --自社車検
                            CASE
                                WHEN tgc.tgc_status = '11' THEN 1
                                ELSE 0
                            END AS intention1,

                            --他社車検
                            CASE
                                WHEN tgc.tgc_status = '12' THEN 1
                                ELSE 0
                            END AS intention2,

                            --自社代替
                            CASE
                                WHEN tgc.tgc_status = '13' THEN 1
                                ELSE 0
                            END AS intention3,

                            --他社代替
                            CASE
                                WHEN tgc.tgc_status = '14' THEN 1
                                ELSE 0
                            END AS intention4,

                            --廃車・転売
                            CASE
                                WHEN tgc.tgc_status = '15' THEN 1
                                ELSE 0
                            END AS intention5,

                            -- 2022/04/05 update
                            --転居予定
                            CASE
                                WHEN tgc.tgc_status = '16' THEN 1
                                ELSE 0
                            END AS intention6,
                            
                            --拠点移管
                            CASE
                                WHEN tgc.tgc_status = '17' THEN 1
                                ELSE 0
                            END AS intention7,
                            
                            CASE
                                WHEN tgc.tgc_status = '18' THEN 1
                                ELSE 0
                            END AS intention8,
                            
                            CASE
                                WHEN tgc.tgc_status = '9' THEN 1
                                ELSE 0
                            END AS intention9,
                            
                            CASE
                                WHEN tgc.tgc_status = '10' OR tgc.tgc_status = '7' THEN 1
                                ELSE 0
                            END AS intention10

                        --FROM

                        --    tmp_tb_user_account account

                        --LEFT JOIN tb_target_cars tgc  ON
                        --    account.user_id = tgc.tgc_user_id

                        FROM

                            tb_target_cars tgc

                        LEFT JOIN tmp_tb_user_account account  ON
                            account.user_id = tgc.tgc_user_id

                        LEFT JOIN tb_manage_info info ON
                            tgc.tgc_car_manage_number = info.mi_car_manage_number AND
                            tgc.tgc_inspection_ym = info.mi_inspection_ym AND
                            tgc.tgc_inspection_id = info.mi_inspection_id
                        
                        --LEFT JOIN tb_base base ON
                        --    base.base_code = tgc.tgc_base_code AND
                        --    base.deleted_at IS NULL

                        --LEFT JOIN tb_user_account account ON
                        --    account.user_id = tgc.tgc_user_id AND
                        --    account.deleted_at IS NULL

                        WHERE
                            --{$search->inspection_div}
                            tgc.tgc_inspection_id = 4 AND
                            tgc.tgc_inspection_ym = '{$search->inspection_ym_from}' AND
                            --( --date_trunc('day', tgc.tgc_syaken_next_date - interval '6 months') >= tgc.created_at
                            --  to_char((tgc_syaken_next_date - interval '6 months'), 'yyyymm') >=  to_char(tgc.created_at, 'yyyymm') 
                            --    OR
                            --    tgc.created_at < '".config('original.six_lock_exclusion')."' 
                            --) AND
                            tgc_lock_flg6 = 0 AND  
                            --( tgc.tgc_customer_kouryaku_flg <> '1' OR tgc.tgc_customer_kouryaku_flg IS NULL ) AND
                            tgc.deleted_at IS NULL
                    )";
        
        return $sql;
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
        $whereSql = StatusDB::getWhereSql( $search );
        // $check = "((strpos(base_short_name,'(削除済)') > 0 AND target_count > 0 ) 
        //           OR strpos(base_short_name,'(削除済)') = 0 )";
        // if (empty($whereSql)){
        //     $whereSql = " WHERE $check ";
        // }
        // else {
        //     $whereSql = $whereSql." AND $check ";
        // }

        // 車点検の値を取得する基本のSQLを取得
        $sql = StatusDB::getBaseSql( $search );

        $sql .= "    ,
                    tb_target_total AS (
                        SELECT
                            base_id,
                            base_code,
                            base_short_name, 

                            COALESCE( sum( target ), 0 ) as target_count,

                            COALESCE( sum( mikakutei ), 0 ) as mikakutei_count,
                            COALESCE( sum( intention1 ), 0 ) as intention1_count,
                            COALESCE( sum( intention2 ), 0 ) as intention2_count,
                            COALESCE( sum( intention3 ), 0 ) as intention3_count,
                            COALESCE( sum( intention4 ), 0 ) as intention4_count,
                            COALESCE( sum( intention5 ), 0 ) as intention5_count,
                            COALESCE( sum( intention6 ), 0 ) as intention6_count,
                            COALESCE( sum( intention7 ), 0 ) as intention7_count,
                            COALESCE( sum( intention8 ), 0 ) as intention8_count,
                            COALESCE( sum( intention9 ), 0 ) as intention9_count,
                            COALESCE( sum( intention10 ), 0 ) as intention10_count

                        FROM
                            tb_target

                        GROUP BY
                            base_id,
                            base_code,
                            base_short_name
                    )
                ";

//        // 総数の時の処理
//        if( $outputFlg == "total" ){
//            // 総数を取得するSQL
//            $sql .= "   -- 先行と当月実施の台数をまとめて取得
//                        SELECT
//                            '合計' as user_name
//                            , '' as user_id
//                            , '' as base_code
//                            , '' as base_short_name
//
//                            , sum( target_count ) as target_count
//
//                            , sum( mikakutei_count ) as mikakutei_count
//                            , sum( intention1_count ) as intention1_count
//                            , sum( intention2_count ) as intention2_count
//                            , sum( intention3_count ) as intention3_count
//                            , sum( intention4_count ) as intention4_count
//                            , sum( intention5_count ) as intention5_count
//                            , sum( intention6_count ) as intention6_count
//                            , sum( intention7_count ) as intention7_count
//
//                            , sum( intention8_count ) as intention8_count
//                            , sum( intention9_count ) as intention9_count
//                            , sum( intention10_count ) as intention10_count
//
//                        FROM
//                            tb_target_total
//
//                        {$whereSql} ";
//        }else{
            // 台数を取得するSQL
            $sql .= "  -- 先行と当月実施の台数をまとめて取得
                        SELECT                            
                             '' as user_name
                            , base_id
                            , base_code
                            , base_short_name
                            
                            , COALESCE( target_count, 0 ) as target_count

                            , COALESCE( mikakutei_count, 0 ) as mikakutei_count
                            , COALESCE( intention1_count, 0 ) as intention1_count
                            , COALESCE( intention2_count, 0 ) as intention2_count
                            , COALESCE( intention3_count, 0 ) as intention3_count
                            , COALESCE( intention4_count, 0 ) as intention4_count
                            , COALESCE( intention5_count, 0 ) as intention5_count
                            , COALESCE( intention6_count, 0 ) as intention6_count
                            , COALESCE( intention7_count, 0 ) as intention7_count
                            , COALESCE( intention8_count, 0 ) as intention8_count
                            , COALESCE( intention9_count, 0 ) as intention9_count
                            , COALESCE( intention10_count, 0 ) as intention10_count

                        FROM
                            tb_target_total

                        {$whereSql}

                        ORDER BY
                            base_code ASC ";
//        }
        
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
        $whereSql = StatusDB::getWhereSql( $search );
        $check = "AND (( strpos(user_name,'(退職者)') > 0 AND target_count > 0 ) 
                      OR strpos(user_name,'(退職者)') = 0 )";
        if( !empty( $whereSql ) ){
            $whereSql .= " AND user_id IS NOT NULL $check ";
        }else{
            $whereSql = " WHERE user_id IS NOT NULL $check";
        }

        // 車点検の値を取得する基本のSQLを取得
        $sql = StatusDB::getBaseSql( $search );

        $sql .= "    ,
                    tb_target_total AS (
                        SELECT
                            base_id,
                            base_code,
                            base_short_name, 
                            user_id,
                            user_code,
                            user_name,

                            COALESCE( sum( target ), 0 ) as target_count,

                            COALESCE( sum( mikakutei ), 0 ) as mikakutei_count,
                            COALESCE( sum( intention1 ), 0 ) as intention1_count,
                            COALESCE( sum( intention2 ), 0 ) as intention2_count,
                            COALESCE( sum( intention3 ), 0 ) as intention3_count,
                            COALESCE( sum( intention4 ), 0 ) as intention4_count,
                            COALESCE( sum( intention5 ), 0 ) as intention5_count,
                            COALESCE( sum( intention6 ), 0 ) as intention6_count,
                            COALESCE( sum( intention7 ), 0 ) as intention7_count,
                            COALESCE( sum( intention8 ), 0 ) as intention8_count,
                            COALESCE( sum( intention9 ), 0 ) as intention9_count,
                            COALESCE( sum( intention10 ), 0 ) as intention10_count

                        FROM
                            tb_target

                        GROUP BY
                            user_id,
                            user_code,
                            user_name,
                            base_code,
                            base_id,
                            base_short_name 
                    )
                ";

        // 総数の時の処理
//        if( $outputFlg == "total" ){
//            // 総数を取得するSQL
//            $sql .= "   -- 先行と当月実施の台数をまとめて取得
//                        SELECT
//                            '合計' as user_name
//                            , '' as user_id
//                            , '' as base_code
//                            , '' as base_short_name
//
//                            , sum( target_count ) as target_count
//
//                            , sum( mikakutei_count ) as mikakutei_count
//                            , sum( intention1_count ) as intention1_count
//                            , sum( intention2_count ) as intention2_count
//                            , sum( intention3_count ) as intention3_count
//                            , sum( intention4_count ) as intention4_count
//                            , sum( intention5_count ) as intention5_count
//                            , sum( intention6_count ) as intention6_count
//                            , sum( intention7_count ) as intention7_count
//                            , sum( intention8_count ) as intention8_count
//                            , sum( intention9_count ) as intention9_count
//                            , sum( intention10_count ) as intention10_count
//
//                        FROM
//                            tb_target_total
//
//                        {$whereSql} ";
//        }else{
            // 台数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            user_id
                            , user_code
                            , user_name
                            , base_id
                            , base_code
                            , base_short_name
                            
                            , COALESCE( target_count, 0 ) as target_count

                            , COALESCE( mikakutei_count, 0 ) as mikakutei_count
                            , COALESCE( intention1_count, 0 ) as intention1_count
                            , COALESCE( intention2_count, 0 ) as intention2_count
                            , COALESCE( intention3_count, 0 ) as intention3_count
                            , COALESCE( intention4_count, 0 ) as intention4_count
                            , COALESCE( intention5_count, 0 ) as intention5_count
                            , COALESCE( intention6_count, 0 ) as intention6_count
                            , COALESCE( intention7_count, 0 ) as intention7_count
                            , COALESCE( intention8_count, 0 ) as intention8_count
                            , COALESCE( intention9_count, 0 ) as intention9_count
                            , COALESCE( intention10_count, 0 ) as intention10_count

                        FROM
                            tb_target_total
                            
                        {$whereSql}
                            
                        ORDER BY
                            user_id ASC ";
//        }
        
        return DB::select( $sql );
    }

}

