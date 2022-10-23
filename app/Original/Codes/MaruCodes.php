<?php

namespace App\Original\Codes;

use App\Lib\Codes\Code;

/**
 * ○を表すコード
 *
 * @author OH_karasawa
 *
 */
class MaruCodes extends Code {

    const ARI = '1';

    private $codes = [
        '1' => '○'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
    public static function isAri($value) {
        return static::ARI == $value;
    }
}
