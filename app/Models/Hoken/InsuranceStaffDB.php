<?php

namespace App\Models\Hoken;

use App\Models\AbstractModel;
use DB;

/**
 * 個人販売計画登録のモデル
 */
class InsuranceStaffDB extends AbstractModel {
    
    /**
     * 指定された拠点コードの対象年に該当する成約の値を取得
     * @param  string $year      対象年
     * @return 指定された拠点コードの対象年に該当する成約の値を取得
     */
    public static function getPlanList( $year="" ){
        // テーブル名
        $tableName = 'tb_insurance_staff_num';
        
        // 主なSQL
        $sql = "    SELECT
                        base.id as base_id,
                        base.base_code,
                        base.base_name,
                        base.base_short_name,
                        
                        plan04.plan_value as plan_value_04,
                        plan05.plan_value as plan_value_05,
                        plan06.plan_value as plan_value_06,
                        plan07.plan_value as plan_value_07,
                        plan08.plan_value as plan_value_08,
                        plan09.plan_value as plan_value_09,
                        plan10.plan_value as plan_value_10,
                        plan11.plan_value as plan_value_11,
                        plan12.plan_value as plan_value_12,
                        plan01.plan_value as plan_value_01,
                        plan02.plan_value as plan_value_02,
                        plan03.plan_value as plan_value_03
                    FROM
                        tb_base base

                    LEFT JOIN {$tableName} plan04 on (
                        plan04.plan_base_code = base.base_code::text and
                        plan04.plan_year = ? and
                        plan04.plan_month = '04'
                    )
                    LEFT JOIN {$tableName} plan05 on (
                        plan05.plan_base_code = base.base_code::text and
                        plan05.plan_year = ? and
                        plan05.plan_month = '05'
                    )
                    LEFT JOIN {$tableName} plan06 on (
                        plan06.plan_base_code = base.base_code::text and
                        plan06.plan_year = ? and
                        plan06.plan_month = '06'
                    )
                    LEFT JOIN {$tableName} plan07 on (
                        plan07.plan_base_code = base.base_code::text and
                        plan07.plan_year = ? and
                        plan07.plan_month = '07'
                    )
                    LEFT JOIN {$tableName} plan08 on (
                        plan08.plan_base_code = base.base_code::text and
                        plan08.plan_year = ? and
                        plan08.plan_month = '08'
                    )
                    LEFT JOIN {$tableName} plan09 on (
                        plan09.plan_base_code = base.base_code::text and
                        plan09.plan_year = ? and
                        plan09.plan_month = '09'
                    )
                    LEFT JOIN {$tableName} plan10 on (
                        plan10.plan_base_code = base.base_code::text and
                        plan10.plan_year = ? and
                        plan10.plan_month = '10'
                    )
                    LEFT JOIN {$tableName} plan11 on (
                        plan11.plan_base_code = base.base_code::text and
                        plan11.plan_year = ? and
                        plan11.plan_month = '11'
                    )
                    LEFT JOIN {$tableName} plan12 on (
                        plan12.plan_base_code = base.base_code::text and
                        plan12.plan_year = ? and
                        plan12.plan_month = '12'
                    )
                    LEFT JOIN {$tableName} plan01 on (
                        plan01.plan_base_code = base.base_code::text and
                        plan01.plan_year = ? and
                        plan01.plan_month = '01'
                    )
                    LEFT JOIN {$tableName} plan02 on (
                        plan02.plan_base_code = base.base_code::text and
                        plan02.plan_year = ? and
                        plan02.plan_month = '02'
                    )
                    LEFT JOIN {$tableName} plan03 on (
                        plan03.plan_base_code = base.base_code::text and
                        plan03.plan_year = ? and
                        plan03.plan_month = '03'
                    )
                    WHERE
                        base.deleted_at IS NULL

                    ORDER BY
                        base.base_code ASC ";

        // データの取得
        $showData = DB::select(
            $sql,
            [
                $year,
                $year,
                $year,
                $year,
                $year,
                $year,
                $year,
                $year,
                $year,
                $year + 1,
                $year + 1,
                $year + 1
            ]
        );
        
        return $showData;
    }

    /**
     * 成約計画の値を更新
     * @param  string $base_code 拠点コード
     * @param  string $year      年
     * @return [type]            [description]
     */
    public static function updatePlanList( $planValue="", $base_code="", $year="", $month="" ){
        // 月の判定を行う
        if( !empty( $month ) == True ){
            // 1月2月3月の時は年度をマイナスにする
            if( in_array( $month, ["01", "02", "03"] ) == True ){
                $year = strval( intval( $year ) + 1 );
            }
        }
        
        // テーブル名
        $tableName = 'tb_insurance_staff_num';
        
        // データの更新SQL
        $sql = "    UPDATE
                        {$tableName}
                    SET
                        plan_value = ?,
                        updated_at = now(),
                        updated_by = 'システム'
                    WHERE
                        plan_base_code = ? AND
                        plan_year = ? AND
                        plan_month = ? ;";

        // データの更新
        DB::update(
            $sql,
            [$planValue, $base_code, $year, $month]
        );

        // データの追加のSQL
        $sql = "    INSERT INTO
                        {$tableName}
                            ( plan_base_code, plan_year, plan_month, plan_value, created_at, updated_at, created_by, updated_by )
                    SELECT
                        ?,
                        ?,
                        ?,
                        ?,
                        now(),
                        now(),
                        'システム',
                        'システム'
                    WHERE
                        NOT EXISTS (
                            SELECT
                                1
                            FROM
                                {$tableName}
                            WHERE
                                plan_base_code = ? AND
                                plan_year = ? AND
                                plan_month = ?
                        ); ";

        // データの追加
        DB::insert(
            $sql,
            [$base_code, $year, $month, $planValue, $base_code, $year, $month]
        );
    }

}
