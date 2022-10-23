<?php

namespace App\Lib\Codes;

/**
 * ABCを表すコード
 *
 * @author yhatsutori
 *
 */
class ViewStatusCodes extends Code {

    private $codes = [
        '1' => '掲載期間中',
        '2' => '掲載終了'
    ];

    public function __construct() {
        parent::__construct($this->codes);
    }
}
