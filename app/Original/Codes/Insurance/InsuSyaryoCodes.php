<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 車両(保険)を表すコード
 *
 * @author OH_Iiyama
 *
 */
class InsuSyaryoCodes extends Code {
    
    private $codes = [
        '一般' => '一般',
        '限定A' => '限定A',
        '車対車A' => '車対車A',
        '免一般' => '免一般',
        '免車対車A' => '免車対車A',
        '不担保' => '不担保'
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
