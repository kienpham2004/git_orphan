<?php

namespace App\Commands\DmHanyou\Collect;

use App\Models\DmHanyou\CollectDB;
use App\Commands\Command;

/**
 * DMの拠点明細の"担当者別"の一覧を取得すすコマンド
 *
 * @author yhatsutori
 */
class SumUserCommand extends Command{
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
//        // 担当者別の集計データの取得
//        $showData = collect( CollectDB::summaryStaff( $this->search ) );
//
//        // 合計の値を取得する
//        $totalData = collect( CollectDB::summaryStaff( $this->search, "total" ) )[0];
//
//        // コレクションの先頭に値を追加
//        $showData->prepend( $totalData );
//
//        return $showData;
//    }
//
}
