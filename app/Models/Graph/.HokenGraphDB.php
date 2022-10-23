<?php

namespace App\Models\Graph;

use App\Lib\Util\DateUtil;
use DB;

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
        $target_ym = 6;

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
        if( isset( $search->user_id ) == True && !empty( $search->user_id ) == True ){
          $queryUser = " tb_user_account.user_id = '{$search->user_id}' AND ";
        }else{
          $queryUser = "";
        }

        // メインのSQL
        $sql  = "   SELECT
                        tb_user_account.user_id,
                        tb_user_account.user_name,
                        target.target_id,
                        target.insu_customer_name_kanji,
                        target.insu_car_name,
                        target.insu_car_base_number,
                        target.insu_car_manage_number,
                        target.insu_inspection_ym,
                        target.insu_status,
                        target.insu_customer_kouryaku_flg,
                        target.insu_customer_insurance_type,
                        target.insu_customer_insurance_company,
                        target.insu_customer_insurance_end_date

                    FROM
                        tb_user_account

                    LEFT JOIN
                    (
                        SELECT
                            insu.id as target_id,
                            insu.insu_user_id,
                            insu.insu_customer_name_kanji,
                            insu.insu_car_name,
                            insu.insu_car_base_number,
                            insu.insu_car_manage_number,
                            insu.insu_inspection_ym,
                            insu.insu_status,
                            insu.insu_customer_kouryaku_flg,
                            insu.insu_customer_insurance_type,
                            insu.insu_customer_insurance_company,
                            insu.insu_customer_insurance_end_date

                        FROM
                            tb_insurance insu

                        WHERE
                            insu.insu_base_code = '{$search->base_code}' AND
                            insu.insu_inspection_ym between '{$from}' and '{$to}'

                    ) as target on
                        tb_user_account.user_id = target.insu_user_id

                    WHERE
                        tb_user_account.deleted_at is null AND
                        tb_user_account.base_code = '{$search->base_code}' AND
                        {$queryUser}
                        target.target_id IS NOT NULL

                    GROUP BY
                        user_name,
                        user_id,
                        target.target_id,
                        target.insu_customer_name_kanji,
                        target.insu_car_name,
                        target.insu_car_base_number,
                        target.insu_car_manage_number,
                        target.insu_inspection_ym,
                        target.insu_status,
                        target.insu_customer_kouryaku_flg,
                        target.insu_customer_insurance_type,
                        target.insu_customer_insurance_company,
                        target.insu_customer_insurance_end_date
                        
                    ORDER BY
                        tb_user_account.user_id asc,
                        target.insu_inspection_ym asc,
                        target.insu_status desc,
                        target.insu_customer_insurance_type asc ";

        $record = DB::select( $sql );
        
        return $record;
    }

    #############################
    ## トレジャーボード(保険状況)
    #############################
    
    /**
     * ベースとなるWITH文を取得
     * @param  [type] $inspection_ym [description]
     * @param  [type] $addWhereSql   [description]
     * @return [type]                [description]
     */
    public static function getHokenJisshiSql( $inspection_ym, $addWhereSql ){
        // トレジャーボードの画面の集計
        $sql =  "   WITH tb_treasure_jisshi AS(
                        SELECT
                            insu.id,
                            insu.insu_customer_name_kanji,
                            insu.insu_inspection_ym,
                            insu.insu_new_insurance_flg,
                            insu.insu_new_insurance_date,

                            insu_base_code,
                            base_short_name as insu_base_name,

                            insu_user_id,
                            user_name as insu_user_name,

                            CASE
                                WHEN
                                    insu_new_insurance_flg = 1
                                    THEN 1
                                ELSE 0
                            END AS back_num

                        FROM
                            tb_insurance_kakutoku insu
                        
                        LEFT JOIN tb_base ON
                            insu.insu_base_code = tb_base.base_code AND
                            tb_base.deleted_at IS NULL

                        LEFT JOIN tb_user_account ON
                            insu.insu_user_id = tb_user_account.user_id AND
                            tb_user_account.deleted_at IS NULL

                        WHERE
                            insu.insu_inspection_ym between '{$inspection_ym}' AND '{$inspection_ym}' AND
                            {$addWhereSql}
                  ) ";
        
        return $sql;
    }

    /**
     * トレジャーボード(保険状況)のメインのデータを取得
     * @param  [type] $inspection_ym [description]
     * @param  [type] $user_id       [description]
     * @return [type]                [description]
     */
    public static function getHokenJisshi( $inspection_ym, $user_id ){
        //
        $tableMotoValues = array();

        // 検索条件
        $addWhereSql = " insu.insu_user_id = '{$user_id}' ";
        // ベースとなるWITH文を取得
        $withSql = self::getHokenJisshiSql( $inspection_ym, $addWhereSql );

        // トレジャーボードの画面の集計
        $sql =  "   {$withSql}

                    SELECT
                        target.id,
                        target.insu_customer_name_kanji,
                        target.insu_inspection_ym,
                        target.insu_new_insurance_flg,
                        target.insu_new_insurance_date,

                        target.insu_base_code,
                        target.insu_base_name,

                        target.insu_user_id,
                        target.insu_user_name,

                        target.back_num

                    FROM
                        tb_treasure_jisshi target

                    WHERE
                        target.back_num <> 0

                    ORDER BY
                        insu_inspection_ym asc,
                        back_num DESC ";

        // データの取得
        $tableMotoValues = DB::select( $sql );

        return $tableMotoValues;
	}

    /**
     * トレジャーボード(保険状況)で使用する担当者情報を取得
     * @param  [type] $inspection_ym [description]
     * @param  [type] $base_code     [description]
     * @return [type]                [description]
     */
    public static function getHokenJisshiUsers( $inspection_ym, $base_code ){
        // 自保険に該当する担当者を格納する変数
        $userList = array();

        // 検索条件
        $addWhereSql = " insu_base_code = '{$base_code}' ";
        // ベースとなるWITH文を取得
        $withSql = self::getHokenJisshiSql( $inspection_ym, $addWhereSql );

        // 担当者情報を取得するSQL
        $sql =  "   {$withSql}
                    
                    SELECT
                        target.insu_user_id,
                        target.insu_user_name,
                        COUNT(*) kensu

                    FROM
                        tb_treasure_jisshi target

                    WHERE
                        target.back_num <> 0

                    GROUP BY
                        insu_user_id,
                        insu_user_name

                    ORDER BY
                        kensu DESC ";

        // データの取得
        $userList = DB::select( $sql );

        return $userList;
    }

}
