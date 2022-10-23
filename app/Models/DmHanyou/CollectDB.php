<?php

namespace App\Models\DmHanyou;

use DB;

/**
 * 拠点明細に関するDB
 */
class CollectDB{
    
    #########################
    ## ベースとなるSQL
    #########################
    
    /**
     * DM総数を取得するSQL
     * @return [type] [description]
     */
    public static function getCollectSql( $search ){
        //
        $sql = "    -- DMの総数を取得
                    WITH tb_collect AS (
                        SELECT
                            base.base_code,
                            base.base_short_name, 
                            account.user_id,
                            account.user_name,
                                       
                            -- 総数
                            COALESCE(
                                sum(
                                    CASE
                                        WHEN
                                            original_dm_flg IS NULL
                                            THEN 1
                                        ELSE
                                            0
                                    END
                                ), 0
                            ) as total,
                            
                            -- 不要総数
                            COALESCE(
                                sum(
                                    CASE
                                        WHEN
                                            original_dm_flg = '1'
                                            THEN 1
                                        ELSE
                                            0
                                    END
                                ), 0
                            ) as total_not
                            
                        FROM
                            tb_customer_dm_1 cust
                            
                        LEFT JOIN tb_base base ON
                            base.base_code = cust.base_code AND
                            base.deleted_at IS NULL

                        LEFT JOIN tb_user_account account ON
                            account.user_id = cust.user_id AND
                            account.deleted_at IS NULL
                            
                        WHERE
                            cust.deleted_at IS NULL AND
                            base.base_code IS NOT NULL

                        GROUP BY
                            base.base_code,
                            base.base_short_name, 
                            account.user_id,
                            account.user_name
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
    public static function getWhereSql( $search ){
        // 検索条件の取得
        $whereSql = "";
        $whereList = array();

        // 対象拠点
        if( !empty( $search->base_code ) == True ){
            $whereList[] = "collect.base_code = '{$search->base_code}'";
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
        $whereSql = CollectDB::getWhereSql( $search );

        // DM総数を取得するSQL
        $collectSql = CollectDB::getCollectSql( $search );

        $sql = "    {$collectSql},

                    -- DMの総数を取得
                    tb_collect_total AS (
                        SELECT
                            base_code,
                            base_short_name,

                            COALESCE( sum( total ), 0 ) as total_count,
                            COALESCE( sum( total_not ), 0 ) as total_not_count

                        FROM
                            tb_collect

                        GROUP BY
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

                            , sum( total_count ) as total_count
                            , sum( total_not_count ) as total_not_count
                            
                        FROM
                            tb_collect_total collect

                        {$whereSql} ";

        }else{
            // 台数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            '' as user_name
                            , collect.base_code
                            , collect.base_short_name

                            , COALESCE( total_count, 0 ) as total_count
                            , COALESCE( total_not_count, 0 ) as total_not_count

                        FROM
                            tb_collect_total collect

                        {$whereSql}

                        ORDER BY
                            collect.base_code ASC ";
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
        $whereSql = CollectDB::getWhereSql( $search );

        if( !empty( $whereSql ) ){
            $whereSql .= " AND collect.user_id IS NOT NULL ";
        }else{
            $whereSql = " WHERE collect.user_id IS NOT NULL ";
        }

        // DM総数を取得するSQL
        $collectSql = CollectDB::getCollectSql( $search );
        
        $sql = "    {$collectSql},

                    -- DMの総数を取得
                    tb_collect_total AS (
                        SELECT
                            user_id,
                            user_name,
                            base_code,
                            base_short_name,

                            COALESCE( sum( total ), 0 ) as total_count,
                            COALESCE( sum( total_not ), 0 ) as total_not_count

                        FROM
                            tb_collect
                            
                        GROUP BY
                            user_id,
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

                            , sum( total_count ) as total_count
                            , sum( total_not_count ) as total_not_count

                        FROM
                            tb_collect_total collect

                        {$whereSql}";

        }else{
            // 台数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            collect.user_id
                            , collect.user_name
                            , collect.base_code
                            , collect.base_short_name
                            
                            , COALESCE( total_count, 0 ) as total_count
                            , COALESCE( total_not_count, 0 ) as total_not_count
                            
                        FROM
                            tb_collect_total collect
                            
                        {$whereSql}
                        
                        ORDER BY
                            collect.user_id ASC ";
        }
        
        return DB::select( $sql );
    }

}
