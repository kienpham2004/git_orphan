<?php

namespace App\Lib\Codes;

/**
 * 済／未を表すコード
 *
 * @author daidv
 *
 */
class FinishedCodes extends Code {

    private $codes = [
        '0' => '未',
        '1' => '済'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
}
