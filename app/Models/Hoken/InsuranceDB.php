<?php

namespace App\Models\Hoken;

use App\Models\Revision\CustomerRev;
use DB;

/**
 * tb_insurance保険データを調べる処理する為のDB
 */
class InsuranceDB{
    
    /**
     * 未接触敗戦者をさがして、未接触敗戦扱いにする
     * @return [type] [description]
     */
    public static function setMisessyokuHaisenRecords() {
        //
        $sql = "    UPDATE tb_insurance
                    SET
                        -- 未接触敗戦の時99で登録
                        insu_status = 99
                    WHERE
                        -- 満期日が現在の日時よりも小さい時
                        insu_insurance_end_date < to_date( now()::text, 'yyyy-mm-dd' ) AND
                        -- 他社分・純新規の時の処理(自社分は除く)
                        (
                            insu_jisya_tasya = '他社分' OR
                            insu_jisya_tasya = '純新規'
                        ) AND
                        -- 何も入力されていない時
                        (
                            coalesce( insu_status, 0 ) = 0 AND
                            coalesce( insu_action, 0 ) = 0 AND
                            coalesce( insu_memo, '' ) = '' AND
                            coalesce( insu_contact_plan_date, NULL ) IS NULL AND
                            coalesce( insu_contact_date, NULL ) IS NULL AND
                            coalesce( insu_contact_mail_tuika, '' ) = '' AND
                            coalesce( insu_contact_jigu, 0 ) = 0 AND
                            coalesce( insu_contact_taisyo, 0 ) = 0 AND
                            coalesce( insu_contact_taisyo_name, '' ) = '' AND
                            coalesce( insu_contact_syaryo_type, '' ) = '' AND
                            coalesce( insu_contact_daisya, 0 ) = 0
                            --AND
                            --coalesce( insu_updated_history, '' ) = ''
                        ) ";

        return DB::select( $sql );
    }

    /**
     * 保険csvの「契約結果」に日付が存在した場合、insu_status の値を登録する
     * 自社分の場合 →　21　獲得済み
     * 他社分の場合 →　6 　更新済（同条件）
     * ※但し、insu_status が手入力済の場合を除く
     * @return [type] [description]
     */
    public static function updateInsuranceStatus() {
        //
        $sql = "    UPDATE tb_insurance
                    SET
                        insu_status = 
                            ( CASE
                                WHEN insu_jisya_tasya =  '自社分' THEN 21
                                WHEN insu_jisya_tasya =  '他社分' THEN 6
                                ELSE NULL
                            END )                        
                    WHERE
                        insu_keiyaku_kekka IS NOT NULL AND
                        insu_status  IS NULL AND 
                        to_char(updated_at, 'yyyymmdd') = to_char(current_date, 'yyyymmdd')
                 ";
        return DB::select( $sql );
    }

}
