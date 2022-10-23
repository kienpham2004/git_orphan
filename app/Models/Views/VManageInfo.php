<?php

namespace App\Models\Views;

use App\Lib\Util\QueryUtil;
use App\Models\AbstractModel;
use App\Models\Customer;

class VManageInfo extends AbstractModel {
    // テーブル名
    protected $table = 'v_manage_info';

    public function scopeTargetYm( $query, $from, $to ) {
        $query = QueryUtil::str_between( $query, 'mi_inspection_ym', $from, $to );
        return $query;
    }

    public static function findTarget( $from, $to ) {
        return VManageInfo::targetYm( $from, $to )
                            ->orderBy( 'mi_inspection_ym', 'asc' )
                            ->get();
    }
    
}
