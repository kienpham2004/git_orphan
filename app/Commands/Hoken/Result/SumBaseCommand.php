<?php

namespace App\Commands\Hoken\Result;

use App\Models\Hoken\HokenResultDB;
use App\Commands\Command;
use App\Original\Util\CodeUtil;

/**
 * 実績分析の拠点・担当者別実施率の"拠点"ごとの一覧を取得するコマンド
 *
 * @author yhatsutori
 */
class SumBaseCommand extends Command{

    /**
     * コンストラクタ
     * @param [type] $search     [description]
     * @param [type] $requestObj リクエストオブジェクト
     */
    public function __construct( $search, $requestObj, $insu_jisya_tasya="jisya" ){
        $this->search = (object)$search;
        $this->requestObj = $requestObj;
        $this->search->insu_jisya_tasya = $insu_jisya_tasya;
    }
    
    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){        
        // 拠点別の集計データの取得
        $showData = collect( HokenResultDB::summaryBase( $this->search ) );
        
        // 合計の値を取得する
        //$totalData = collect( HokenResultDB::summaryBase( $this->search, "total" ) )[0];
        if (!$showData->isEmpty()){
            // コレクションの最後に値を追加
            $showData->push($this->getTotal($showData));
            $showData->isStaffListType = false;
        }

        // 拠点の値を取得
        $plan_value = HokenResultDB::summaryStaffPlan( $this->search )[0]->plan_value;

        return array( $showData, $plan_value );
    }

    /**
     * 合計の値を取得する
     * @param $dataList
     */
    public static function getTotal($dataList) {
        $totalData =[
            'user_name' => '合計',
            'base_code' => '',
            'base_short_name' => '合計'
        ];
        return CodeUtil::getTotalOfAllRecord($dataList,$totalData);
    }
}
