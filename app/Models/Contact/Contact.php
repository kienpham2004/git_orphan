<?php

namespace App\Models\Contact;

use App\Models\AbstractModel;

/**
 * 活動日報実績に関するモデル
 */
class Contact extends AbstractModel {
    // テーブル名
    protected $table = 'tb_contact';

    // 変更可能なカラム
    protected $fillable = [
        'ctc_user_id',          // 担当者Id
        'ctc_user_code_init',  // 初期担当者コード
        'ctc_customer_code',    // 顧客コード
        'ctc_contact_date',     // 活動実績日
        'ctc_contact_ym',       // 活動実績日の年月
        'ctc_contact_number',   // 活動実績連番
        'ctc_contact_memo',     // コメント記述
        'ctc_way_code',         // 接触方法コード
        'ctc_yotei_number',     // 接触予定連番
        'ctc_result_code',      // 接触成果コード
        'ctc_result_name',      // 接触成果名称
        'ctc_car_manage_number',// 統合車両管理No
    ];
    
    // 日付のカラム
    protected $dates = [
        'created_at',
        'updated_at',
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
        
        // 接触方法コードと接触成果コードの存在で判定
        $ctc_way_code = !empty($values['ctc_way_code']) ? $values['ctc_way_code'] : '';
        $ctc_result_code = !empty($values['ctc_result_code']) ? $values['ctc_result_code'] : '';
        
        Contact::updateOrCreate(
            [
                'ctc_customer_code' => $values['ctc_customer_code'],
                'ctc_contact_date' => $values['ctc_contact_date'],
                'ctc_way_code' => $ctc_way_code, 
                'ctc_result_code' => $ctc_result_code
            ],
            $values
        );
        
    }

}
