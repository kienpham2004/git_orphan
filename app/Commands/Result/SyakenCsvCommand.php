<?php

namespace App\Commands\Result;

use App\Original\Util\CodeUtil;

use App\Commands\Command;
use App\Commands\Result\SumBaseCommand;
use App\Commands\Result\SumUserCommand;
use Illuminate\Foundation\Bus\DispatchesCommands;
// 独自
use OhInspection;

/**
 * 実績分析(車検)データCSVダウンロード
 *
 * @author y_ohishi
 */
class SyakenCsvCommand extends Command{
    
    use DispatchesCommands;

    /**
     * コンストラクタ
     * @param $requestObj 検索条件
     * @param [type] $filename 出力ファイル名
     */
    public function __construct( $requestObj, $filename="resultSyaken.csv" ){
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
            //'tb_target_cars.id' => 'id',
            'inspection_ym' => '車検月',
            'inspection_id' => '車検区分',
            'base_code' => '拠点コード',
            'base_short_name' => '拠点',
            'user_code' => '担当者コード',
            'user_name' => '担当者',
            //vuongdm update field
//            'plan_data' => '車検計画',
//            'target_count' => '対象台数',
//            'daigae_count' => '当月代替台数',
//            'tougetu_jissi_count' => '当月実施',
//            'tougetu_jissi_machi_count' => '（当月未実施）',
//            'senkou_jissi_count' => '期日先行実施',
//            'tougetu_count' => '当月対象計',
//            'all_jisshi_count' => '総実施数',
//            'car_inspection_rate' => '車検実施率',
//            'plan_implement_rate' => '計画実施率',
//            'togetu_htc_number_count' => '加入台数',
//            'togetu_mi_htc_login_flg_count' => 'ログイン済台数',
//            'togetu_mi_htc_login_flg_rate' => '初回ログイン率',

            'plan_data' => '車検計画',
            'tougetu_jissi_count'         => '当月対象当月実施',
            'senkou_jissi_count'          => '先行実施',
            'tougetu_keika_jissi_count'   => '経過実施',
            'all_jisshi_count'            => '総実施数',
            'plan_implement_rate'         => '計画達成率',
            'target_count'                => '当月対象(車検台数)',
            'daigae_count'                => '当月対象代替',
            'tougetu_taisyo_jissi_count'  => '当月対象実施',
            'tougetu_taisyo_jissi_rate'   => '当月対象実施率',
            'borei_rate'                  => '防衛率',
            'togetu_recall_target_count'  => '市場措置対象台数',
            'togetu_recall_rate'          => '実施率',
            'togetu_htc_number_count' => 'HTC加入台数',
            'togetu_mi_htc_login_flg_count' => 'ログイン済台数',
            'togetu_mi_htc_login_flg_rate' => '初回ログイン率'
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
            $syakenData = $this->dispatch(new SumUserCommand($search, $requestObj, "syaken"));
        }else{
            //拠点別データ取得
            $syakenData = $this->dispatch(new SumBaseCommand($search, $requestObj, "syaken"));
        }
        
        if ( empty( $syakenData ) ) {
            throw new \Exception('データが見つかりません');
        }

        // カラムとヘッダーの値を取得
        $csvParams = $this->getCsvParams();
        if ($syakenData->isStaffListType == false){
            unset($csvParams['user_code']);
            unset($csvParams['user_name']);
        }
        $this->headers = array_values( $csvParams );

        // 検索結果をCSV出力ように変換
        $export = $this->convert( $syakenData, $search['inspection_ym_from'] );
        
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
            $export[$key]['inspection_id'] = "車検";

            $export[$key]['base_code'] = ( isset($value->base_code)) ? '\''.sprintf('%02s', $value->base_code) : "";
            $export[$key]['base_short_name'] = ( isset($value->base_short_name ) ) ? $value->base_short_name : "";
            if ($data->isStaffListType) {
                $export[$key]['user_code'] = (isset($value->user_code)) ? '\'' . sprintf('%03s', $value->user_code) : "";
                $export[$key]['user_name'] = (isset($value->user_name)) ? $value->user_name : "";
            }

            // 合計行の場合
            if ($value->user_name == "合計"){
                $export[$key]['inspection_ym'] = "";
                $export[$key]['inspection_id'] = "";
                $export[$key]['base_code'] = "";
                if ($data->isStaffListType) {
                    $export[$key]['base_short_name'] = "";
                    $export[$key]['user_code'] = "";
                }
            }
            
