<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 手続き内容を表すコード
 *
 * @author OH_Iiyama
 *
 */
class InsuTetsudukiCodes extends Code {
    
    private $codes = [
        '新規'      => '新規',
        '変更'      => '変更',
        '更改'      => '更改',
        '解約'      => '解約',
        '長期の確認' => '長期の確認'
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
