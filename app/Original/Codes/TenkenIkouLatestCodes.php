<?php

namespace App\Original\Codes;

use App\Lib\Codes\Code;
use App\Lib\Util\Constants;
/**
 * 意向確認の最新意向を表すコード
 *
 * @author y_ohishi
 *
 */
class TenkenIkouLatestCodes extends Code {
    private $codes = [
        '1' => '未確定',
        '2' => '入庫意向',
        '3' => '代替意向',
        '4' => '点検意向無',
        '5' => '抹消・転売',
        '21' => '他社車検（確定）',
        '22' => '他社代替（確定）',
        '23' => '自社代替（確定）'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
