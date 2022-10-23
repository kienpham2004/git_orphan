<?php

namespace App\Lib\Codes;

/**
 * 表示/削除を表すコード
 *
 * @author yhatsutori
 *
 */
class DeleteCodes extends Code {

    private $codes = [
        '1' => '表示',
        '2' => '削除'
    ];

    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }

    public static function isDelete($value) {
        return $value == '2';
    }

}
