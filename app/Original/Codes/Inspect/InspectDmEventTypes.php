<?php

namespace App\Original\Codes\Inspect;

use App\Lib\Codes\Code;

/**
 * 車点検区分を表すコード(DM用)
 *
 * @author yhatsutori
 *
 */
class InspectDmEventTypes extends Code {
    
    const INSPECT_M6 = '1';
    const INSPECT_AK1 = '21';
    const INSPECT_H12 = '3';
    const INSPECT_AK2 = '22';
    
    private $codes = [
        //'1' => '無料６ヶ月',
        '21' => '安心快適',
        '3' => '法定１２ヶ月',
        '22' => '車検６ヶ月前',
        '4' => '車検',
        '6' => '対象外'
    ];
    
    public function __construct() {
        parent::__construct($this->codes);
    }
        
}