            //2022/03/25 edit field CSV
//            $export[$key]['plan_data'] = $value->plan_data;
//            $export[$key]['target_count'] = $value->target_count;
//            $export[$key]['daigae_count'] = $value->daigae_count;
//            $export[$key]['tougetu_jissi_count'] = $value->tougetu_jissi_count;
//            $export[$key]['tougetu_jissi_machi_count'] = $value->tougetu_jissi_machi_count;
//            $export[$key]['senkou_jissi_count'] = $value->senkou_jissi_count;
//            $export[$key]['tougetu_count'] = $value->target_count - $value->daigae_count + $value->senkou_jissi_count;
//            $export[$key]['all_jisshi_count'] = $value->senkou_jissi_count + $value->tougetu_jissi_count;
//
//            if( $export[$key]['tougetu_count'] != 0 && $export[$key]['all_jisshi_count'] != 0){
//                $export[$key]['car_inspection_rate'] = sprintf( '%.1f', (round( $export[$key]['all_jisshi_count'] / $export[$key]['tougetu_count'] * 100, 1 )))."%";
//            }else{
//                $export[$key]['car_inspection_rate'] = "0.0%";
//            }
//
//            if( $export[$key]['all_jisshi_count'] != 0 && $value->plan_data != 0 ){
//                $export[$key]['plan_implement_rate'] = sprintf( '%.1f', (round( $export[$key]['all_jisshi_count'] / $value->plan_data * 100, 1)))."%";
//            }else{
//                $export[$key]['plan_implement_rate'] = "0.0%";
//            }
//
//            $export[$key]['togetu_htc_number_count'] = $value->togetu_htc_number_count;
//            $export[$key]['togetu_mi_htc_login_flg_count'] = $value->togetu_mi_htc_login_flg_count;
//            if( $value->togetu_mi_htc_login_flg_count != 0 && $value->target_count != 0 ){
//                $export[$key]['togetu_mi_htc_login_flg_rate'] = sprintf( '%.1f', (round( $value->togetu_mi_htc_login_flg_count / $value->target_count * 100, 1)))."%";
//            }else{
//                $export[$key]['togetu_mi_htc_login_flg_rate'] = "0.0%";
//            }
//        }

            $export[$key]['plan_data']                  = isset($value->plan_data) ? $value->plan_data : 0;
            $export[$key]['tougetu_jissi_count']        = $value->tougetu_jissi_count . '(' . (!empty($value->tougetu_jissi_machi_count) ? $value->tougetu_jissi_machi_count : '') . ')';
            $export[$key]['senkou_jissi_count']         = empty($value->senkou_jissi_count) ? 0 : $value->senkou_jissi_count;
            $export[$key]['tougetu_keika_jissi_count']  = empty($value->tougetu_keika_jissi_count) ? 0 : $value->tougetu_keika_jissi_count;
            $num = $value->senkou_jissi_count + $value->tougetu_jissi_count + $value->tougetu_keika_jissi_count;
            $export[$key]['all_jisshi_count']           = empty($num) ? 0 : $num;
            $export[$key]['plan_implement_rate']        = !empty($value->plan_data) ? sprintf( '%.1f', (round( $num / $value->plan_data * 100, 1 ))).'%' : '0.0%';
            $export[$key]['target_count']               = empty($value->target_count) ? 0 : $value->target_count;
            $export[$key]['daigae_count']               = empty($value->daigae_count) ? 0 : $value->daigae_count;
            $export[$key]['tougetu_taisyo_jissi_count'] = empty($value->tougetu_taisyo_jissi_count) ? 0 : $value->tougetu_taisyo_jissi_count;
            $targetRate = '0.0%';
            if( !empty( $value->target_count ) && !empty( $value->tougetu_taisyo_jissi_count )) {
                $targetRate = sprintf( '%.1f', (round( $value->tougetu_taisyo_jissi_count / $value->target_count * 100, 1 ))).'%';
            }
            $export[$key]['tougetu_taisyo_jissi_rate']  = $targetRate;
            $targetSum = $value->daigae_count + $value->tougetu_taisyo_jissi_count;
            $targetSumRate = '0.0%';
            if( !empty( $targetSum ) && !empty( $value->target_count ) ) {
                $targetSumRate = sprintf( '%.1f', (round( $targetSum / $value->target_count * 100, 1 ))).'%';
            }
            $export[$key]['borei_rate']                 = $targetSumRate;
            $export[$key]['togetu_recall_target_count'] = empty($value->togetu_recall_target_count) ? 0 : $value->togetu_recall_target_count;
            $recallRate = '0.0%';
            if( !empty( $value->togetu_recall_jisshi_count ) && !empty( $value->togetu_recall_target_count ) ) {
                $recallRate = sprintf( '%.1f', (round( $value->togetu_recall_jisshi_count / $value->togetu_recall_target_count * 100, 1 ))).'%';
            }
            $export[$key]['togetu_recall_rate']         = $recallRate;
            $export[$key]['togetu_htc_number_count'] = $value->togetu_htc_number_count;
            $export[$key]['togetu_mi_htc_login_flg_count'] = $value->togetu_mi_htc_login_flg_count;
            if( $value->togetu_mi_htc_login_flg_count != 0 && $value->target_count != 0 ){
                $export[$key]['togetu_mi_htc_login_flg_rate'] = sprintf( '%.1f', (round( $value->togetu_mi_htc_login_flg_count / $value->target_count * 100, 1)))."%";
            }else{
                $export[$key]['togetu_mi_htc_login_flg_rate'] = "0.0%";
            }

        }

        return $export;
    }

}
