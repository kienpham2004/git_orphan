<?php

namespace App\Original\Codes\Inspect;

use App\Lib\Codes\Code;

/**
 * クレジット区分を表すコード
 *
 * @author yhatsutori
 *
 */
class InspectInsuTypes extends Code {

    private $codes = [
        '自加入' => '自加入',
        '他加入' => '他加入'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
