<?php

namespace App\Models\Result\Bouei;

use DB;

/**
 * 実績集計に関するDB
 */
class ResultDB{
    
    #########################
    ## ベースとなるSQL
    #########################
    
    /**
     * 先行実施を取得するSQL
     * @param  [type] $search [description]
     * @return [type]               [description]
     */
    public static function getSenkouSql( $search ){
        //
        $sql = "    -- 先行実績の台数を取得
                    WITH tb_senkou AS (
                        SELECT
                            tgc.tgc_car_name,
                            base.base_code,
                            base.base_short_name, 
                            account.user_id,
                            account.user_name,

                            -- 期日先行予約
                            --CASE
                            --    WHEN
                            --        info.rstc_delivered_date is not null AND
                            --        info.mi_rstc_delivered_date <> '1970-01-01 09:00:00' AND
                            --        to_char(info.mi_rstc_delivered_date, 'yyyymm') = '{$search->inspection_ym_from}'
                            --        THEN 1
                            --    ELSE 0
                            --END senkou_yoyaku,

                            -- 期日先行実施
                            CASE
                                WHEN
                                    --info.mi_rstc_delivered_date is not null AND
                                    --info.mi_rstc_delivered_date <> '1970-01-01 09:00:00' AND
                                    to_char(info.mi_rstc_delivered_date, 'yyyymm') = '{$search->now_syaken_ym}' AND
                                    info.mi_rstc_reserve_status = '出荷済'
                                    THEN 1
                                ELSE 0
                            END senkou_jissi

                        FROM
                            tb_target_cars tgc

                        LEFT JOIN tb_manage_info info ON
                            tgc.tgc_car_manage_number = info.mi_car_manage_number AND
                            tgc.tgc_inspection_ym = info.mi_inspection_ym AND
                            tgc.tgc_inspection_id = info.mi_inspection_id

                        LEFT JOIN tb_base base ON
                            base.base_code = tgc.tgc_base_code AND
                            base.deleted_at IS NULL

                        LEFT JOIN tb_user_account account ON
                            account.user_id = tgc.tgc_user_id AND
                            account.deleted_at IS NULL

                        WHERE
                            {$search->inspection_div}
                            to_char(tgc.tgc_syaken_next_date, 'YYYYMM') = '{$search->next_syaken_ym}' AND
                            tgc.deleted_at IS NULL AND
                            base.base_code IS NOT NULL
                    ) ";
        
        return $sql;
    }
    
