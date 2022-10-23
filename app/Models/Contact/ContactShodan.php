<?php

namespace App\Models\Contact;

use App\Models\AbstractModel;

/**
 * 接触情報のうち最新のデータのみを抽出したモデル
 * 試乗のみ
 */
class ContactShodan extends AbstractModel {
    // テーブル名
    protected $table = 'tb_contact_shodan';

    // 変更可能なカラム
    protected $fillable = [
        'ctcsho_user_id',           // 担当者コード
        'ctcsho_customer_code',     // 顧客コード
        'ctcsho_contact_date',      // 活動実績日
        'ctcsho_contact_ym',        // 活動実績日の年月
        'ctcsho_car_manage_number', // 統合車両管理No
        'ctcsho_result_code',       // 接触成果コード
        'ctcsho_result_name',       // 接触成果名称
        'created_by',               // 登録者
        'updated_by',               // 更新者
    ];

    // 日付のカラム
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    ###########################
    ## Shijo List Commands
    ###########################
    
    /**
     * 検索条件を指定するメソッド
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereRequest( $query, $requestObj ){
        return $query;
    }

}
