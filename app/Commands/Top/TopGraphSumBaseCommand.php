<?php

namespace App\Commands\Top;

use App\Models\Top\TopGraphDB;
use App\Commands\Top\SumCommand;
use App\Http\Requests\SearchRequest;
use App\Commands\Command;
use App\Original\Util\CodeUtil;

/**
 * 拠点単位で車点検関連の集計を行うコマンド
 * @author yhatsutori
 */
class TopGraphSumBaseCommand extends Command{
    
    /**
     * コンストラクタ
     * @param SearchRequest $requestObj リクエスト
     */
    public function __construct( $search, SearchRequest $requestObj ){
        $this->search = (object)$search;
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // TOPのグラフ部分のインスタンス
        $graphData = collect();
        
        // 活動リストの一覧を取得
        $inspectionCodeList = [
            "inspection_4" => 4
//            "inspection_1" => 1,
//            "inspection_2" => 2,
//            "inspection_3" => 3
        ];
        
        //取得年月
        //表示する月数をconfig/original.phpから取得(デフォルトは3)
        $m_count = config('original.top_get_month_count', 3);
        $inspection_ym = array();
        //指定した年月の次の月を取得
        $yy = substr( $this->search->inspection_ym_from, 0, 4 );
        $mm = substr( $this->search->inspection_ym_from, 4, 2 );
        for($i=0;$i<$m_count;$i++){

            if( $mm >= 13 ){
                $yy++;
                $mm = '1';
            }

            $inspection_ym[] = $yy . substr( '0'. $mm, -2 );
            $mm += 1;
        }

        // 活動項目の該当する値を取得
        foreach( $inspectionCodeList as $name => $inspection_id ){
            $search_tmp = $this->search;
            foreach ($inspection_ym as $ym){
                // 活動項目を検索条件に追加
                $search_tmp->inspection_id = $inspection_id;
                
                //検索条件の取得年月を変更
                $search_tmp->inspection_ym_from = $ym;

//                // 拠点単位での合計を取得する
//                $total = collect( TopGraphDB::totalInspection( $search_tmp ) )[0];
//
//                // 対象件数が０の場合は詳細なサマリーは取得しない
//                if($total->target_count == 0) {
//                    $graphData->{$name}[$ym] = null;
//
//                } else {
//                    // サマリーを取得する
//                    $graphData->{$name}[$ym] = collect( TopGraphDB::summaryBase( $search_tmp ) );
//                    // 拠点と担当者を表示するかどうかのフラグ
//                    $graphData->{$name}[$ym]->isStaffListType = false;
//
//                    $graphData->{$name}[$ym]->total = $total;
//                }
                // サマリーを取得する
                $graphData->{$name}[$ym] = collect( TopGraphDB::summaryBase( $search_tmp ) );
                // 拠点と担当者を表示するかどうかのフラグ
                $graphData->{$name}[$ym]->isStaffListType = false;

                // 拠点単位での合計を取得する
                if (!$graphData->{$name}[$ym]->isEmpty()){
                    $graphData->{$name}[$ym]->total = $this->getTotal($graphData->{$name}[$ym]);
                } else {
                    $graphData->{$name}[$ym]->total = null;
                }
            }
        }

        return $graphData;
    }

    /**
     * 合計の値を取得する
     * @param $dataList
     */
    public static function getTotal($dataList) {
        $totalData =[];
        return CodeUtil::getTotalOfAllRecord($dataList,$totalData);
    }
}