    /**
     * 当月実施を取得するSQL
     * @return [type] [description]
     */
    public static function getTougetuSql( $search, $whereSql="" ){
        //
        $sql = "    -- 当月実績の台数を取得
                    tb_tougetu AS (
                        SELECT
                            tgc.tgc_car_name,
                            base.base_code,
                            base.base_short_name, 
                            account.user_id,
                            account.user_name,

                            1 as target,

                            -- 代替数
                            -- 予約か実績がある時はノーカウント
                            CASE
                                --WHEN
                                --    info.mi_rstc_delivered_date is not null AND
                                --    info.mi_rstc_delivered_date <> '1970-01-01 09:00:00'
                                --    THEN 0
                                --WHEN
                                --    info.mi_rstc_delivered_date is not null AND
                                --    info.mi_rstc_delivered_date <> '1970-01-01 09:00:00'
                                --    THEN 0
                                WHEN
                                    {$search->bouei_daigae}
                                    --tgc.tgc_status IN  ('13')
                                    info.mi_ik_final_achievement = '自社代替'
                                    THEN 1
                                ELSE 0
                            END daigae,

                            -- 先行予約
                            CASE
                                WHEN
                                    info.mi_rstc_delivered_date is not null AND
                                    info.mi_rstc_delivered_date <> '1970-01-01 09:00:00' AND
                                    to_char( date_trunc('month'::text, info.mi_rstc_delivered_date), 'yyyymm') < '{$search->now_syaken_ym}'
                                    THEN 1
                                ELSE 0
                            END tougetu_senkou_yoyaku,

                            -- 当月予約
                            CASE
                                WHEN
                                    info.mi_rstc_delivered_date is not null AND
                                    info.mi_rstc_delivered_date <> '1970-01-01 09:00:00' AND
                                    to_char(info.mi_rstc_delivered_date, 'yyyymm') = '{$search->now_syaken_ym}'
                                    THEN 1
                                ELSE 0
                            END tougetu_yoyaku,

                            -- 経過予約
                            CASE
                                WHEN
                                    info.mi_rstc_delivered_date is not null AND
                                    info.mi_rstc_delivered_date <> '1970-01-01 09:00:00' AND
                                    to_char( date_trunc('month'::text, info.mi_rstc_delivered_date), 'yyyymm') > '{$search->now_syaken_ym}'
                                    THEN 1
                                ELSE 0
                            END tougetu_keika_yoyaku,

                            -- 当月先行実施
                            CASE
                                WHEN
                                    --info.mi_rstc_delivered_date is not null AND
                                    --info.mi_rstc_delivered_date <> '1970-01-01 09:00:00' AND
                                    to_char( date_trunc('month'::text, info.mi_rstc_delivered_date), 'yyyymm') < '{$search->now_syaken_ym}' AND
                                    -- 20170706 現在日時との判定を追加
                                    info.mi_rstc_delivered_date <= current_date AND
                                    info.mi_rstc_reserve_status = '出荷済'
                                    THEN 1
                                ELSE 0
                            END tougetu_senkou_jissi,

                            -- 当月実施
                            CASE
                                WHEN
                                    --info.mi_rstc_delivered_date is not null AND
                                    --info.mi_rstc_delivered_date <> '1970-01-01 09:00:00' AND
                                    to_char(info.mi_rstc_delivered_date, 'yyyymm') = '{$search->now_syaken_ym}' AND
                                    info.mi_rstc_reserve_status = '出荷済'
                                    THEN 1
                                ELSE 0
                            END tougetu_jissi,

                            -- 実施待ち
                            CASE
                                WHEN
                                    info.mi_rstc_delivered_date is not null AND
                                    info.mi_rstc_delivered_date <> '1970-01-01 09:00:00' AND
                                    to_char(info.mi_rstc_delivered_date, 'yyyymm') = '{$search->now_syaken_ym}' AND
                                    -- 20170706 現在日時との判定を追加
                                    info.mi_rstc_delivered_date > current_date
                                    THEN 1
                                ELSE 0
                            END tougetu_jissi_machi,

                            -- 当月経過実施
                            CASE
                                WHEN
                                    info.mi_rstc_delivered_date is not null AND
                                    info.mi_rstc_delivered_date <> '1970-01-01 09:00:00' AND
                                    to_char( date_trunc('month'::text, info.mi_rstc_delivered_date), 'yyyymm') > '{$search->now_syaken_ym}' AND
                                    -- 20170706 現在日時との判定を追加
                                    info.mi_rstc_delivered_date <= current_date
                                    THEN 1
                                ELSE 0
                            END tougetu_keika_jissi

                        FROM
                            tb_target_cars tgc
                            
                        LEFT JOIN tb_base base ON
                            base.base_code = tgc.tgc_base_code AND
                            base.deleted_at IS NULL

                        LEFT JOIN tb_user_account account ON
                            account.user_id = tgc.tgc_user_id AND
                            account.deleted_at IS NULL
                            
                        LEFT JOIN tb_manage_info info ON
                            tgc.tgc_car_manage_number = info.mi_car_manage_number AND
                            tgc.tgc_inspection_ym = info.mi_inspection_ym AND
                            tgc.tgc_inspection_id = info.mi_inspection_id

                        --LEFT JOIN v_ciao ON
                        --    tgc.tgc_customer_code = v_ciao.ciao_customer_code AND
                        --    tgc.tgc_car_manage_number = v_ciao.ciao_car_manage_number AND
                        --    tgc.tgc_inspection_ym <= to_char( v_ciao.ciao_end_date, 'yyyymm' ) AND
                        --    v_ciao.deleted_at IS NULL

                        WHERE
                            {$search->inspection_div}
                            to_char(tgc.tgc_syaken_next_date, 'YYYYMM') = '{$search->now_syaken_ym}' AND
                            {$whereSql}
                            tgc.deleted_at IS NULL AND
                            base.base_code IS NOT NULL
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
                $whereList[] = "tougetu.base_code = '{$search->base_code}'";
            }
        }

