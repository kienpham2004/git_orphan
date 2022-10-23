<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 自社・他社
 *
 * @author yhatsutori
 *
 */
class InsuJisyaTasyaCodes extends Code {

    private $codes = [
        '自社分' => '自社分',
        '他社分' => '他社分',
        '追加' => '追加',
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
