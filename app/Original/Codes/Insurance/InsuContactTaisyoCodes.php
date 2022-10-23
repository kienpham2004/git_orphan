<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 接触対象(本人：本人以外)を表すコード
 *
 * @author OH_Iiyama
 *
 */
class InsuContactTaisyoCodes extends Code {
    
    private $codes = [
        '1' => '本人',
        '2' => '本人以外',
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
