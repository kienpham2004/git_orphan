<?php

namespace App\Lib\Codes;

/**
 * ○/×を表すコード
 *
 * @author yhatsutori
 *
 */
class MaruBatsuCodes extends Code {

    private $codes = [
        '1' => '○',
        '2' => '×'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
