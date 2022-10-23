<?php

namespace App\Original\Codes\Intent;

use App\Lib\Codes\Code;

/**
 * 活動意向（クレジット）を表すコード
 *
 * @author yhatsutori
 *
 */
class IntentCreditCodes extends Code {
    
    private $codes = [
        '8' => '再クレジット',
        '9' => '返却',
        '10' => '返却(自社代替)',
        '11' => '返却(他社代替)',
        '15' => '一括返済', // 2017-03-28 追加
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
}
