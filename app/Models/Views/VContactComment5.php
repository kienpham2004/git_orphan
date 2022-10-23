<?php

namespace App\Models\Views;

use App\Models\AbstractModel;

class VContactComment5 extends AbstractModel {
    // テーブル名
    protected $table = 'v_contact_comment5';
    /**
     * 指定された期間の値を取得
     * @param  [type] $from [description]
     * @param  [type] $to   [description]
     * @return [type]       [description]
     */
    public static function findTarget( $code ) {
        // 検索条件を指定
        $builderObj = VContactComment5::where( 'ctccom_customer_code',$code );

        // 値を取得
        $data = $builderObj->get();

        return $data;
    }
    
}
