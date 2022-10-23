<?php

namespace App\Original\Codes\Append;

use App\Lib\Codes\Code;

/**
 * ABCを表すコード
 *
 * @author yhatsutori
 *
 */
class AbcCodes extends Code {

    private $codes = [
        'A' => 'A',
        'B' => 'B',
        'C' => 'C'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
}
