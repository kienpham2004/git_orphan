<?php

namespace App\Models\Append;

use App\Models\AbstractModel;

/**
 * ABCデータに関するモデル
 */
class Abc extends AbstractModel {
    // テーブル名
    protected $table = 'tb_abc';

    // 変更可能なカラム
    protected $fillable = [
        'abc_car_manage_number',
        'abc_abc'//, 
        //'abc_insurance_type', 
        //'abc_insurance_company', 
        //'abc_insurance_end_date'
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
        
        Abc::updateOrCreate(
            [
                'abc_car_manage_number' => $values['abc_car_manage_number']
            ],
            $values
        );
        
    }
    
}
