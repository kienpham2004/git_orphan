<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 活動内容
 *
 * @author yhatsutori
 *
 */
class HtcMemberRegistStatus extends Code {

    private $codes = [
        '0' => '未ログイン',
        '1' => 'ログイン済'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
