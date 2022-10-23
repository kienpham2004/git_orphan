<?php

namespace App\Lib\Codes;

/**
 * 削除を表すコード
 *
 * @author yhatsutori
 *
 */
class CheckAriCodes extends Code {

    const ARI = '1';

    private $codes = [
        '1' => '有'
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
