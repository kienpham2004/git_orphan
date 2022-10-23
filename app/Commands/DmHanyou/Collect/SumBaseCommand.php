<?php

namespace App\Commands\DmHanyou\Collect;

use App\Models\DmHanyou\CollectDB;
use App\Commands\Command;

/**
 * DMの拠点明細の"拠点"ごとの一覧を取得するコマンド
 *
 * @author yhatsutori
 */
class SumBaseCommand extends Command{
//
//    /**
//     * コンストラクタ
//     * @param [type] $search     [description]
//     * @param [type] $requestObj リクエストオブジェクト
//     */
//    public function __construct( $search, $requestObj ){
//        $this->search = (object)$search;
//        $this->requestObj = $requestObj;
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
//        // コレクションの先頭に値を追加
//        $showData->prepend( $totalData );
//
//        return $showData;
//    }
//
}
