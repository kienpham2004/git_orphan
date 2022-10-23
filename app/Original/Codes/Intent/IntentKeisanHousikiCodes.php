<?php

namespace App\Original\Codes\Intent;

use App\Lib\Codes\Code;

/**
 * 活動意向（クレジット）を表すコード
 *
 * @author yhatsutori
 *
 */
class IntentKeisanHousikiCodes extends Code {
    
    private $codes = [
        '1' => '実質年率',
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
}
