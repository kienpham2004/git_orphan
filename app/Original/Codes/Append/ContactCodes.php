<?php

namespace App\Original\Codes\Append;

use App\Lib\Codes\Code;

/**
 * 接触内容を表すコード
 *
 * @author yhatsutori
 *
 */
class ContactCodes extends Code {

    private $codes = [
        '041' => '査定',
        '042' => '試乗',
        '043' => '見積り'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
}
