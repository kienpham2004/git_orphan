<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 活動内容
 *
 * @author yhatsutori
 *
 */
class InsuActionCodes extends Code {

    private $codes = [
        '1' => '電話',
        '2' => '来店',
        '3' => '訪問',
        '4' => 'メール'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
