<?php

namespace App\Models\Top;

use DB;

/**
 * Topのグラフに関するDB
 */
class TopGraphDB{
    
    #########################
    ## 検索条件をまとめる処理
    #########################
    
    /**
     * 検索条件を取得し、検索オブジェクトの値を加工する
     * 表示部分の条件式
     * @param  [type] $search [description]
     * @return [type]             [description]
     */
    public static function getWhereShowSql( $search ){
        // 検索条件の取得
        $whereSql = "";
        $whereList = array();

        // 対象拠点
        if( !empty( $search->base_code ) == True ){
            $whereList[] = " base_code = '{$search->base_code}'";
        }
        
        if( !empty( $whereList ) == True ){
            $whereSql = " WHERE " . implode( " AND ", $whereList );
        }
        
        return $whereSql;
    }

    /**
     * 検索条件を取得し、検索オブジェクトの値を加工する
     * 件数取得部分の条件式
     * @param  [type] $search [description]
     * @return [type]             [description]
     */
    public static function getWhereSql( $search ){
        $whereSql = "";
        $whereList = array();

        // 対象拠点
        if( !empty( $search->inspection_id ) == True ){
            $whereList[] = "tgc.tgc_inspection_id = {$search->inspection_id}";
        }

        // 対象月の指定
//        if( !empty( $search->inspection_ym_from ) == True && !empty( $search->inspection_ym_to ) == True ){
//            $whereList[] = " tgc.tgc_inspection_ym between '{$search->inspection_ym_from}' AND '{$search->inspection_ym_to}' ";
//
//        }else if( !empty( $search->inspection_ym_from ) == True ){
//            $whereList[] = " tgc.tgc_inspection_ym = '{$search->inspection_ym_from}' ";
//
//        }
        if( !empty( $search->inspection_ym_from ) == True ){
            $whereList[] = " to_char(tgc.tgc_syaken_next_date, 'YYYYMM') = '{$search->inspection_ym_from}' ";
        }

        // 6ヶ月ロック
        $whereList[] = " tgc_lock_flg6 = 0 ";

        //攻略対象車のフラグが立っているものは対象台数から除外
        // $whereList[] = " ( tgc.tgc_customer_kouryaku_flg <> '1' OR tgc.tgc_customer_kouryaku_flg IS NULL ) ";
        
        // 車両無を除外
        // $whereList[] = " umu.umu_csv_flg = 1 ";
        
        //【最終実績】の意向が空白のセル、または自社車検のもののみカウント  @201908 車検実績のグラフが無くなったので廃止
//        $whereList[] = " ( info.mi_ik_final_achievement IS NULL OR info.mi_ik_final_achievement = '自社車検' ) ";
        
        // 検索の値があるときに動作
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
     * @param  [type] $search         [description]
     * @param  [type] $whereSql       [description]
     * @return [type]                 [description]
     */
    public static function getBaseSql( $search, $whereSql ){
        //
        $sql = "    
                WITH tmp_tb_user_account AS (
                    SELECT 
                        account.id, account.user_code, account.user_name, 
                        account.base_id, base.base_code, base.base_name, base.base_short_name,
                        account.deleted_at
                    FROM 
                        tb_user_account account
                    LEFT JOIN tb_base base ON
                        base.id = account.base_id AND
                        base.deleted_at IS NULL  
                    --WHERE
                    --    account.deleted_at IS NULL --担当者削除済みも含める
                ), 
                tb_top_graph AS(
                    SELECT
                        tgc.tgc_inspection_ym,
                        tgc.tgc_inspection_id,

                        CASE
                            WHEN
                                account.base_code IS NULL
                                THEN '99'
                            ELSE
                                account.base_code
                        END base_code,

                        CASE
                            WHEN
                                account.base_short_name IS NULL
                                THEN 'その他'
                            ELSE
                                account.base_short_name
                        END base_short_name,

                        account.user_code,
                        account.user_name,
                        account.deleted_at,

                        COUNT( tgc.id ) AS target,

                        -- 車両無
                        SUM(
                            CASE
                                WHEN
                                    umu.umu_csv_flg is null THEN 1
                                ELSE 0
                            END
                        ) AS target_none,

                        -- 自社車検
                        SUM(
                            CASE
                                WHEN
                                    tgc.tgc_status IN ('11') THEN 1
                                ELSE 0
                            END
                        ) AS jisya,

                        -- 他社車検
                        SUM(
                            CASE
                                WHEN
                                    tgc.tgc_status IN ('12') THEN 1
                                ELSE 0
                            END
                        ) AS tasya,

                        --その他
                        SUM(
                            CASE
                                WHEN
                                    tgc.tgc_status NOT IN ('11', '12', '20','0')
                                    AND tgc.tgc_status IS NOT NULL THEN 1
                                ELSE 0
                            END
                        ) AS other,
                        
                        --未確認
                        SUM(
                            CASE
                                WHEN
                                    tgc.tgc_status = '20'
                                    OR tgc.tgc_status IS NULL THEN 1
                                ELSE 0
                            END
                        ) AS unconfirmed,
                        
                        -- 出荷
                        SUM(
                            CASE
                                WHEN
                                    info.mi_dsya_syaken_jisshi_date IS NOT NULL
                                    THEN 1
                                ELSE 0
                            END
                        ) AS syukka,
                        
                        -- 予約
                        SUM(
                            CASE
                                WHEN
                                    info.mi_dsya_syaken_reserve_date IS NOT NULL
                                    -- 2022/03/22 Add
                                    AND to_char(tgc_syaken_next_date + '-12 month'::interval, 'YYYYMM'::text) < to_char(info.mi_dsya_syaken_reserve_date, 'YYYYMM')
                                    THEN 1
                                ELSE 0
                            END
                        ) AS reserve,

                        SUM(
                                CASE
                                        WHEN
                                                (info.mi_ctcview_date_tel is not null OR
                                                info.mi_ctcview_date_home is not null OR
                                                info.mi_ctcview_date_shop is not null)
                                                -- Update 2022/03/28 
                                                AND 
                                                ( to_char(TO_DATE(tgc_inspection_ym , 'YYYYMM') + INTERVAL '-2 month','YYYYMM') <= to_char(mi_ctcview_date_tel , 'YYYYMM')
                                                 OR to_char(TO_DATE(tgc_inspection_ym , 'YYYYMM') + INTERVAL '-2 month','YYYYMM') <= to_char(mi_ctcview_date_home , 'YYYYMM')
                                                 OR to_char(TO_DATE(tgc_inspection_ym , 'YYYYMM') + INTERVAL '-2 month','YYYYMM') <= to_char(mi_ctcview_date_shop , 'YYYYMM')
                                                )
                                                THEN 1
                                        ELSE 0
                                END
                        ) AS jissi_sessyoku,

                        SUM(
                                CASE
                                        WHEN
                                                (info.mi_ctcview_date_home is not null OR
                                                info.mi_ctcview_date_shop is not null)
                                                -- Update 2022/03/28
                                                AND 
                                                ( to_char(TO_DATE(tgc_inspection_ym , 'YYYYMM') + INTERVAL '-2 month','YYYYMM') <= to_char(mi_ctcview_date_tel , 'YYYYMM')
                                                 OR to_char(TO_DATE(tgc_inspection_ym , 'YYYYMM') + INTERVAL '-2 month','YYYYMM') <= to_char(mi_ctcview_date_home , 'YYYYMM')
                                                 OR to_char(TO_DATE(tgc_inspection_ym , 'YYYYMM') + INTERVAL '-2 month','YYYYMM') <= to_char(mi_ctcview_date_shop , 'YYYYMM')
                                                )
                                                THEN 1
                                        ELSE 0
                                END
                        ) AS jissi_sessyoku_homon_raiten,

                        -- 査定
                        SUM(
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
                                END
                        ) AS jissi_satei,

                        -- 試乗
                        SUM(
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
                                END
                        ) AS jissi_shijo,

                        -- 商談
                        SUM(
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
                                END
                        ) AS jissi_shodan,

                        SUM(
                                CASE
                                        WHEN
                                                info.mi_dsya_keiyaku_car is not null
                                                THEN 1
                                        ELSE 0
                                END
                        ) AS seiyaku

                    FROM
                        tb_target_cars tgc

                    LEFT JOIN tb_manage_info info ON
                        tgc.tgc_car_manage_number = info.mi_car_manage_number AND
                        tgc.tgc_inspection_ym = info.mi_inspection_ym AND
                        tgc.tgc_inspection_id = info.mi_inspection_id

                    LEFT JOIN tmp_tb_user_account account ON
                        account.id = tgc.tgc_user_id 

                    -- LEFT JOIN tb_base base ON
                    --    base.id = tgc.tgc_base_id AND
                    --    base.deleted_at IS NULL

                    --LEFT JOIN tb_user_account account ON
                    --    account.id = tgc.tgc_user_id AND
                    --    account.deleted_at IS NULL
                        
                    LEFT JOIN tb_customer_umu umu ON
                        tgc.tgc_car_manage_number = umu.umu_car_manage_number

                    {$whereSql}

                    GROUP BY
                        tgc.tgc_inspection_ym,
                        tgc.tgc_inspection_id,
                        
                        account.base_code,
                        account.base_short_name,

                        account.user_code,
                        account.user_name,
                        account.deleted_at

                )";
        
        return $sql;
    }

    #######################
    ## 総数取得
    #######################
    
    /**
     * 点検関連の計画＆実績集計を取得する
     * @param  [type]  $search [description]
     * @return [type]              [description]
     */
    public static function totalInspection( $search ) {
        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereShowSql = TopGraphDB::getWhereShowSql( $search );

        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereSql = TopGraphDB::getWhereSql( $search );

        // 値を取得する基本のSQLを取得
        $sql = TopGraphDB::getBaseSql( $search, $whereSql );

        $sql .= ",
                    tb_top_graph_sum AS(
                        SELECT
                            base_code,
                            base_short_name,

                            sum( target ) as target_count,
                            sum( target_none ) as target_none_count,
                            sum( jisya ) as jisya_count,
                            sum( tasya ) as tasya_count,
                            sum( other ) as other_count,
                            sum( unconfirmed ) as unconfirmed_count,
                            sum( syukka ) as syukka_count,
                            sum( reserve ) as reserve_count,
                            sum( jissi_sessyoku ) as jissi_sessyoku_count,
                            sum( jissi_sessyoku_homon_raiten ) as jissi_sessyoku_homon_raiten_count,
                            sum( jissi_satei ) as jissi_satei_count,
                            sum( jissi_shijo ) as jissi_shijo_count,
                            sum( jissi_shodan ) as jissi_shodan_count,
                            sum( seiyaku ) as seiyaku_count

                        FROM
                            tb_top_graph
                            
                        {$whereShowSql}

                        GROUP BY
                            base_code,
                            base_short_name

                    )
                    SELECT
                        sum( tb_top_graph_sum.target_count ) as target_count,
                        sum( tb_top_graph_sum.target_none_count ) as target_none_count,
                        sum( tb_top_graph_sum.jisya_count ) as jisya_count,
                        sum( tb_top_graph_sum.tasya_count ) as tasya_count,
                        sum( tb_top_graph_sum.other_count ) as other_count,
                        sum( tb_top_graph_sum.unconfirmed_count ) as unconfirmed_count,
                        sum( tb_top_graph_sum.syukka_count ) as syukka_count,
                        sum( tb_top_graph_sum.reserve_count ) as reserve_count,
                        sum( tb_top_graph_sum.jissi_sessyoku_count ) as jissi_sessyoku_count,
                        sum( tb_top_graph_sum.jissi_sessyoku_homon_raiten_count ) as jissi_sessyoku_homon_raiten_count,
                        sum( tb_top_graph_sum.jissi_satei_count ) as jissi_satei_count,
                        sum( tb_top_graph_sum.jissi_shijo_count ) as jissi_shijo_count,
                        sum( tb_top_graph_sum.jissi_shodan_count ) as jissi_shodan_count,
                        sum( tb_top_graph_sum.seiyaku_count ) as seiyaku_count

                    FROM
                        tb_top_graph_sum ";

        return DB::select( $sql );
    }

    #########################
    ## 各拠点の集計データの取得
    #########################

    /**
     * 拠点単位でのTOP集計データを取得する
     */
    public static function summaryBase( $search ) {
        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereShowSql = TopGraphDB::getWhereShowSql( $search );

        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereSql = TopGraphDB::getWhereSql( $search );

        // 値を取得する基本のSQLを取得
        $sql = TopGraphDB::getBaseSql( $search, $whereSql );
        
        $sql .= "   ,
                    tb_top_graph_sum AS(
                        SELECT
                            base_code,
                            base_short_name,

                            sum( target ) as target_count,
                            sum( target_none ) as target_none_count,
                            sum( jisya ) as jisya_count,
                            sum( tasya ) as tasya_count,
                            sum( other ) as other_count,
                            sum( unconfirmed ) as unconfirmed_count,
                            sum( syukka ) as syukka_count,
                            sum( reserve ) as reserve_count,
                            sum( jissi_sessyoku ) as jissi_sessyoku_count,
                            sum( jissi_sessyoku_homon_raiten ) as jissi_sessyoku_homon_raiten_count,
                            sum( jissi_satei ) as jissi_satei_count,
                            sum( jissi_shijo ) as jissi_shijo_count,
                            sum( jissi_shodan ) as jissi_shodan_count,
                            sum( seiyaku ) as seiyaku_count

                        FROM
                            tb_top_graph

                        {$whereShowSql}

                        GROUP BY
                            base_code,
                            base_short_name

                    )
                    SELECT
                        tb_top_graph_sum.base_code,
                        tb_top_graph_sum.base_short_name,
                        
                        tb_top_graph_sum.target_count,
                        tb_top_graph_sum.target_none_count,
                        tb_top_graph_sum.jisya_count,
                        tb_top_graph_sum.tasya_count,
                        tb_top_graph_sum.other_count,
                        tb_top_graph_sum.unconfirmed_count,
                        tb_top_graph_sum.syukka_count,
                        tb_top_graph_sum.reserve_count,
                        tb_top_graph_sum.jissi_sessyoku_count,
                        tb_top_graph_sum.jissi_sessyoku_homon_raiten_count,
                        tb_top_graph_sum.jissi_satei_count,
                        tb_top_graph_sum.jissi_shijo_count,
                        tb_top_graph_sum.jissi_shodan_count,
                        tb_top_graph_sum.seiyaku_count
                        
                    FROM
                        tb_top_graph_sum

                    ORDER BY
                        --tb_top_graph_sum.reserve_per desc,
                        tb_top_graph_sum.base_code ";
                        
        return DB::select( $sql );
    }

    #########################
    ## 各担当の集計データの取得
    #########################

    /**
     * 担当者単位の点検の予定、計画、実績を集計する
     */
    public static function summaryUser( $search ) {
        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereShowSql = TopGraphDB::getWhereShowSql( $search );

        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereSql = TopGraphDB::getWhereSql( $search );

        // 値を取得する基本のSQLを取得
        $sql = TopGraphDB::getBaseSql( $search, $whereSql );

        $sql .= "   ,
                    tb_top_graph_sum AS(
                        SELECT
                            base_code,
                            base_short_name,
                            user_code,
                            user_name,
                            deleted_at,

                            sum( target ) as target_count,
                            sum( target_none ) as target_none_count,
                            sum( jisya ) as jisya_count,
                            sum( tasya ) as tasya_count,
                            sum( other ) as other_count,
                            sum( unconfirmed ) as unconfirmed_count,
                            sum( syukka ) as syukka_count,
                            sum( reserve ) as reserve_count,
                            sum( jissi_sessyoku ) as jissi_sessyoku_count,
                            sum( jissi_sessyoku_homon_raiten ) as jissi_sessyoku_homon_raiten_count,
                            sum( jissi_satei ) as jissi_satei_count,
                            sum( jissi_shijo ) as jissi_shijo_count,
                            sum( jissi_shodan ) as jissi_shodan_count,
                            sum( seiyaku ) as seiyaku_count

                        FROM
                            tb_top_graph
                             
                        {$whereShowSql}

                        GROUP BY
                            base_code,
                            base_short_name,
                            user_code,
                            user_name,
                            deleted_at
                    )
                    SELECT
                        tb_top_graph_sum.base_code,
                        tb_top_graph_sum.base_short_name,
                        tb_top_graph_sum.user_code,
                        tb_top_graph_sum.user_name,
                        tb_top_graph_sum.deleted_at,

                        tb_top_graph_sum.target_count,
                        tb_top_graph_sum.target_none_count,
                        tb_top_graph_sum.jisya_count,
                        tb_top_graph_sum.tasya_count,
                        tb_top_graph_sum.other_count,
                        tb_top_graph_sum.unconfirmed_count,
                        tb_top_graph_sum.syukka_count,
                        tb_top_graph_sum.reserve_count,
                        tb_top_graph_sum.jissi_sessyoku_count,
                        tb_top_graph_sum.jissi_sessyoku_homon_raiten_count,
                        tb_top_graph_sum.jissi_satei_count,
                        tb_top_graph_sum.jissi_shijo_count,
                        tb_top_graph_sum.jissi_shodan_count,
                        tb_top_graph_sum.seiyaku_count
                        
                    FROM
                        tb_top_graph_sum

                    ORDER BY
                        --tb_top_graph_sum.reserve_per desc,
                        tb_top_graph_sum.base_code,
                        tb_top_graph_sum.user_code ";

        return DB::select( $sql );
    }

}
