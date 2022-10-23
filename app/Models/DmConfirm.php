<?php

namespace App\Models;

/**
 * Dmの確認に関するモデル
 */
class DmConfirm extends AbstractModel {
    // テーブル名
    protected $table = 'tb_dm_confirm';
    
    // 変更可能なカラム
    protected $fillable = [
        'user_id',
        'base_code',
        'inspection_ym',
        'dm_confirm_flg',
        'flg_6month_before',
        'flg_souki_nyuko',
        'flg_tenken',
    ];

}
