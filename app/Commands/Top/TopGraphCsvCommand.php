<?php

namespace App\Commands\Top;

use App\Original\Util\CodeUtil;
use App\Models\Top\TopGraphDB;
use App\Commands\Top\TopGraphSumBaseCommand;
use App\Commands\Top\TopGraphSumUserCommand;
use App\Commands\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;
// 独自
use OhInspection;

/**
 * TOPページデータCSVダウンロード
 *
 * @author y_ohishi
 */
class TopGraphCsvCommand extends Command{
    
    use DispatchesCommands;

    /**
     * コンストラクタ
     * @param $requestObj 検索条件
     * @param [type] $filename 出力ファイル名
     */
    public function __construct( $requestObj, $filename="topGraph.csv" ){
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
            'target' => '対象台数',
            'target_none_count' => '車両無',
            'unconfirmed' => '未確定',
            'other' => 'その他',
            'tasya' => '他社車検',
            'jisya' => '自社車検',
            'jisya_rate' => '自社率',
            'syukka' => '出荷',
            'reserve' => '予約',
            'acquisition_rate' => '獲得率',
            'jissi_sessyoku' => '接触実施',
            'jissi_sessyoku_rate' => '接触実施率',
            'jissi_sessyoku_homon_raiten' => '接触実施(電話以外)',
            'jissi_sessyoku_homon_raiten_rate' => '接触実施率(電話以外)',
            'jissi_satei' => '査定',
            'jissi_satei_rate' => '査定実施率',
            'jissi_shijo' => '試乗',
            'jissi_shijo_rate' => '試乗実施率',
            'jissi_shodan' => '商談',
            'jissi_shodan_rate' => '商談実施率',
            'seiyaku' => '自社代替',
            'seiyaku_rate' => '自社代替成約率',
//            'ikou_tasya_rate' => '意向確認_他社',
//            'ikou_unconfirmed_rate' => '意向確認_未確認',
//            'achieve_syukka_rate' => '実績_出荷',
//            'achieve_reserve_rate' => '実績_予約'
            
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
            // 担当者別成績の取得
            $mainGraphData = $this->dispatch(new TopGraphSumUserCommand( $search, $requestObj ));
        }else{
            // 拠点成績の取得
            $mainGraphData = $this->dispatch(new TopGraphSumBaseCommand( $search, $requestObj ));
        }
        
        if ( empty( $mainGraphData ) ) {
            throw new \Exception('データが見つかりません');
        }

        // 検索結果をCSV出力ように変換
        $export = $this->convert( $mainGraphData );
        
