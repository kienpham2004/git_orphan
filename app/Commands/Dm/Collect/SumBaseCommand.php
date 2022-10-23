<?php

namespace App\Commands\Dm\Collect;

use App\Models\Dm\CollectDB;
use App\Commands\Command;

/**
 * DMの拠点明細の"拠点"ごとの一覧を取得するコマンド
 *
 * @author yhatsutori
 */
class SumBaseCommand extends Command{

//    /**
//     * コンストラクタ
//     * @param [type] $search     [description]
//     * @param [type] $requestObj リクエストオブジェクト
//     */
//    public function __construct( $search, $requestObj ){
//        $this->search = (object)$search;
//        $this->requestObj = $requestObj;
//
//        // 対象月（他ページでは「XXXX年XX月＋中旬」という表示）
//        $this->search->inspection_ym_from = date( 'Ym', strtotime( '+1 month', strtotime( $this->search->inspection_ym_from . "01" ) ) );
//    }
//
//    /**
//     * メインの処理
//     * @return [type] [description]
//     */
//    public function handle(){
//        // 拠点別の集計データの取得
//        $showData = collect( CollectDB::summaryBase( $this->search ) );
//
//        // 合計の値を取得する
//        $totalData = collect( CollectDB::summaryBase( $this->search, "total" ) )[0];
//
//        // コレクションの最後に値を追加
//        $showData->push( $totalData );
//
//        return $showData;
//    }
    
}
