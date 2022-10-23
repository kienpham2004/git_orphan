<?php

namespace App\Original\Codes\Intent;

use App\Lib\Codes\Code;

/**
 * 活動意向（納車）を表すコード
 *
 * @author yhatsutori
 *
 */
class IntentKojinHojinCodes extends Code {
    
    // 本番用
    private $codes = [
        '1' => '個人',
        '2' => '法人',
        '3' => '官庁'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
