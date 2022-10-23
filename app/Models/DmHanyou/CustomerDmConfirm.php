<?php

namespace App\Models\DmHanyou;

use App\Models\AbstractModel;

/**
 * Dmの確認に関するモデル
 */
class CustomerDmConfirm extends AbstractModel {
    // テーブル名
    protected $table = 'tb_customer_dm_confirm';
    
    // 変更可能なカラム
    protected $fillable = [
        'user_id',
        'base_code',
        'inspection_ym',
        'dm_confirm_flg'
    ];

}
