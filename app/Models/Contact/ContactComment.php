<?php

namespace App\Models\Contact;

use App\Models\AbstractModel;

/**
 * 活動日報実績に関するモデル
 */
class ContactComment extends AbstractModel {
    // テーブル名
    protected $table = 'tb_contact_comment';

    // 変更可能なカラム
    protected $fillable = [
        'ctccom_customer_code', // 顧客コード
        'ctccom_contact_date',  // 活動実績日
        'ctccom_contact_memo',  // コメント記述
        'created_at',           // 登録日時
        'updated_at',           // 更新日時
        'deleted_at',           // 削除フラグ
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
        
        // 顧客コードと活動実績日が同じものは上書き
        ContactComment::updateOrCreate(
            [
                'ctccom_customer_code' => $values['ctccom_customer_code'],
                'ctccom_contact_date' => $values['ctccom_contact_date']
            ],
            $values
        );
    }

}
