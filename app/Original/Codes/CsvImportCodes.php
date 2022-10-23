<?php

namespace App\Original\Codes;

use App\Lib\Codes\Code;

/**
 * CSV取込ファイルをを表すコード
 *
 * @author yhatsutori
 *
 */
class CsvImportCodes extends Code {

    private $codes = [
        //1  => '活動データ（基本）',
        //2  => '活動データ（個別）',
        3  => '顧客データ',
        4  => 'TMR 架電結果リスト',
        5  => 'PIT管理作業明細',
//        6  => 'ABCデータ',
//        7  => 'チャオデータ',
        8  => '活動日報実績',
        9  => 'スマートプロ査定',
        // 10 => '意向確認',
        12 => 'リコール',
        13 => 'HTCログイン',
        14 => '車検実施リスト',
        15 => '代替車検推進管理',
        20 => '保険データ'
//        11  => '04_見込客管理情報',
//        12  => '市場措置未実施リスト',
//        13  => '代替SYS用顧客情報',
//        14  => '販売予定＆下取り予定'
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct( $this->codes );
    }
    
}