        return OhInspection::download( $export, $this->headers, $this->filename );
    }

    /**
     * 出力形式に変換
     * @param $data
     * @return
     */
    private function convert( $data ){
        //変換格納用変数と列番号変数の初期化
        $export = null;
        $i = 0;
        
        //年月をキーにして1ヶ月のデータを取得しているのでループ
        foreach( $data->inspection_4 as $ym => $list ){
            //データがない場合は次へ
            if( $list == null ){
                continue;
            }
            //不要な項目は削除
            if(isset($list["isStaffListType"])) unset($list["isStaffListType"]);
            
            //１ヶ月内の１件ごとにデータを整形
            foreach( $list as $key => $value ){
                
                $export[$i]['inspection_ym'] = $ym;
                $export[$i]['inspection_id'] = "車検";
                
                $export[$i]['base_code'] = ( isset($value->base_code)) ? '\''.sprintf('%02s', $value->base_code) : "";
                $export[$i]['base_short_name'] = ( isset($value->base_short_name ) ) ? $value->base_short_name : "";
                $export[$i]['user_code'] = (isset($value->user_code )) ?  '\''.sprintf('%03s', $value->user_code ) : "";
                $export[$i]['user_name'] = (isset($value->user_name )) ? $value->user_name : "";

                if($export[$i]['user_name'] != "" && (isset($value->deleted_at) && $value->deleted_at != "")){
                    $export[$i]['user_name'] = $export[$i]['user_name']."(退職者)";
                }

                $export[$i]['target'] = $value->target_count;
                $export[$i]['target_none_count'] = $value->target_none_count;
                $export[$i]['unconfirmed'] = $value->unconfirmed_count;
                $export[$i]['other'] = $value->other_count;
                $export[$i]['tasya'] = $value->tasya_count;
                $export[$i]['jisya'] = $value->jisya_count;
                
                if($value->jisya_count != 0){
                    $export[$i]['jisya_rate'] = sprintf( '%.1f', (round( $value->jisya_count / $value->target_count * 100, 1 ))) . "%";
                }else{
                    $export[$i]['jisya_rate'] = "0.0%";
                }
                $export[$i]['syukka'] = $value->syukka_count;
                $export[$i]['reserve'] = $value->reserve_count;
                
                if( $value->reserve_count != 0 || $value->syukka_count != 0){
                    $export[$i]['acquisition_rate'] = sprintf( '%.1f', (round( ($value->reserve_count + $value->syukka_count) / $value->target_count * 100, 1 )))."%";
                }else{
                    $export[$i]['acquisition_rate'] = "0.0%";
                }
                
                $export[$i]['jissi_sessyoku'] = $value->jissi_sessyoku_count ;
                if( $value->jissi_sessyoku_count != 0){
                    $export[$i]['jissi_sessyoku_rate'] = sprintf( '%.1f', (round( $value->jissi_sessyoku_count / $value->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['jissi_sessyoku_rate'] = "0.0%";
                }
                $export[$i]['jissi_sessyoku_homon_raiten'] = $value->jissi_sessyoku_homon_raiten_count ;
                if( $value->jissi_sessyoku_homon_raiten_count != 0){
                    $export[$i]['jissi_sessyoku_homon_raiten_rate'] = sprintf( '%.1f', (round( $value->jissi_sessyoku_homon_raiten_count / $value->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['jissi_sessyoku_homon_raiten_rate'] = "0.0%";
                }
                
                $export[$i]['jissi_satei'] = $value->jissi_satei_count ;
                if( $value->jissi_satei_count != 0){
                    $export[$i]['jissi_satei_rate'] = sprintf( '%.1f', (round( $value->jissi_satei_count / $value->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['jissi_satei_rate'] = "0.0%";
                }
                
                $export[$i]['jissi_shijo'] = $value->jissi_shijo_count ;
                if( $value->jissi_shijo_count != 0){
                    $export[$i]['jissi_shijo_rate'] = sprintf( '%.1f', (round( $value->jissi_shijo_count / $value->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['jissi_shijo_rate'] = "0.0%";
                }
                
                $export[$i]['jissi_shodan'] = $value->jissi_shodan_count ;
                if( $value->jissi_shodan_count != 0){
                    $export[$i]['jissi_shodan_rate'] = sprintf( '%.1f', (round( $value->jissi_shodan_count / $value->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['jissi_shodan_rate'] = "0.0%";
                }
                
                $export[$i]['seiyaku'] = $value->seiyaku_count ;
                if( $value->seiyaku_count != 0){
                    $export[$i]['seiyaku_rate'] = sprintf( '%.1f', (round( $value->seiyaku_count / $value->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['seiyaku_rate'] = "0.0%";
                }
                
//                if( $value->tasya_count != 0){
//                    $export[$i]['ikou_tasya_rate'] = sprintf( '%.1f', (round( $value->tasya_count / $value->target_count * 100 , 1 )))."%";
//                }else{
//                    $export[$i]['ikou_tasya_rate'] = "0.0%";
//                }
//                
//                if( $value->unconfirmed_count != 0){
//                    $export[$i]['ikou_unconfirmed_rate'] = sprintf( '%.1f', (round( $value->unconfirmed_count / $value->target_count , 2) * 100))."%";
//                }else{
//                    $export[$i]['ikou_unconfirmed_rate'] = "0.0%";
//                }
//                
//                if( $value->syukka_count != 0){
//                    $export[$i]['achieve_syukka_rate'] = sprintf( '%.1f', (round( $value->syukka_count / $value->target_count , 2) * 100))."%";
//                }else{
//                    $export[$i]['achieve_syukka_rate'] = "0.0%";
//                }
//                
//                if( $value->reserve_count != 0){
//                    $export[$i]['achieve_reserve_rate'] = sprintf( '%.1f', (round( $value->reserve_count / $value->target_count , 2) * 100))."%";
//                }else{
//                    $export[$i]['achieve_reserve_rate'] = "0.0%";
//                }
                
                $i++;
            }
            
            //合計
            if( $list->total != null){
                $export[$i]['inspection_ym'] = "";
                $export[$i]['inspection_id'] = "";

                $export[$i]['base_code'] = "";
                $export[$i]['base_short_name'] = "";
                $export[$i]['user_id'] = "";
                $export[$i]['user_name'] = "合計";

                $export[$i]['target'] = $list->total->target_count ;
                $export[$i]['target_none_count'] = $list->total->target_none_count;
                $export[$i]['unconfirmed'] = $list->total->unconfirmed_count;
                $export[$i]['other'] = $list->total->other_count;
                $export[$i]['tasya'] = $list->total->tasya_count;
                
                $export[$i]['jisya'] = $list->total->jisya_count ;
                if($list->total->jisya_count != 0){
                    $export[$i]['jisya_rate'] = sprintf( '%.1f', (round( $list->total->jisya_count / $list->total->target_count * 100, 1 ))) . "%";
                }else{
                    $export[$i]['jisya_rate'] = "0.0%";
                }
                $export[$i]['syukka'] = $list->total->syukka_count;
                $export[$i]['reserve'] = $list->total->reserve_count;

                if( $list->total->reserve_count != 0 || $list->total->syukka_count != 0){
                    $export[$i]['acquisition_rate'] = sprintf( '%.1f', (round( ($list->total->reserve_count + $list->total->syukka_count) / $list->total->target_count * 100, 1 )))."%";
                }else{
                    $export[$i]['acquisition_rate'] = "0.0%";
                }
                
                
                $export[$i]['jissi_sessyoku'] = $list->total->jissi_sessyoku_count ;
                if( $list->total->jissi_sessyoku_count != 0){
                    $export[$i]['jissi_sessyoku_rate'] = sprintf( '%.1f', (round( $list->total->jissi_sessyoku_count / $list->total->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['jissi_sessyoku_rate'] = "0.0%";
                }
                $export[$i]['jissi_sessyoku_homon_raiten'] = $list->total->jissi_sessyoku_homon_raiten_count ;
                if( $list->total->jissi_sessyoku_homon_raiten_count != 0){
                    $export[$i]['jissi_sessyoku_homon_raiten_rate'] = sprintf( '%.1f', (round( $list->total->jissi_sessyoku_homon_raiten_count / $list->total->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['jissi_sessyoku_homon_raiten_rate'] = "0.0%";
                }
                
                $export[$i]['jissi_satei'] = $list->total->jissi_satei_count ;
                if( $list->total->jissi_satei_count != 0){
                    $export[$i]['jissi_satei_rate'] = sprintf( '%.1f', (round( $list->total->jissi_satei_count / $list->total->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['jissi_satei_rate'] = "0.0%";
                }
                
                $export[$i]['jissi_shijo'] = $list->total->jissi_shijo_count ;
                if( $list->total->jissi_shijo_count != 0){
                    $export[$i]['jissi_shijo_rate'] = sprintf( '%.1f', (round( $list->total->jissi_shijo_count / $list->total->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['jissi_shijo_rate'] = "0.0%";
                }
                
                $export[$i]['jissi_shodan'] = $list->total->jissi_shodan_count ;
                if( $list->total->jissi_shodan_count != 0){
                    $export[$i]['jissi_shodan_rate'] = sprintf( '%.1f', (round( $list->total->jissi_shodan_count / $list->total->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['jissi_shodan_rate'] = "0.0%";
                }
                
                $export[$i]['seiyaku'] = $list->total->seiyaku_count ;
                if( $list->total->seiyaku_count != 0){
                    $export[$i]['seiyaku_rate'] = sprintf( '%.1f', (round( $list->total->seiyaku_count / $list->total->target_count * 100 , 1 )))."%";
                }else{
                    $export[$i]['seiyaku_rate'] = "0.0%";
                }

                $i++;
            }
                
        }

        return $export;
    }

}
