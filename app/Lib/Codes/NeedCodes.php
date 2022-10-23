<?php

namespace App\Lib\Codes;

/**
 * 要/不要を表すコード
 *
 * @author yhatsutori
 *
 */
class NeedCodes extends Code {

    private $codes = [
        '1' => '要',
        '0' => '不要'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
