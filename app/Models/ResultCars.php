<?php

namespace App\Models;

class ResultCars extends AbstractModel {
    // テーブル名
    protected $table = 'tb_result_cars';

    // 変更可能なカラム
    protected $fillable = [
        "rstc_inspection_id", 
        "rstc_inspection_ym", 
        "rstc_customer_id", 
        "rstc_base_code_init",
        "rstc_accept_date", 
        "rstc_customer_code", 
        "rstc_customer_name", 
        "rstc_user_id",
        "rstc_user_code_init",
        "rstc_user_name_csv",
        "rstc_user_base_code", 
        "rstc_car_name", 
        "rstc_manage_number", 
        "rstc_start_date", 
        "rstc_end_date", 
        "rstc_detail", 
        "rstc_hosyo_kbn", 
        "rstc_youmei", 
        "rstc_hikitori_value",
        "rstc_put_in_date", 
        "rstc_get_out_date", 
        "rstc_daisya_value",
        "rstc_reserve_commit_date", 
        "rstc_reserve_status", 
        "rstc_work_put_date", 
        "rstc_delivered_date", 
        "rstc_syaken_next_date",
        "rstc_machi_seibi_value",
        "rstc_web_reserv_flg"
    ];

    ###########################
    ## 抽出の処理
    ###########################
    
    /**
     * データの登録と更新
     * @param  [type] $values [description]
     * @return [type]         [description]
     */
    public static function merge( $values ) {
        $convertValues = static::convertFromViewTargetCars( $values );

        ResultCars::updateOrCreate(
            [
                'rstc_inspection_id' => $values['rst_inspection_id'],
                'rstc_inspection_ym' => $values['rst_inspection_date'],
                'rstc_manage_number' => $values['rst_manage_number'],
                'rstc_customer_code' => $values['rst_customer_code']
            ],
            $convertValues
        );
    }
    
    /**
     * update or insert
     * @param  [type] $values [description]
     * @return [type]         [description]
     */
    public static function convertFromViewTargetCars( $values ) {
        $filter = collect(
            [
                'id', 
                'customer_kouryaku_flg', 
                'customer_dm_flg', 
                'created_at', 
                'updated_at', 
                'deleted_at', 
                'created_by', 
                'updated_by', 
                'deleted_by'
            ]
        );

        foreach ( $values as $key => $value ) {
            $ex = $filter->contains( $key );
            //\Log::debug($ex);

            if( empty( $ex ) ) {
                \Log::debug( $key . '=' . $value );
                $key = str_replace( 'rst', 'rstc', $key );
                $result[$key] = $value;
            }
        }

        $result['rstc_customer_id'] = $values['id'];
        
        return $result;
    }
    
}
