<?php

namespace App\Original\Codes\Intent;

use App\Lib\Codes\Code;

/**
 * 活動意向（保険）を表すコード
 *
 * @author yhatsutori
 *
 */
class IntentInsuranceCodes extends Code {
    
    private $codes = [
        '12' => '継続',
        '13' => '継続落ち',
        '16' => '新規獲得', // 2017-03-28 追加
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
