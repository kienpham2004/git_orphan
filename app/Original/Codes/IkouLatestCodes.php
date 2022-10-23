<?php

namespace App\Original\Codes;

use App\Lib\Codes\Code;

/**
 * 意向確認の最新意向を表すコード
 *
 * @author y_ohishi
 *
 */
class IkouLatestCodes extends Code {

    private $codes = [
        '11' => '自社車検',
        '12' => '他社車検',
        '13' => '自社代替',
        '14' => '他社代替',
        '15' => '廃車・転売',
        '16' => '転居予定',
        '17' => '拠点移管',
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

    public function getCode($name) {
        return array_search($name, $this->codes);
    }
}
