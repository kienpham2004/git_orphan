<?php

namespace App\Models\Views;

use App\Models\AbstractModel;

class VTargetAnkai extends AbstractModel {
    // テーブル名
    protected $table = 'v_tenken_ansin_kaiteki';

    ###########################
    ## スコープメソッド(Join文)
    ###########################
    
    /**
     * 顧客テーブルのJOIN
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function scopeJoinCustomer( $query ) {
        $query->leftJoin(
            'tb_customer',
            function( $join ){
            $join->on( 'v_tenken_ansin_kaiteki.car_manage_number', '=', 'tb_customer.car_manage_number' )
                ->whereNull( 'tb_customer.deleted_at' );
            }
        );

        return $query;
    }

    /**
     * 指定された期間の値を取得
     * @param  [type] $from [description]
     * @param  [type] $to   [description]
     * @return [type]       [description]
     */
    public static function findTarget( $from, $to ) {
        // 他のテーブルとJOIN
        $builderObj = VTargetAnkai::joinCustomer();

        // 検索条件を指定
        $builderObj = $builderObj->wherePeriodNormal( 'inspection_date', $from, $to );

        // 並び替えの処理
        $builderObj = $builderObj->orderBy( 'inspection_date', 'asc' );

        // 値を取得
        $data = $builderObj->get();

        return $data;
    }
    
}
