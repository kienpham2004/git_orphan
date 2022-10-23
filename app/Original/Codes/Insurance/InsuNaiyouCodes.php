<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 処理内容
 *
 * @author yhatsutori
 *
 */
class InsuNaiyouCodes extends Code {

    private $codes = [
        '1' => '車両入替',
        '2' => '補償内容の変更',
        '6' => '契約訂正',
        '7' => '住所変更',
        '8' => '口座登録',
        '5' => '中途更改',
        '3' => '解約',
        '4' => 'その他'
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
