<?php

namespace App\Models;

use Log;

/**
 * 顧客データに関するモデル
 */
class CustomerUmu extends AbstractModel {
    // テーブル名
    protected $table = 'tb_customer_umu';

    // 変更不可なカラム
    protected $fillable = [
        'umu_car_manage_number',
        'umu_base_code_init',
        'umu_user_id',
        'umu_user_code_init',
        'umu_customer_code',
        'umu_csv_flg',
        'created_by',
        'updated_by'
    ];
    
    ###########################
    ## CSVの処理
    ###########################
    
    /**
     * データの登録と更新
     * @param  [type] $values [description]
     * @return [type]         [description]
     */
    public static function merge( $values ) {
            
        //\Log::debug( $values );
        CustomerUmu::updateOrCreate(
            [
                'umu_car_manage_number' => $values['umu_car_manage_number']
            ],
            $values
        );
        
    }

}
