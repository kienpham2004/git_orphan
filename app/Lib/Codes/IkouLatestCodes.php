<?php

namespace App\Original\Codes;

use App\Lib\Codes\Code;

/**
 * 意向確認の最新意向を表すコード
 *
 * @author daidv
 *
 */
class IkouLatestCodes extends Code {
    private $codes = [
        '11' => '未確認',
        '12' => '自社車検',
        '13' => '自社代替',
        '14' => '他社車検',
        '15' => '他社代替',
        '16' => '廃車・転売・買取',
        '17' => '拠点移管',
        '18' => '転居（予定含む）',
        '19' => '自社車検（ピット管理入力済）',
        '20' => '自社代替（契約書発行済）',
        '21' => '他社車検（確定）',
        '22' => '他社代替（確定）'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

}
