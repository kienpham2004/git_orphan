<?php

namespace App\Models\Append;

use App\Models\AbstractModel;
use App\Lib\Util\DateUtil;

/**
 * チャオモデル
 */
class Ciao extends AbstractModel {

    // テーブル名
    protected $table = 'tb_ciao';

    // 変更可能なカラム
    protected $fillable = [
        'ciao_base_code_init',
        'ciao_base_name', 
        'ciao_user_id',
        'ciao_user_code_init',
        'ciao_user_name', 
        'ciao_customer_code', 
        'ciao_customer_name',
        'ciao_car_manage_number',
        'ciao_car_name',
        'ciao_car_base_number_min', 
        'ciao_car_base_number', 
        'ciao_number', 
        'ciao_course', 
        'ciao_first_regist_date_ym', 
        'ciao_syaken_manryo_date', 
        'ciao_syaken_next_date', 
        'ciao_money', 
        'ciao_start_date', 
        'ciao_end_date', 
        'ciao_jisshi_type', 
        'ciao_jisshi_yotei', 
        'ciao_jisshi', 
        'ciao_jisshi_flg', 
        'ciao_course_keizoku', 
        'ciao_kaiinsyou_hakkou_date', 
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
        if ( !is_null( $values['ciao_customer_code'] ) == True && !is_null( $values['ciao_car_base_number'] ) == True ){
            // 統合車両管理Noに不正な値がはいっていたので、除外
            if( $values['ciao_car_base_number'] != "#N/A" ){
                Ciao::updateOrCreate(
                    [
                        'ciao_customer_code' => $values['ciao_customer_code'], 
                        'ciao_car_base_number' => $values['ciao_car_base_number'], 
                        'ciao_number' => $values['ciao_number'], 
                        'ciao_end_date' => $values['ciao_end_date']
                    ],
                    $values
                );
                
            }
        }
    }

}
