<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 獲得源泉（保険）を表すコード
 *
 * @author yhatsutori
 *
 */
class InsuStatusGensenCodes extends Code {
    
    private $codes = [
        '1' => '自社新規',
        '2' => '他社新規'
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
