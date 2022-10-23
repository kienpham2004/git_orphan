<?php

namespace App\Commands\Hoken\Result;

use App\Models\Hoken\HokenResultDB;
use App\Commands\Command;
use App\Commands\Hoken\Result\SumBaseCommand;

/**
 * 実績分析の拠点・担当者別実施率の"担当者別"の一覧を取得すすコマンド
 *
 * @author yhatsutori
 */
class SumUserCommand extends Command{

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
        // 担当者別の集計データの取得
        $showData = collect( HokenResultDB::summaryStaff( $this->search ) );

        // 合計の値を取得する
        // $totalData = collect( HokenResultDB::summaryStaff( $this->search, "total" ) )[0];
        if (!$showData->isEmpty()){
            // コレクションの最後に値を追加
            $showData->push(SumBaseCommand::getTotal($showData));
            $showData->isStaffListType = true;
        }

        // 拠点の値を取得
        $plan_value = HokenResultDB::summaryStaffPlan( $this->search )[0]->plan_value;
        
        return array( $showData, $plan_value );
    }
}
