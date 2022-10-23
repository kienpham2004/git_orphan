<?php

namespace App\Original\Codes;

use App\Lib\Codes\Code;

/**
 * CSV取込ファイルを保持するコード
 *
 * @author yhatsutori
 *
 */
class CsvDirCodes extends Code {

    private $codes = [
        //1  => 'act_kihon',
        //2  => 'act_kobetsu',
        3  => 'customer',
        4  => 'tmr',
        5  => 'pit',
//        6  => 'abc',
//        7  => 'ciao',
        8  => 'contact',
        9  => 'smart_pro',
        // 10 => 'ikou',
        12 => 'recall',
        13 => 'htc',
        14 => 'syaken_jisshi',
        15 => 'daigae_syaken',
        20 => 'hoken'
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct( $this->codes );
    }
    
}
