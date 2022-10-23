<?php

namespace App\Lib\Codes;

/**
 * DM不要理由を表すコード
 *
 * @author yhatsutori
 *
 */
class DmUnnecessaryReasonCodes extends Code {

    private $codes = [
        '1' => '予約済み',
        '2' => '乗っていない',
        '3' => '代替済',
        '4' => '商談中',
//        '5' => 'その他',
        '6' => '実施済',
        '7' => '転居転売',
        '8' => 'トラブル',
        '9' => '業者関連',
        
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
}
