<?php

namespace App\Original\Codes\Inspect;

use App\Lib\Codes\Code;

/**
 * クレジット区分を表すコード
 *
 * @author yhatsutori
 *
 */
class InspectCreditTypes extends Code {

    const INSPECT_A = 'A';
    const INSPECT_B = 'B';
    const INSPECT_C = 'C';
    const INSPECT_9 = '9';
    
    private $codes = [
        'A' => '据置クレ',
        'B' => '通クレ',
        'C' => '残クレ(HFC保証)',
        'D' => '通クレ(不均等)',
        'E' => '通クレ(頭金逆引)',
        '1' => '通クレ(回数指定)',
        '2' => '通クレ(月額指定)',
        '5' => 'ボーナス一括払',
        '6' => '翌々月一括払',
        '7' => '通クレ(一括：お客様)',
        '8' => '通クレ(一括：販売店)',
        '9' => '残クレ(販社保証)'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
    public static function isInspect_A($value) {
        return static::INSPECT_A == $value;
    }

    public static function isInspect_B($value) {
        return static::INSPECT_B == $value;
    }

    public static function isInspect_C($value) {
        return static::INSPECT_C == $value;
    }

    public static function isInspect_9($value) {
        return static::INSPECT_9 == $value;
    }

}
