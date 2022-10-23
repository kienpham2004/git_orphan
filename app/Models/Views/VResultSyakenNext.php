<?php

namespace App\Models\Views;

use App\Models\AbstractModel;

/**
 * 次回車検日が書き換わってしまったデータの紐づけ用
 */
class VResultSyakenNext extends AbstractModel {
    // テーブル名
    protected $table = 'v_syaken_result_rext';

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
                $join->on( 'v_syaken_result_rext.rst_manage_number', '=', 'tb_customer.car_manage_number' )
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
        $builderObj = VResultSyakenNext::joinCustomer();

        // 検索条件を指定
        $builderObj = $builderObj->wherePeriodNormal( 'rst_inspection_date', $from, $to );
        
        // 並び替えの処理
        $builderObj = $builderObj->orderBy( 'rst_inspection_date', 'asc' );
        
        // 値を取得
        $data = $builderObj->get();

        return $data;
    }

}
