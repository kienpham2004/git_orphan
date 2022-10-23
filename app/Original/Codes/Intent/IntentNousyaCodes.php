<?php

namespace App\Original\Codes\Intent;

use App\Lib\Codes\Code;

/**
 * 活動意向（納車）を表すコード
 *
 * @author yhatsutori
 *
 */
class IntentNousyaCodes extends Code {
    
    // 本番用
    private $codes = [
        '14' => '紹介依頼'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
