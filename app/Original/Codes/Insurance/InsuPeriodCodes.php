<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 保険期間（長期の推進)を表すコード
 *
 * @author OH_Iiyama
 *
 */
class InsuPeriodCodes extends Code {
    
    private $codes = [
        '1年' => '1年',
        '2年' => '2年',
        '3年' => '3年'
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
