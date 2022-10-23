<?php

namespace App\Lib\Codes;

/**
 * 削除を表すコード
 *
 * @author yhatsutori
 *
 */
class CheckCodes extends Code {

    const NASHI = '0';
    const ARI = '1';

    private $codes = [
        '0' => '無',
        '1' => '有'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

    public static function isNashi($value) {
        return static::NASHI == $value;
    }

    public static function isAri($value) {
        return static::ARI == $value;
    }
}
