<?php

namespace App\Lib\Codes;

/**
 * 拠点レベルを表すコード
 *
 * @author yhatsutori
 *
 */
class WorkLevelCodes extends Code {

    private $codes = [
        '1' => '新車拠点',
        '2' => '中古車拠点'
    ];

    public function __construct() {
        parent::__construct($this->codes);
    }
}
