<?php

namespace App\Original\Codes;

use App\Lib\Codes\Code;

/**
 * CSV取込ファイルをを表すコード
 *
 * @author yhatsutori
 *
 */
class CsvJudgeCodes extends Code {

    private $codes = [
        //1  => '活動データ（基本）',
        //2  => '活動データ（個別）',
        3  => '顧客データ',
        4  => '架電',
        5  => '作業明細',
        6  => 'ABC',
        7  => 'パック',
        8  => '活動日報',
        9  => 'スマートプロ',
        10 => '意向確認'
//        11  => '見込客',
//        12  => '未実施',
//        13  => '顧客情報',
//        14  => '下取'
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct( $this->codes );
    }
    
}
