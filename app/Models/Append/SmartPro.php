<?php

namespace App\Models\Append;

use App\Lib\Util\DateUtil;
use App\Models\AbstractModel;
use DB;

/**
 * スマートプロ査定データに関するモデル
 */
class SmartPro extends AbstractModel {
    // テーブル名
    protected $table = 'tb_smart_pro';

    // 変更可能なカラム
    protected $fillable = [
        'smart_base_code_init',
        'smart_user_name_csv',
        'smart_day',
        'smart_car_base_number',
        'smart_nintei_type',
        'smart_car_num',
        'smart_syaken_yuukou_day',
        'smart_client_name',
        'smart_syoyuusya_name',
        'smart_siyousya_name',
        'smart_nego_kbn',
        'smart_nego_cartype',
        'smart_irai_datetime',
        'smart_irai_comment',
        'smart_user_id',
        'smart_user_code_init',
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
        
        if ( !is_null( $values['smart_car_base_number'] ) == True && !is_null( $values['smart_nintei_type'] ) == True ){
            // 統合車両管理Noと作業内容と次回車検日が合致するデータは更新
            SmartPro::updateOrCreate(
                [
                    'smart_car_base_number' => $values['smart_car_base_number'], 
                    'smart_nintei_type' => $values['smart_nintei_type'],
                    'smart_day' => $values['smart_day']
                ],
                $values
            );
            
        }
    }
    
}
