<?php

namespace App\Commands\Result\Status;

use App\Models\Result\StatusDB;
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
        // 拠点別の集計データの取得
        $showData = collect( StatusDB::summaryBase( $this->search ) );
        
        // 合計の値を取得する
        //$totalData = collect( StatusDB::summaryBase( $this->search, "total" ) )[0];
        if (!$showData->isEmpty()){
            $showData->isStaffListType = false;
            // コレクションの最後に値を追加
            $showData->push( $this->getTotal($showData) );
        }

        return $showData;
    }

    /**
     * 合計の値を取得する
     * @param $dataList
     */
    public static function getTotal($dataList) {
        $totalData =[
            'user_name' => '合計',
            'user_code' => '',
            'base_code' => '',
            'base_short_name' => '合計'
        ];
        return CodeUtil::getTotalOfAllRecord($dataList,$totalData);
    }
}
