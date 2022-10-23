<?php

namespace App\Commands\Result\Status;

use App\Models\Result\StatusDB;
use App\Commands\Command;
use App\Commands\Result\Status\SumBaseCommand;

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
    public function __construct( $search, $requestObj ){
        $this->search = (object)$search;
        $this->requestObj = $requestObj;
        
        // 指定の内時は空を指定
        $this->search->inspection_div = "";

        // 車点検区分が検索された時の動作    
        if( isset( $this->search->inspect_divs ) == True ){
            // 検索された値を元に区分を取得
            if( count( $this->search->inspect_divs ) > 0 && !empty( $this->search->inspect_divs[0] ) ) {
                $this->search->inspection_div = "tgc.tgc_inspection_id in ('" . implode( "','", $this->search->inspect_divs ) . "') AND ";
            }
        }
    }
    
    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 担当者別の集計データの取得
        $showData = collect( StatusDB::summaryStaff( $this->search ) );

        // 合計の値を取得する
        // $totalData = collect( StatusDB::summaryStaff( $this->search, "total" ) )[0];
        if (!$showData->isEmpty()){
            $showData->isStaffListType = true;
            // コレクションの最後に値を追加
            $showData->push( SumBaseCommand::getTotal($showData) );
        }

        return $showData;
    }
}