        if( !empty( $whereList ) == True ){
            $whereSql = " WHERE " . implode( " AND ", $whereList );
        }

        return $whereSql;
    }

    /**
     * 検索条件を取得し、検索条件を作る
     * @param  [type] &$search [description]
     * @return [type]                [description]
     */
    public static function getWhereCoreSql( $search, $groupName="" ){
        // 検索条件の取得
        $whereSql = "";
        $whereList = array();

        // 対象車種
        if( !empty( $search->car_name ) == True ){
            //if( $groupName == 3 ){
                $whereList[] = "tgc.tgc_car_name LIKE '%{$search->car_name}%'";
            //}
        }

        // 車検回数
        if( !empty( $search->car_times_1 ) == True && !empty( $search->car_times_2 ) == True ){
            $whereList[] = "    (
                                    tgc.tgc_syaken_times >= '{$search->car_times_1}' AND
                                    tgc.tgc_syaken_times <= '{$search->car_times_2}'
                                ) ";

        }elseif( !empty( $search->car_times_1 ) == True && empty( $search->car_times_2 ) == True ){
            $whereList[] = " tgc.tgc_syaken_times >= '{$search->car_times_1}' ";

        }elseif( empty( $search->car_times_1 ) == True && !empty( $search->car_times_2 ) == True ){
            $whereList[] = " tgc.tgc_syaken_times >= '{$search->car_times_2}' ";

        }

        // まかせチャオ
        if( isset( $search->ciao ) == True && !empty( $search->ciao ) == True ){
            foreach( $search->ciao as $ciaoKey => $ciaoValue ){
                if( $ciaoValue == "0" ){
                    //$whereList[] = " v_ciao.ciao_course IS NULL ";
                    $whereList[] = " tb_target_cars.tgc_ciao_course IS NULL ";
                    
                }else if( $ciaoValue == "1" ){
                    //$whereList[] = " v_ciao.ciao_course IS NOT NULL ";
                    $whereList[] = " tb_target_cars.tgc_ciao_course IS NOT NULL ";

                }

            }
        }
        
        //管理外の突発車検を除く（6ヶ月ロック）
        if( $search->six_rock_flg == True){
//            $whereList[] = " (
//                              --date_trunc('day', tgc.tgc_syaken_next_date - interval '6 months') >= tgc.created_at
//                              to_char((tgc_syaken_next_date - interval '6 months'), 'yyyymm') >=  to_char(tgc.created_at, 'yyyymm')
//                              OR
//                              tgc.created_at < '".config('original.six_lock_exclusion')."'
//                           ) ";
            $whereList[] = " tgc_lock_flg6 = 0 ";
        }
        
        if( isset($search->kouryaku_flg) == True ){
            //攻略対象車のフラグが立っているものは対象台数から除外
            $whereList[] = " ( tgc.tgc_customer_kouryaku_flg <> '1' OR tgc.tgc_customer_kouryaku_flg IS NULL ) ";
        }
        
        if( !empty( $whereList ) == True ){
            $whereSql = implode( " AND ", $whereList ) . " AND " ;
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
        $whereSql = ResultDB::getWhereSql( $search );
        
        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereCoreSql = ResultDB::getWhereCoreSql( $search );

        // 先行実施を取得するSQL
        $senkouSql = ResultDB::getSenkouSql( $search );
        // 当月実施を取得するSQL
        $tougetsuSql = ResultDB::getTougetuSql( $search, $whereCoreSql );

        $sql = "    {$senkouSql},

                    -- 先行実績の総数を取得
                    tb_senkou_total AS (
                        SELECT
                            base_code,
                            base_short_name,

                            --COALESCE( sum( senkou_yoyaku ), 0 ) as senkou_yoyaku_count,
                            COALESCE( sum( senkou_jissi ), 0 ) as senkou_jissi_count
                        FROM
                            tb_senkou

                        GROUP BY
                            base_code,
                            base_short_name
                    ),
                    
                    {$tougetsuSql},

                    -- 当月実績の総数を取得
                    tb_tougetu_total AS (
                        SELECT
                            base_code,
                            base_short_name,

                            COALESCE( sum( target ), 0 ) as target_count,
                            COALESCE( sum( daigae ), 0 ) as daigae_count,
                            
                            COALESCE( sum( tougetu_senkou_yoyaku ), 0 ) as tougetu_senkou_yoyaku_count,
                            COALESCE( sum( tougetu_yoyaku ), 0 ) as tougetu_yoyaku_count,
                            COALESCE( sum( tougetu_keika_yoyaku ), 0 ) as tougetu_keika_yoyaku_count,

                            COALESCE( sum( tougetu_senkou_jissi ), 0 ) as tougetu_senkou_jissi_count,
                            COALESCE( sum( tougetu_jissi ), 0 ) as tougetu_jissi_count,
                            COALESCE( sum( tougetu_jissi_machi ), 0 ) as tougetu_jissi_machi_count,
                            COALESCE( sum( tougetu_keika_jissi ), 0 ) as tougetu_keika_jissi_count
                            
                        FROM
                            tb_tougetu
                            
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
                            , '合計' as tgc_car_name -- 車種

                            , sum( target_count ) as target_count
                            , sum( daigae_count ) as daigae_count

                            --, sum( senkou_yoyaku_count ) as senkou_yoyaku_count
                            , sum( senkou_jissi_count ) as senkou_jissi_count

                            , sum( tougetu_senkou_yoyaku_count ) as tougetu_senkou_yoyaku_count
                            , sum( tougetu_yoyaku_count ) as tougetu_yoyaku_count
                            , sum( tougetu_keika_yoyaku_count ) as tougetu_keika_yoyaku_count

                            , sum( tougetu_senkou_jissi_count ) as tougetu_senkou_jissi_count
                            , sum( tougetu_jissi_count ) as tougetu_jissi_count
                            , sum( tougetu_jissi_machi_count ) as tougetu_jissi_machi_count
                            , sum( tougetu_keika_jissi_count ) as tougetu_keika_jissi_count
                            
                            , COALESCE( sum( plan.plan_data ), 0 ) as plan_data
                            
                        FROM
                            tb_tougetu_total tougetu

                        LEFT JOIN tb_senkou_total senkou ON
                            tougetu.base_code = senkou.base_code
                            
                        LEFT JOIN tb_plan plan ON
                            tougetu.base_code = plan.plan_base_code AND
                            plan.plan_user_id is null AND
                            plan.plan_ym = '{$search->inspection_ym_from}' 
                            
                        {$whereSql} ";
        }else{
            // 台数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            '' as user_name
                            , tougetu.base_code
                            , tougetu.base_short_name
                            
                            , COALESCE( target_count, 0 ) as target_count
                            , COALESCE( daigae_count, 0 ) as daigae_count
                            
                            --, COALESCE( senkou_yoyaku_count, 0 ) as senkou_yoyaku_count
                            , COALESCE( senkou_jissi_count, 0 ) as senkou_jissi_count

                            , COALESCE( tougetu_senkou_yoyaku_count, 0 ) as tougetu_senkou_yoyaku_count
                            , COALESCE( tougetu_yoyaku_count, 0 ) as tougetu_yoyaku_count
                            , COALESCE( tougetu_keika_yoyaku_count, 0 ) as tougetu_keika_yoyaku_count

                            , COALESCE( tougetu_senkou_jissi_count, 0 ) as tougetu_senkou_jissi_count
                            , COALESCE( tougetu_jissi_count, 0 ) as tougetu_jissi_count
                            , COALESCE( tougetu_jissi_machi_count, 0 ) as tougetu_jissi_machi_count
                            , COALESCE( tougetu_keika_jissi_count, 0 ) as tougetu_keika_jissi_count
                            
                            , COALESCE( plan_data, 0 ) as plan_data

                        FROM
                            tb_tougetu_total tougetu

                        LEFT JOIN tb_senkou_total senkou ON
                            tougetu.base_code = senkou.base_code
                            
                        LEFT JOIN tb_plan plan ON
                            tougetu.base_code = plan.plan_base_code AND
                            plan.plan_user_id is null AND
                            plan.plan_ym = '{$search->inspection_ym_from}'

                        {$whereSql}

                        ORDER BY
                            tougetu.base_code ASC ";
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
        $whereSql = ResultDB::getWhereSql( $search );

        if( !empty( $whereSql ) ){
            $whereSql .= " AND tougetu.user_id IS NOT NULL ";
        }else{
            $whereSql = " WHERE tougetu.user_id IS NOT NULL ";
        }

        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereCoreSql = ResultDB::getWhereCoreSql( $search );

        // 先行実施を取得するSQL
        $senkouSql = ResultDB::getSenkouSql( $search );
        // 当月実施を取得するSQL
        $tougetsuSql = ResultDB::getTougetuSql( $search, $whereCoreSql );
        
        $sql = "    {$senkouSql},

                    -- 先行実績の総数を取得
                    tb_senkou_total AS (
                        SELECT
                            user_id,
                            user_name,
                            base_code,
                            base_short_name, 

                            --COALESCE( sum( senkou_yoyaku ), 0 ) as senkou_yoyaku_count,
                            COALESCE( sum( senkou_jissi ), 0 ) as senkou_jissi_count

                        FROM
                            tb_senkou

                        GROUP BY
                            user_id,
                            user_name,
                            base_code,
                            base_short_name 
                    ),
                    
                    {$tougetsuSql},

                    -- 当月実績の総数を取得
                    tb_tougetu_total AS (
                        SELECT
                            user_id,
                            user_name,
                            base_code,
                            base_short_name,

                            COALESCE( sum( target ), 0 ) as target_count,
                            COALESCE( sum( daigae ), 0 ) as daigae_count,

                            COALESCE( sum( tougetu_senkou_yoyaku ), 0 ) as tougetu_senkou_yoyaku_count,
                            COALESCE( sum( tougetu_yoyaku ), 0 ) as tougetu_yoyaku_count,
                            COALESCE( sum( tougetu_keika_yoyaku ), 0 ) as tougetu_keika_yoyaku_count,

                            COALESCE( sum( tougetu_senkou_jissi ), 0 ) as tougetu_senkou_jissi_count,
                            COALESCE( sum( tougetu_jissi ), 0 ) as tougetu_jissi_count,
                            COALESCE( sum( tougetu_jissi_machi ), 0 ) as tougetu_jissi_machi_count,
                            COALESCE( sum( tougetu_keika_jissi ), 0 ) as tougetu_keika_jissi_count

                        FROM
                            tb_tougetu
                            
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
                            , '合計' as tgc_car_name -- 車種

                            , sum( target_count ) as target_count
                            , sum( daigae_count ) as daigae_count

                            --, sum( senkou_yoyaku_count ) as senkou_yoyaku_count
                            , sum( senkou_jissi_count ) as senkou_jissi_count

                            , sum( tougetu_senkou_yoyaku_count ) as tougetu_senkou_yoyaku_count
                            , sum( tougetu_yoyaku_count ) as tougetu_yoyaku_count
                            , sum( tougetu_keika_yoyaku_count ) as tougetu_keika_yoyaku_count

                            , sum( tougetu_senkou_jissi_count ) as tougetu_senkou_jissi_count
                            , sum( tougetu_jissi_count ) as tougetu_jissi_count
                            , sum( tougetu_jissi_machi_count ) as tougetu_jissi_machi_count
                            , sum( tougetu_keika_jissi_count ) as tougetu_keika_jissi_count
                            
                            , COALESCE( sum( plan.plan_data ), 0 ) as plan_data


                        FROM
                            tb_tougetu_total tougetu

                        LEFT JOIN tb_senkou_total senkou ON
                            tougetu.user_id = senkou.user_id
                            
                        LEFT JOIN tb_plan plan ON
                            tougetu.base_code = plan.plan_base_code AND
                            tougetu.user_id = plan.plan_user_id AND
                            plan.plan_ym = '{$search->inspection_ym_from}' 

                        {$whereSql} ";
        }else{
            // 台数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            tougetu.user_id
                            , tougetu.user_name
                            , tougetu.base_code
                            , tougetu.base_short_name
                            
                            , COALESCE( target_count, 0 ) as target_count
                            , COALESCE( daigae_count, 0 ) as daigae_count

                            --, COALESCE( senkou_yoyaku_count, 0 ) as senkou_yoyaku_count
                            , COALESCE( senkou_jissi_count, 0 ) as senkou_jissi_count

                            , COALESCE( tougetu_senkou_yoyaku_count, 0 ) as tougetu_senkou_yoyaku_count
                            , COALESCE( tougetu_yoyaku_count, 0 ) as tougetu_yoyaku_count
                            , COALESCE( tougetu_keika_yoyaku_count, 0 ) as tougetu_keika_yoyaku_count

                            , COALESCE( tougetu_senkou_jissi_count, 0 ) as tougetu_senkou_jissi_count
                            , COALESCE( tougetu_jissi_count, 0 ) as tougetu_jissi_count
                            , COALESCE( tougetu_jissi_machi_count, 0 ) as tougetu_jissi_machi_count
                            , COALESCE( tougetu_keika_jissi_count, 0 ) as tougetu_keika_jissi_count
                            
                            , COALESCE( plan_data, 0 ) as plan_data

                        FROM
                            tb_tougetu_total tougetu
                            
                        LEFT JOIN tb_senkou_total senkou ON
                            tougetu.user_id = senkou.user_id
                            
                        LEFT JOIN tb_plan plan ON
                            tougetu.base_code = plan.plan_base_code AND
                            tougetu.user_id = plan.plan_user_id AND
                            plan.plan_ym = '{$search->inspection_ym_from}'
                            
                        {$whereSql}
                            
                        ORDER BY
                            tougetu.user_id ASC ";
        }
        
        return DB::select( $sql );
    }

    #########################
    ## 車種別の集計データの取得
    #########################

    /**
     * 車種名での集計を取得する
     * @param unknown $search
     */
    public static function summaryCarType( $search, $outputFlg="", $sort=[] ) {
        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereSql = ResultDB::getWhereSql( $search, 3 );
        
        // 検索条件を取得し、検索オブジェクトの値を加工する
        $whereCoreSql = ResultDB::getWhereCoreSql( $search );

        // 先行実施を取得するSQL
        $senkouSql = ResultDB::getSenkouSql( $search );
        // 当月実施を取得するSQL
        $tougetsuSql = ResultDB::getTougetuSql( $search, $whereCoreSql );
        
        $sql = "    {$senkouSql},

                    -- 先行実績の総数を取得
                    tb_senkou_total AS (
                        SELECT
                            tgc_car_name, 

                            --COALESCE( sum( senkou_yoyaku ), 0 ) as senkou_yoyaku_count,
                            COALESCE( sum( senkou_jissi ), 0 ) as senkou_jissi_count

                        FROM
                            tb_senkou

                        {$whereSql}

                        GROUP BY
                            tgc_car_name 
                    ),
                    
                    {$tougetsuSql},

                    -- 当月実績の総数を取得
                    tb_tougetu_total AS (
                        SELECT
                            tgc_car_name, 

                            COALESCE( sum( target ), 0 ) as target_count,
                            COALESCE( sum( daigae ), 0 ) as daigae_count,

                            COALESCE( sum( tougetu_senkou_yoyaku ), 0 ) as tougetu_senkou_yoyaku_count,
                            COALESCE( sum( tougetu_yoyaku ), 0 ) as tougetu_yoyaku_count,
                            COALESCE( sum( tougetu_keika_yoyaku ), 0 ) as tougetu_keika_yoyaku_count,

                            COALESCE( sum( tougetu_senkou_jissi ), 0 ) as tougetu_senkou_jissi_count,
                            COALESCE( sum( tougetu_jissi ), 0 ) as tougetu_jissi_count,
                            COALESCE( sum( tougetu_jissi_machi ), 0 ) as tougetu_jissi_machi_count,
                            COALESCE( sum( tougetu_keika_jissi ), 0 ) as tougetu_keika_jissi_count

                        FROM
                            tb_tougetu

                        {$whereSql}

                        GROUP BY
                            tgc_car_name 
                    ) ";

        // 総数の時の処理
        if( $outputFlg == "total" ){
            // 総数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            '合計' as user_name
                            , '' as base_code
                            , '' as base_short_name
                            , '合計' as tgc_car_name -- 車種

                            , sum( target_count ) as target_count
                            , sum( daigae_count ) as daigae_count

                            --, sum( senkou_yoyaku_count ) as senkou_yoyaku_count
                            , sum( senkou_jissi_count ) as senkou_jissi_count

                            , sum( tougetu_senkou_yoyaku_count ) as tougetu_senkou_yoyaku_count
                            , sum( tougetu_yoyaku_count ) as tougetu_yoyaku_count
                            , sum( tougetu_keika_yoyaku_count ) as tougetu_keika_yoyaku_count

                            , sum( tougetu_senkou_jissi_count ) as tougetu_senkou_jissi_count
                            , sum( tougetu_jissi_count ) as tougetu_jissi_count
                            , sum( tougetu_jissi_machi_count ) as tougetu_jissi_machi_count
                            , sum( tougetu_keika_jissi_count ) as tougetu_keika_jissi_count

                        FROM
                            tb_tougetu_total tougetu

                        LEFT JOIN tb_senkou_total senkou ON
                            tougetu.tgc_car_name = senkou.tgc_car_name ";
        }else{
            // 台数を取得するSQL
            $sql .= "   -- 先行と当月実施の台数をまとめて取得
                        SELECT
                            tougetu.tgc_car_name

                            , COALESCE( target_count, 0 ) as target_count
                            , COALESCE( daigae_count, 0 ) as daigae_count

                            --, COALESCE( senkou_yoyaku_count, 0 ) as senkou_yoyaku_count
                            , COALESCE( senkou_jissi_count, 0 ) as senkou_jissi_count

                            , COALESCE( tougetu_senkou_yoyaku_count, 0 ) as tougetu_senkou_yoyaku_count
                            , COALESCE( tougetu_yoyaku_count, 0 ) as tougetu_yoyaku_count
                            , COALESCE( tougetu_keika_yoyaku_count, 0 ) as tougetu_keika_yoyaku_count
                            
                            , COALESCE( tougetu_senkou_jissi_count, 0 ) as tougetu_senkou_jissi_count
                            , COALESCE( tougetu_jissi_count, 0 ) as tougetu_jissi_count
                            , COALESCE( tougetu_jissi_machi_count, 0 ) as tougetu_jissi_machi_count
                            , COALESCE( tougetu_keika_jissi_count, 0 ) as tougetu_keika_jissi_count
                            
                        FROM
                            tb_tougetu_total tougetu

                        LEFT JOIN tb_senkou_total senkou ON
                            tougetu.tgc_car_name = senkou.tgc_car_name

                        ORDER BY
                            tougetu.tgc_car_name {$sort["sort"]["tgc_car_name"]} ";
        }

        return DB::select( $sql );
    }

}
