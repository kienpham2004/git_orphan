<?php

namespace App\Original\Codes\Intent;

use App\Lib\Codes\Code;

/**
 * 活動意向（点車検）を表すコード
 *
 * @author yhatsutori
 *
 */
class IntentSyatenkenCodes extends Code {
    
    // 本番用
    private $codes = [
//        '1' => '自社車検(旧：入庫意向)',
//        '2' => '自社車検(旧：自社予約済)',
//        '3' => '自社代替(旧：自社代替予定)',
//        '4' => '自社代替(旧：自社代替済)',
//        '5' => '他社車検(旧：他社意向)',
//        '6' => '他社車検(旧：他社予約済)',
//        '7' => '他社代替(旧：他社代替)',
//        '8' => '転居予定(旧：転居・他)',
        '11' => '自社車検',
        '12' => '他社車検',
        '13' => '自社代替',
        '14' => '他社代替',
        '15' => '廃車・転売',
        '16' => '転居予定',
        '17' => '拠点移管',
        '20' => '未確認'
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
