<?php

namespace App\Commands\Result;

use App\Original\Util\CodeUtil;

use App\Commands\Command;
use App\Commands\Result\Status\SumBaseCommand;
use App\Commands\Result\Status\SumUserCommand;
use Illuminate\Foundation\Bus\DispatchesCommands;
// 独自
use OhInspection;

/**
 * 意向結果CSVダウンロード
 *
 * @author y_ohishi
 */
class StatusCsvCommand extends Command{
    
    use DispatchesCommands;

    /**
     * コンストラクタ
     * @param $requestObj 検索条件
     * @param [type] $filename 出力ファイル名
     */
    public function __construct( $requestObj, $filename="resultStatus.csv" ){
        $this->requestObj = $requestObj;
        $this->filename = $filename;

        // カラムとヘッダーの値を取得
        $csvParams = $this->getCsvParams();
        // カラムを取得
        $this->columns = array_keys( $csvParams );
        // ヘッダーを取得
        $this->headers = array_values( $csvParams );
    }

    /**
     * カラムとヘッダーの値を取得
     * @return array
     */
    private function getCsvParams(){
        return [
            'inspection_ym' => '車点検月',
            'base_code' => '拠点コード',
            'base_short_name' => '拠点',
            'user_code' => '担当者コード',
            'user_name' => '担当者',
            'target_count' => '対象台数',
            'mikakutei_count' => '未確定',
            'intention1_count' => '自社車検',
            'intention2_count' => '他社車検',
            'intention3_count' => '自社代替',
            'intention4_count' => '他社代替',
            'intention5_count' => '廃車・転売',
            'intention6_count' => '拠点移管',
            'intention7_count' => '転居予定'
        ];
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 表示も問題で一度変数に格納
        $requestObj = $this->requestObj;
        
        //検索条件の取得
        $search = $this->requestObj->all();
        
        // 担当者の時は担当者別の集計表示
        if( isset($search['top_staffList_flag']) ){
            //担当者別データ取得
            $statusData = $this->dispatch(new SumUserCommand($search, $requestObj));
        }else{
            //拠点別データ取得
            $statusData = $this->dispatch(new SumBaseCommand($search, $requestObj));
        }
        
        if ( empty( $statusData ) ) {
            throw new \Exception('データが見つかりません');
        }

        // カラムとヘッダーの値を取得
        $csvParams = $this->getCsvParams();
        if ($statusData->isStaffListType == false){
            unset($csvParams['user_code']);
            unset($csvParams['user_name']);
        }
        $this->headers = array_values( $csvParams );

        // 検索結果をCSV出力ように変換
        $export = $this->convert( $statusData, $search['inspection_ym_from'] );
        
        return OhInspection::download( $export, $this->headers, $this->filename );
    }

    /**
     * 出力形式に変換
     * @param $data
     * @return
     */
    private function convert( $data, $ym ){
        //変換格納用変数と列番号変数の初期化
        $export = null;

        //１件ごとにデータを整形
        foreach( $data as $key => $value ){

            $export[$key]['inspection_ym'] = $ym;
            $export[$key]['base_code'] = ( isset($value->base_code)) ? '\''.sprintf('%02s', $value->base_code) : "";
            $export[$key]['base_short_name'] = ( isset($value->base_short_name ) ) ? $value->base_short_name : "";
            if ($data->isStaffListType) {
                $export[$key]['user_code'] = (isset($value->user_code)) ? '\'' . sprintf('%03s', $value->user_code) : "";
                $export[$key]['user_name'] = (isset($value->user_name)) ? $value->user_name : "";
            }

            // 合計行の場合
            if ($value->user_name == "合計"){
                $export[$key]['inspection_ym'] = "";
                $export[$key]['base_code'] = "";
                if ($data->isStaffListType) {
                    $export[$key]['base_short_name'] = "";
                    $export[$key]['user_code'] = "";
                }
            }

            $export[$key]['target_count'] = $value->target_count;
            $export[$key]['mikakutei_count'] = $value->mikakutei_count;
            $export[$key]['intention1_count'] = $value->intention1_count;
            $export[$key]['intention2_count'] = $value->intention2_count;
            $export[$key]['intention3_count'] = $value->intention3_count;
            $export[$key]['intention4_count'] = $value->intention4_count;
            $export[$key]['intention5_count'] = $value->intention5_count;
//            2022/04/05 update`
            $export[$key]['intention7_count'] = $value->intention7_count;
            $export[$key]['intention6_count'] = $value->intention6_count;


        }

        return $export;
    }

}
