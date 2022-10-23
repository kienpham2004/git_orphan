<?php

namespace App\Models\Hoken;

use App\Lib\Util\DateUtil;
use DB;
use App\Lib\Util\Constants;

/**
 * トレジャーボード保険に関するDB
 */
class HokenGraphDB{
  
    #############################
    ## トレジャーボード(保険意向)
    #############################

    /**
     * トレジャーボード（保険）のデータを取得する
     * 処理速度の問題で、クエリビルダーやEloquentを使わずに
     * ほぼ生のSQLを利用（with句を使いたいので）
     * これにより、次のメソッドを変更した
     *
     * private static function subQueryTargetCarsSummary
     *
     * @param $search
     */
    public static function getHokenIkou( $search ){
        $s = microtime(true);
        
        // 対象反映(月数)
        $target_ym = 3;

        // 対象年月の指定がなければ現時刻のYM
        $from = date('Ym');
        if( empty( $search->inspection_ym_from ) OR $search->inspection_ym_from != "" ){
          $from = $search->inspection_ym_from;
        }

        // 対象年月の指定がなければ現時刻から７ヶ月とし、
        // 対象年月が指定されている場合は、指定日時から７ヶ月とする
        if ( empty( $search->inspection_ym_from ) OR $search->inspection_ym_from != "" ) {
            $to = DateUtil::monthLater( $search->inspection_ym_from . "01", $target_ym, 'Ym' );
        } else {
            $to = DateUtil::monthLater( date( 'Ymd' ), $target_ym, 'Ym' );
        }

        // 担当者の絞り
//        if( isset( $search->user_name ) == True && !empty( $search->user_name ) == True ){
//          $queryUser = " target.user_name ILIKE '%{$search->user_name}%' AND ";
//        }else{
//          $queryUser = "";
//        }
        if( isset( $search->user_id ) == True && !empty( $search->user_id ) == True ){
            if ($search->user_id == Constants::CONS_TAISHOKUSHA_CODE) {
                $queryUser = " deleted_at is not null AND ";
            } else {
                $queryUser = " user_id = '{$search->user_id}' AND ";
            }
        }else{
          $queryUser = "";
        }

        // メインのSQL
        $sql  = "   WITH tmp_tb_user_account AS (
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
                            account.base_id,
                            account.base_code,
                            account.base_name,
                            account.user_id,
                            account.user_name,
                            insu.id as target_id,                            
                            -- 計上月か、保険対象月を対象とする
                            CASE
                                WHEN
                                    insu_contact_keijyo_ym IS NOT NULL
                                    THEN insu_contact_keijyo_ym
                                ELSE
                                    -- 主に自社分対応ですが、対象年月と、満期年月が同じ時人を対象とする為
                                    -- 対象年月軸とする
                                    insu_inspection_ym
                            END AS insu_inspection_make_ym,

                            --insu.insu_base_code as base_code,
                            --replace( replace( insu.insu_user_name , '　', ' ') , '  ', ' ') as user_name,
                            insu.insu_customer_name,
                            coalesce( insu.insu_status, 0 ) as insu_status,
                            insu.insu_jisya_tasya,
                            insu.insu_insurance_end_date,
                            insu.insu_add_tetsuduki_date,
                            insu.insu_status_gensen,
                            insu.insu_pair_fleet,
                            insu.insu_alert_memo,
                            --insu.insu_user_id as user_id
                            account.deleted_at
                        FROM
                            tb_insurance insu
                        LEFT JOIN
                            tmp_tb_user_account account ON account.user_id = insu.insu_user_id                            
                        WHERE
                            -- 非表示は除外
                            insu.deleted_at IS NULL
                    ),
                    target AS(
                        SELECT
                            insu.target_id,
                            base_id,
                            base_code,
                            user_id,
                            user_name,
                            insu.insu_customer_name,
                            insu.insu_inspection_make_ym,

                            -- 獲得済みを左に表示する為に一工夫
                            CASE
                                WHEN insu.insu_status = '6' THEN 1 -- 獲得済み
                                WHEN insu.insu_status = '4' THEN 2 -- 確約
                                WHEN insu.insu_status = '3' THEN 3 -- 獲得予定
                                WHEN insu.insu_status = '2' THEN 4 -- 提案中
                                WHEN insu.insu_status = '1' THEN 5 -- キビしい
                                WHEN insu.insu_status = '0' THEN 6 -- 未接触
                                WHEN insu.insu_status = '5' THEN 7 -- 敗戦
                                WHEN insu.insu_status = '99'  THEN insu.insu_status -- 未連絡敗戦はそのまま
                                WHEN insu.insu_status = '100' THEN insu.insu_status -- 対象外はそのまま
                                ELSE  insu.insu_status + 10
                            END as insu_status,

                            insu.insu_jisya_tasya,
                            insu.insu_insurance_end_date,
                            insu.insu_add_tetsuduki_date,
                            insu.insu_status_gensen,
                            insu.insu_pair_fleet,
                            insu.insu_alert_memo,
                            deleted_at

                        FROM
                            target_base insu 
                        --LEFT JOIN
                        --    target_base insu ON acc.user_id = insu.insu_user_id
                            
                        WHERE
                            {$queryUser}
                            insu.base_code = '{$search->base_code}' AND
                            -- 保険対象月
                            insu.insu_inspection_make_ym between '{$from}' and '{$to}'
                    )
                    SELECT
                        target.target_id,
                        target.base_id,
                        target.base_code,
                        target.user_id,
                        target.user_name,
                        target.insu_customer_name,
                        target.insu_inspection_make_ym,
                        target.insu_status,
                        target.insu_jisya_tasya,
                        target.insu_insurance_end_date,
                        target.insu_add_tetsuduki_date,
                        target.insu_status_gensen,
                        target.insu_pair_fleet,
                        target.insu_alert_memo
                    FROM
                        target
                    WHERE
                        -- 2017-11-24 初鳥 追加
                        (
                            target.insu_jisya_tasya = '他社分' OR
                            target.insu_jisya_tasya = '純新規'
                        ) AND
                        target.target_id IS NOT NULL
                    GROUP BY
                        target.target_id,
                        target.base_id,
                        target.base_code,
                        target.user_id,
                        target.user_name,
                        target.insu_customer_name,
                        target.insu_inspection_make_ym,
                        target.insu_status,
                        target.insu_jisya_tasya,
                        target.insu_insurance_end_date,
                        target.insu_add_tetsuduki_date,
                        target.insu_status_gensen,
                        target.insu_pair_fleet,
                        target.insu_alert_memo
                    ORDER BY
                        target.insu_status asc,
                        target.user_name asc,
                        target.insu_insurance_end_date asc
                         ";

        $record = DB::select( $sql );
        
        return $record;
    }

}
