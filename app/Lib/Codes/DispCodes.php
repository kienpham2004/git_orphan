<?php

namespace App\Lib\Codes;

/**
 * 表示/非表示を表すコード
 *
 * @author yhatsutori
 *
 */
class DispCodes extends Code {

    const DISP = '1';
    const NO_DISP = '9';

    private $codes = [
        '1' => '表示',
        '9' => '非表示'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

    public function getValueOrNull($code, $default = '非表示') {
        $value = $this->getValue($code);
        if(empty($value)) {
            $value = $default;
        }
        return $value;
    }

    public static function isDisp($value) {
        return static::DISP == $value;
    }
}
