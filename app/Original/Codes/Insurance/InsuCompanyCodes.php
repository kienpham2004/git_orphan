<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 保険会社の一覧
 *
 * @author yhatsutori
 *
 */
class InsuCompanyCodes extends Code {

    private $codes = [
        '東京海上日動火災' => '東京海上日動火災',
        '損保ジャパン日本興亜' => '損保ジャパン日本興亜',
        '三井住友海上火災' => '三井住友海上火災'
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
