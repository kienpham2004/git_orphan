<?php

namespace App\Models\Append;

use App\Models\AbstractModel;

/**
 * 代替車検推進管理に関するモデル
 */
class Htc extends AbstractModel
{
    // テーブル名
    protected $table = 'tb_htc';

    // 変更可能なカラム
    protected $fillable = [
        'htc_number',               // HTC会員番号
        'htc_customer_number',      // 顧客No.
        'htc_customer_name',        // お客様名
        'htc_syadai_number',        // 車台番号
        'htc_model_name',           // 車種名
        'htc_appication_date',      // HTC申込日
        'htc_car_regist_date',      // HTC車両登録日
        'htc_my_dealer',            // Myディーラー
        'htc_member_regist_status', // HTC会員サイト登録状況
        'htc_not_regist_reson',     // 登録不要理由
        'htc_login_status',         // HTC会員サイトお客様初回ログイン状況
        'htc_user',                 // 担当営業

        "created_by",
        "updated_by"
    ];

    ###########################
    ## CSVの処理
    ###########################

    /**
     * データの登録と更新
     * @param  [type] $values [description]
     * @return [type]         [description]
     * @todo   車点検種別がある場合は基本的に必須項目とする
     */
    public static function merge($values)
    {
        //\Log::debug( $values );

        Htc::updateOrCreate(
            [
                'htc_number' => $values['htc_number'], // HTC会員番号
                'htc_customer_number' => $values['htc_customer_number'], // 顧客No
            ],
            $values
        );

    }

}
