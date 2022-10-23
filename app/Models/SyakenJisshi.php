<?php

namespace App\Models;

use Log;

/**
 * 車検実施リストに関するモデル
 */
class SyakenJisshi extends AbstractModel {
    // テーブル名
    protected $table = 'tb_syaken_jisshi';

    // 無効化
    public $timestamps = false;

    // 変更可能なカラム
    protected $fillable = [
        'sj_base_code_init',// 拠点 コード
        'sj_user_id',
        'sj_user_code_init',// 営業担当者 コード
        'sj_customer_code',// 顧客No.（IF）
        'sj_car_manage_number',// 統合車両管理No. IF
        'sj_syaken_next_date',// 車検満了日
        'sj_shukka_date',// 出荷報告日
        'created_at',// 作成日
        'updated_at',// 更新日
        'deleted_at',// 削除日
        'created_by',// 登録者
        'updated_by',// 更新者
    ];

    ###########################
    ## CSVの処理
    ###########################

    /**
     * データの登録と更新
     * @param  array
     * @return void
     */
    public static function merge( $values ) {
        //\Log::debug( $values );

        SyakenJisshi::updateOrCreate(
            [
                'sj_customer_code' => $values['sj_customer_code'],
                'sj_car_manage_number' => $values['sj_car_manage_number'],
            ],
            $values
        );
    }
}
