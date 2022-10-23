<?php

namespace App\Models\Append;

use App\Models\AbstractModel;

/**
 * TMRデータに関するモデル
 */
class Tmr extends AbstractModel {
    // テーブル名
    protected $table = 'tb_tmr';

    // 変更可能なカラム
    protected $fillable = [
        'tmr_base_code_init',
        'tmr_user_id',
        'tmr_user_code_init',
        'tmr_customer_code',
        'tmr_custmer_name',
        'tmr_manage_number',
        'tmr_syaken_next_date',
        'tmr_last_process_status',
        'tmr_last_call_result',
        'tmr_call_times',
        'tmr_last_call_date',
        'tmr_to_base_comment',
        'tmr_in_sub_intention',
        'tmr_in_sub_detail',
        'tmr_call_1_status',
        'tmr_call_1_result',
        'tmr_call_1_date',
        'tmr_call_2_status',
        'tmr_call_2_result',
        'tmr_call_2_date',
        'tmr_call_3_status',
        'tmr_call_3_result',
        'tmr_call_3_date',
        'tmr_call_4_status',
        'tmr_call_4_result',
        'tmr_call_4_date',
        'tmr_call_5_status',
        'tmr_call_5_result',
        'tmr_call_5_date',
        'tmr_call_6_status',
        'tmr_call_6_result',
        'tmr_call_6_date'
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
        
        Tmr::updateOrCreate(
            [
                'tmr_manage_number' => $values['tmr_manage_number'],
                //'tmr_syaken_next_date' => $values['tmr_syaken_next_date']	// 次回車検日
                'tmr_last_call_date' => $values['tmr_last_call_date']		// 最終架電日
            ],
            $values
        );
        
    }
    
}
