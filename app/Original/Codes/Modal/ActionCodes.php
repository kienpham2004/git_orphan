<?php

namespace App\Original\Codes\Modal;

use App\Lib\Codes\Code;

/**
 * 活動内容
 *
 * @author yhatsutori
 *
 */
class ActionCodes extends Code {
    // Aily後テキスト   5,6,7,8追加＠201902
    private $codes = [
        '1' => '電話',
        '2' => '訪問',
        '3' => '来店',
        '4' => 'メール',
        '5' => 'ｺｰﾙ音',
        '6' => '留守電',
        '7' => '投函',
        '8' => '納回'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
