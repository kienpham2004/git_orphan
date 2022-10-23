<?php

namespace App\Commands\Dm\Collect;

use App\Original\Util\CodeUtil;
use App\Models\Dm\CollectDB;
use App\Commands\Command;
// 独自
use OhInspection;

/**
 * 取り込みデータの実績CSVダウンロード
 *
 * @author yhatsutori
 */
class SumBaseCsvCommand extends Command{

//    /**
//     * コンストラクタ
//     * @param array $search 並び順
//     * @param $requestObj 検索条件
//     * @param [type] $filename 出力ファイル名
//     */
//    public function __construct( $search, $requestObj, $filename="target.csv" ){
//        $this->search = (object)$search;
//        $this->requestObj = $requestObj;
//        $this->filename = $filename;
//
//        // 点検の対象月
//        $this->search->inspection_ym_from = date( 'Ym', strtotime( '+1 month', strtotime( $this->search->inspection_ym_from . "01" ) ) );
//
//        // ヘッダーを取得
//        $this->headers = [
//            "拠点",
//            "発送月",
//            "無料6ヶ月",
//            //"無料6ヶ月(DM不要)",
//            "安心快適",
//            //"安心快適(DM不要)",
//            "法定12ヶ月",
//            //"法定12ヶ月(DM不要)",
//            "車検6ヶ月前",
//            //"車検6ヶ月前(DM不要)",
//            "早期入庫",
//            "総数",
//            //"不要総数"
//        ];
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
//        $showData->push( $totalData );
//
//        if ( empty( $showData ) ) {
//            throw new \Exception('データが見つかりません');
//        }
//
//        // 検索結果をCSV出力ように変換
//        $export = $this->convert( $showData, $this->search->inspection_ym_from );
//
//        return OhInspection::download( $export, $this->headers, $this->filename );
//    }
//
//    /**
//     * 出力形式に変換
//     * @param $data
//     * @return
//     */
//    private function convert( $data, $inspection_ym_from ){
//        $export = null;
//
//        foreach( $data as $key => $value ){
//            if( $value->user_name == "合計" ){
//                $value->base_short_name = "合計";
//            }
//            $export[$key]['base_short_name'] = $value->base_short_name;
//            $export[$key]['inspection_ym_from'] = date( "Y年m月", strtotime( "- 1 month" , strtotime( $inspection_ym_from . "01" ) ) );
//            $export[$key]['muryo_count'] = $value->muryo_count;
//            //$export[$key]['muryo_not_count'] = $value->muryo_not_count;
//            $export[$key]['anshin_count'] = $value->anshin_count;
//            //$export[$key]['anshin_not_count'] = $value->anshin_not_count;
//            $export[$key]['houtei_count'] = $value->houtei_count;
//            //$export[$key]['houtei_not_count'] = $value->houtei_not_count;
//            $export[$key]['syaken_count'] = $value->syaken_count;
//            //$export[$key]['syaken_not_count'] = $value->syaken_not_count;
//            $export[$key]['syaken_souki_nyuko_count'] = $value->syaken_souki_nyuko_count;
//            $export[$key]['total_count'] = $value->total_count;
//            //$export[$key]['total_not_count'] = $value->total_not_count;
//        }
//
//        return $export;
//    }

}
