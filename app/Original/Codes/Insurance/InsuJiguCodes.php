<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 治具を表すコード
 *
 * @author OH_Iiyama
 *
 */
class InsuJiguCodes extends Code {
    
    private $codes = [
        '1' => 'HSS',
        '2' => '継続用紙',
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
