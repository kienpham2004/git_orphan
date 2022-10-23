<?php

namespace App\Original\Codes;

use App\Lib\Codes\Code;

/**
 * キャッシュレスを有
 *
 * @author OH_karasawa
 *
 */
class CheckboxAriCodes extends Code {

    private $codes = [
        '1' => '有'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
