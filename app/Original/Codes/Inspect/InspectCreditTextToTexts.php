<?php

namespace App\Original\Codes\Inspect;

use App\Lib\Codes\Code;

/**
 * クレジット区分を表すコード
 *
 * @author yhatsutori
 *
 */
class InspectCreditTextToTexts extends Code {

    private $codes = [
        '据置クレジット（回数指定）'            => '据置クレ',
        '残価クレジット（回数指定：HFC保証）'   => '残クレ(HFC保証)',
        '通常クレジット（頭金逆引）'            => '通クレ(頭金逆引)',
        '通常クレジット（回数指定）'            => '通クレ(回数指定)',
        '通常クレジット（月額指定）'            => '通クレ(月額指定)',
        'ボーナス一括払'                        => 'ボーナス一括払',
        '翌々月一括払'                          => '翌々月一括払',
        '通常クレジット（一括払い：お客様）'    => '通クレ(一括：お客様)',
        '通常クレジット（一括払い：販売店）'    => '通クレ(一括：販売店)',
        '残価設定型クレジット（販社保証）'      => '残クレ(販社保証)'
//        => '通クレ',
//        => '通クレ(不均等)',
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
