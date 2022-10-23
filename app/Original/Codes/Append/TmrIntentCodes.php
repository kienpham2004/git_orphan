<?php

namespace App\Original\Codes\Append;

use App\Lib\Codes\Code;

/**
 * Tmr意向を表すコード
 *
 * @author yhatsutori
 *
 */
class TmrIntentCodes extends Code {
	
    private $codes = [
		'1' => '入庫意向有',
		'2' => '自社予約済',
		'3' => '代替意向有',
		'4' => '他社予約済',
		'5' => '代替意向無',
		'6' => '入庫意向無'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
