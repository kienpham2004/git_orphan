<?php

namespace App\Original\Codes\Insurance;

use App\Lib\Codes\Code;

/**
 * 活動意向（保険）を表すコード
 *
 * @author yhatsutori
 *
 */
class InsuStatusCodes extends Code {
    
    private $codes = [
        // 他社分・追加分
        /*
        // もともと
        '6' => '獲得済',
        '4' => '見込度：90%',
        '3' => '見込度：70%',
        '2' => '見込度：50%',
        '1' => '見込度：30%',
        '5' => '敗戦',
        */
        
        '6' => '獲得済',
        '4' => '確約',
        '3' => '獲得予定',
        '2' => '提案中',
        '1' => 'キビしい',
        '5' => '敗戦',
        '99' => '未接触敗戦',

        // 自社分
        '21' => '更新済（同条件）',
        '22' => '更新済（変更あり）',
        '23' => '更新予定',
        '24' => '未継続見込',
        '25' => '未継続確定',
        '26' => '長期の確認',
        
        // 共通
        '100' => '対象外',
    ];
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct($this->codes);
    }
    
}
