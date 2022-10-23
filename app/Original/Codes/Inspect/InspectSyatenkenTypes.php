<?php

namespace App\Original\Codes\Inspect;

use App\Lib\Codes\Code;

/**
 * 車点検区分を表すコード
 *
 * @author yhatsutori
 *
 */
class InspectSyatenkenTypes extends Code {
    
    const INSPECT_M6 = '1';
    const INSPECT_AK = '2';
    const INSPECT_H12 = '3';
    const INSPECT_SK = '4';
    
    private $codes = [
        '1' => '無料６ヶ月',
        '2' => '安心快適',
        '3' => '法定１２ヶ月',
        '4' => '車検'
    ];
    
    public function __construct() {
        parent::__construct($this->codes);
    }
    
    public static function isInspectM6($value) {
        return static::INSPECT_M6 == $value;
    }
    
    public static function isInspectAK($value) {
        return static::INSPECT_AK == $value;
    }
    
    public static function isInspectH12($value) {
        return static::INSPECT_H12 == $value;
    }
    
    public static function isInspectSK($value) {
        return static::INSPECT_SK == $value;
    }
    
}
