<?php

namespace App\Lib\Codes;

/**
 * 行数を表すコード
 *
 * @author yhatsutori
 *
 */
class RowNumCodes extends Code {

    private $codes = [
        '5'=>'5',
        '10'=>'10',
        '20' => '20',
        '100'=>'100',
        '1000' => '1000'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

    public static function getDefault() {
        return '20';
    }
}
