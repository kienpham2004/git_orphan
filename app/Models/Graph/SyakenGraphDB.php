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
class SyakenGraphDB{
  
    #############################
    ## トレジャーボード(車検)
    #############################

    /**
     * トレジャーボード(車検)のデータを取得する
     * 処理速度の問題で、クエリビルダーやEloquentを使わずに
     * ほぼ生のSQLを利用（with句を使いたいので）
     * これにより、次のメソッドを変更した
     *
     * private static function subQueryTargetCarsSummary
     *
     * @param $search
     */
    public static function getSyakenIkou( $search ){
        $s = microtime(true);
        
        // 対象反映(月数)
        $target_ym = 6;

        // 対象年月の指定がなければ現時刻のYM →　Ymd
        //$from = date('Ym');
        $from = date('Ym')."01";
        if( !empty( $search->inspection_ym_from ) OR $search->inspection_ym_from != "" ){
            //$from = $search->inspection_ym_from;
            $from = $search->inspection_ym_from . '01';
        }

        // 対象年月の指定がなければ現時刻から７ヶ月とし、
        // 対象年月が指定されている場合は、指定日時から７ヶ月とする
        if ( !empty( $search->inspection_ym_from ) OR $search->inspection_ym_from != "" ) {
            //$to = DateUtil::monthLater( $search->inspection_ym_from . "01", $target_ym, 'Ym' );
            $to = date('Ymd', strtotime('last day of' . DateUtil::monthLater( $search->inspection_ym_from . "01", $target_ym, 'Ymd' )));
        } else {
            //$to = DateUtil::monthLater( date( 'Ymd' ), $target_ym, 'Ym' );
            $to = date('Ymd', strtotime('last day of' . DateUtil::monthLater( date( 'Ymd' ), $target_ym, 'Ymd' )));
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
//
//        if( !empty($search->ciao) ) {
//            // 両方にチェック
//            if( in_array("0", $search->ciao) && in_array("1", $search->ciao) ){
//                $queryCiao = "(tgc_ciao_course is null OR tgc_ciao_course is not null) AND ";
//            }
//
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

        // 6ヶ月基準の除外条件（この日以前に登録済みかどうか）
        // config/originalから条件値を取得
        //$reference_date = config('original.six_lock_exclusion');
        $created = 'tb_target_cars.created_at';
        
        // ログイン者の拠点のデータのみ表示
        $querLoginBase = "";
        
        // ログイン情報を取得
        $loginAccountObj = SessionUtil::getUser();
        
        // 店長, 工場長, 営業担当, CAの人の時は、所属拠点のみ表示
        //if( in_array( $loginAccountObj->getRolePriority(), [4,5,6,7] ) ){
        if( in_array( $loginAccountObj->getRolePriority(), [6,7] ) ){
            // 拠点コードを取得
            $base_id = $loginAccountObj->getBaseId();
            
            $querLoginBase = " account.base_id = {$base_id} AND ";

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
                        tgc_credit_hensaihouhou_name,
                        tgc_credit_manryo_date_ym,
                        tgc_status_update,

                        CASE
                          WHEN info.mi_htc_login_flg = '1' THEN 7
                          WHEN info.mi_tmr_in_sub_intention = '入庫意向有' THEN 1
                          WHEN info.mi_tmr_in_sub_intention = '自社予約済' THEN 2
                          WHEN info.mi_tmr_in_sub_intention = '代替意向有' THEN 3
                          WHEN info.mi_tmr_in_sub_intention = '他社予約済' THEN 4
                          WHEN info.mi_tmr_in_sub_intention = '代替意向無' THEN 5
                          WHEN info.mi_tmr_in_sub_intention = '入庫意向無' THEN 6
                          -- ELSE 0
                        END as tmr_status,

                        CASE
                            -- 代替済
                            WHEN
                                info.mi_dsya_keiyaku_car IS NOT NULL
                            THEN 103
                            
                            -- 入庫済
                            WHEN
                                info.mi_dsya_syaken_jisshi_date IS NOT NULL
                            THEN 102

                            -- 予約済
                            WHEN
                                info.mi_dsya_syaken_reserve_date IS NOT NULL
                                -- 2022/03/22 add
                                AND to_char(tgc_syaken_next_date + '-12 month'::interval, 'YYYYMM'::text) < to_char(info.mi_dsya_syaken_reserve_date, 'YYYYMM')
                                THEN 101
                            WHEN tgc_status in (11,12,13,14,15,16,17)
                            THEN tgc_status                          
                            ELSE 0
                        END as target_status,

                        tb_target_cars.tgc_alert_memo,

                        info.mi_smart_day,
                        info.mi_rstc_put_in_date,
                        info.mi_ik_final_achievement,
                        info.mi_rstc_reserve_status,
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
                            -- 2022/03/23 update check
                            --WHEN tb_target_cars.tgc_sj_shukka_date is not null THEN 1
                            --WHEN tb_target_cars.tgc_sj_shukka_date is null AND tb_target_cars.tgc_syaken_times = 1 THEN 2
                            --WHEN tb_target_cars.tgc_sj_shukka_date is null AND tb_target_cars.tgc_syaken_times >= 2 THEN 3 
                            WHEN tb_target_cars.tgc_syaken_times = 1 THEN 1 
                            WHEN tb_target_cars.tgc_sj_shukka_date is not null THEN 2 
                            ELSE 3
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
                          --tb_target_cars.tgc_base_id = $search->base_code AND
                          tgc_inspection_id = '4' AND
                          --(
                          --  --date_trunc('day', tgc_syaken_next_date - interval '6 months') >= {$created}
                          --  to_char((tgc_syaken_next_date - interval '6 months'), 'yyyymm') >=  to_char({$created}, 'yyyymm') 
                          --  OR 
                          --{$created} < '{reference_date}') AND
                          tgc_lock_flg6 = 0 AND
                          {$queryCiao}
                          (tgc_syaken_next_date >= '{$from}' and tgc_syaken_next_date <= '{$to}')
                          --tgc_inspection_ym between '{$from}' and '{$to}'                  
                  )
                  SELECT
                        account.user_id,
                        account.user_name,
                        account.base_id,
                        account.base_code,
                        target.target_id,
                        target.tgc_user_id,
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
                        target.tgc_credit_hensaihouhou_name,
                        target.tgc_credit_manryo_date_ym,
                        
                        -- 入庫日
                        target.mi_rstc_put_in_date,

                        -- アラートメモ
                        target.tgc_alert_memo,

                        -- 査定データ
                        target.mi_smart_day,

                        -- チャオ
                        target.tgc_ciao_course,
                        target.tgc_ciao_end_date,
                        
                        -- csvデータ有無
                        target.umu_csv_flg,
                        
                        -- 最終実績
                        target.mi_ik_final_achievement,
                        
                        -- 状況
                        target.mi_rstc_reserve_status,
                        
                        -- 作業進捗：納車済日時
                        target.mi_rstc_delivered_date,
                        
                        target.mi_rcl_recall_flg,
                        target.mi_rstc_web_reserv_flg,
                        target.mi_ctc_seiyaku_flg,
                        -- 2022/02/17 add select field
                        target.mi_shijo_6m1m,
                        target.mi_syodan_6m1m,
                        target.mi_satei_6m1m,
                        target.mi_dsya_syaken_jisshi_date,
                        target.jisshi_flg
                        
                    FROM
                        tmp_tb_user_account account

                    left join tmp_tb_target_cars as target on
                      account.user_id = target.tgc_user_id

                    WHERE
                      --tb_user_account.deleted_at is null AND
                      account.base_code = '{$search->base_code}' AND
                      --target.flg_6month = '1' AND
                      {$queryUser}
                      {$querLoginBase}
                      target.target_id IS NOT NULL

                    GROUP BY                        
                        user_id,
                        user_name,
                        account.base_id,
                        account.base_code,
                        target.tgc_user_id,
                        --target.tgc_base_id,
                        target.target_id,
                        target.target_status,
                        target.tmr_status,                        
                        target.tgc_customer_name_kanji,
                        target.tgc_customer_kouryaku_flg,
                        target.tgc_syaken_times,
                        target.tgc_car_name,
                        target.tgc_car_manage_number,
                        target.tgc_syaken_next_date,
                        target.tgc_inspection_ym,
                        target.tgc_alert_memo,
                        target.mi_smart_day,
                        target.tgc_ciao_course,
                        target.tgc_ciao_end_date,
                        target.umu_csv_flg,
                        target.tgc_credit_hensaihouhou_name,
                        target.tgc_credit_manryo_date_ym,
                        target.mi_rstc_put_in_date,
                        target.mi_rstc_reserve_status,
                        target.mi_ik_final_achievement,
                        target.mi_rstc_delivered_date,
                        target.tgc_status_update,
                        target.mi_rcl_recall_flg,
                        target.mi_rstc_web_reserv_flg,
                        target.mi_ctc_seiyaku_flg,
                        target.mi_shijo_6m1m,
                        target.mi_syodan_6m1m,
                        target.mi_satei_6m1m,
                        target.mi_dsya_syaken_jisshi_date,
                        target.jisshi_flg
                        
                    ORDER BY
                        user_id asc,
                        target.umu_csv_flg asc,
                        CASE 
                            When  target_status = 0 then 0
                            When  target_status = 11 then 1
                            When  target_status = 12 then 3
                            When  target_status = 13 then 2
                            When  target_status = 14 then 4
                            When  target_status = 15 then 5
                            When  target_status = 16 then 7
                            When  target_status = 17 then 6
                            When  target_status = 101 then 8
                            When  target_status = 102 then 9
                            When  target_status = 103 then 10
                            else 11 end asc,
                            
                        --target.target_status asc,
                        target.tmr_status asc,
                        target.tgc_inspection_ym asc,
                        target.tgc_syaken_next_date asc ";
                        
        $record = DB::select( $sql );
     
        return $record;
    }

    #############################
    ## トレジャーボード(車検状況)
    #############################
    
    /**
     * 復活するかもしれないので、コメントアウト
     * ベースとなるWITH文を取得
     * @param  [type] $inspection_ym [description]
     * @param  [type] $addWhereSql   [description]
     * @return [type]                [description]
     */
    /*
    public static function getSyakenJisshiSql( $inspection_ym, $addWhereSql ){
        // 対象月の1ヶ月先(先行実施台数を当月対象としない為の対策)
        $inspection_ym_from_back = date( 'Ym', strtotime( '-1 month', strtotime( $inspection_ym . "01" ) ) );
        // 対象月の1ヶ月先(先行実施台数対策)
        $inspection_ym_from_next = date( 'Ym', strtotime( '+1 month', strtotime( $inspection_ym . "01" ) ) );

        // トレジャーボードの画面の集計
        $sql =  " WITH tb_treasure_jisshi AS(
                    SELECT
                      tgc.id,
                      tgc.tgc_customer_name_kanji,
                      tgc.tgc_car_model,
                      tgc.tgc_car_name,
                      tgc.tgc_car_frame_number,
                      tgc.tgc_inspection_id,
                      tgc.tgc_inspection_ym,
                      tgc.tgc_car_manage_number,
                      
                      tgc_user_id,
                      tgc_user_name,
                      
                      CASE
                          WHEN
                              info.mi_rstc_get_out_date is not null AND
                              info.mi_rstc_get_out_date <> '1970-01-01 09:00:00' AND
                              tgc_inspection_ym = '{$inspection_ym_from_next}' AND
                              to_char(info.mi_rstc_get_out_date, 'yyyymm') < '{$inspection_ym_from_next}'
                              THEN 4
                          WHEN
                              info.mi_rstc_get_out_date is not null AND
                              info.mi_rstc_get_out_date <> '1970-01-01 09:00:00' AND
                              tgc_inspection_ym = '{$inspection_ym}' AND
                              to_char(info.mi_rstc_get_out_date, 'yyyymm') = '{$inspection_ym}'
                              THEN 5
                          ELSE 0
                      END AS back_num

                    FROM
                      tb_target_cars tgc

                    LEFT JOIN tb_manage_info info ON
                        tgc.tgc_car_manage_number = info.mi_car_manage_number AND
                        tgc.tgc_inspection_ym = info.mi_inspection_ym AND
                        tgc.tgc_inspection_id = info.mi_inspection_id

                    WHERE
                      tgc.tgc_inspection_id in ('4') AND
                      tgc.tgc_inspection_ym between '{$inspection_ym}' AND '{$inspection_ym_from_next}' AND
                      to_char( info.mi_rstc_get_out_date, 'yyyymm' ) > '{$inspection_ym_from_back}' AND
                      {$addWhereSql}
                  ) ";
        
        return $sql;
    }
    */
    
    /**
     * 復活するかもしれないので、コメントアウト
     * トレジャーボード(車検状況)のメインのデータを取得
     * @param  [type] $inspection_ym [description]
     * @param  [type] $user_id       [description]
     * @return [type]                [description]
     */
    /*
    public static function getSyakenJisshi( $inspection_ym, $user_id ){
        //
        $tableMotoValues = array();

        // 検索条件
        $addWhereSql = " tgc.tgc_user_id = '{$user_id}' ";
        // ベースとなるWITH文を取得
        $withSql = self::getSyakenJisshiSql( $inspection_ym, $addWhereSql );

        // トレジャーボードの画面の集計
        $sql =  "   {$withSql}

                    SELECT
                        target.id,
                        target.tgc_customer_name_kanji,
                        target.tgc_car_model,
                        target.tgc_car_name,
                        target.tgc_car_frame_number,
                        target.tgc_inspection_id,
                        target.tgc_inspection_ym,
                        target.tgc_car_manage_number,
                        target.back_num

                    FROM
                        tb_treasure_jisshi target

                    WHERE
                        target.back_num <> 0

                    ORDER BY
                        tgc_inspection_ym asc,
                        back_num DESC ";

        // データの取得
        $tableMotoValues = DB::select( $sql );

        return $tableMotoValues;
    }
    */
     
    /**
     * 復活するかもしれないので、コメントアウト
     * トレジャーボード(車検状況)で使用する担当者情報を取得
     * @param  [type] $inspection_ym [description]
     * @param  [type] $base_code     [description]
     * @return [type]                [description]
     */
    /*
    public static function getSyakenJisshiUsers( $inspection_ym, $base_code ){
        // 自車検に該当する担当者を格納する変数
        $userList = array();

        // 検索条件
        $addWhereSql = " tgc_base_code = '{$base_code}' ";
        // ベースとなるWITH文を取得
        $withSql = self::getSyakenJisshiSql( $inspection_ym, $addWhereSql );

        // 担当者情報を取得するSQL
        $sql =  "   {$withSql}
                    
                    SELECT
                        target.tgc_user_id,
                        target.tgc_user_name,
                        COUNT(*) kensu

                    FROM
                        tb_treasure_jisshi target

                    WHERE
                        target.back_num <> 0

                    GROUP BY
                        tgc_user_id,
                        tgc_user_name

                    ORDER BY
                        kensu DESC ";

        // データの取得
        $userList = DB::select( $sql );

        return $userList;
    }
    */
    
}
