<?php

namespace App\Lib\Codes;

/**
 * 抹消理由区分を表すコード
 *
 * @author ルック
 *
 */
class MassyoReasonCodes extends Code {

    private $codes = [
        "0"  => "",
        "1"  => "敗戦/代済",
        "2"  => "転売",
        "3"  => "廃車",
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
