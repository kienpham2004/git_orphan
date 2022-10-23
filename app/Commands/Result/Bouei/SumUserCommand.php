<?php

namespace App\Commands\Result\Bouei;

use App\Models\Result\Bouei\ResultDB;
use App\Commands\Command;

/**
 * 実績分析の拠点・担当者別実施率の"担当者別"の一覧を取得すすコマンド
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
//    public function __construct( $search, $requestObj, $inspectionType="all" ){
//        $this->search = (object)$search;
//        $this->requestObj = $requestObj;
//
//        // 対象月の1ヶ月先(先行実施台数対策)
//        $this->search->nextInspection_ym_from = date( 'Ym', strtotime( '+1 month', strtotime( $this->search->inspection_ym_from . "01" ) ) );
//
//        // 対象月の1ヶ月前(既に実施済台数対策)
//        $this->search->beforeInspection_ym_from = date( 'Ym', strtotime( '-1 month', strtotime( $this->search->inspection_ym_from . "01" ) ) );
//
//        //代替え台数取得条件の設定
//        $this->search->bouei_daigae = "";
//
//        //６ヶ月ロック対象フラグ
//        $this->search->six_rock_flg = False;
//
//        // 対象車点検区分
//        if( $inspectionType == "all" ){
//            $this->search->inspection_div = " tgc.tgc_inspection_id in ( '1', '2', '3', '4' ) AND ";
//
//        }else if( $inspectionType == "syaken" || $inspectionType == "syaken_bouei"){ // 車検の時の区分
//            $this->search->inspection_div = " tgc.tgc_inspection_id in ( '4' ) AND ";
//
//            $this->search->kouryaku_flg = True;
//
//            if($inspectionType == "syaken_bouei"){
//                //拠点・担当者別防衛率の場合の代替え台数取得条件対応
//                $this->search->bouei_daigae = "( to_char(tgc.tgc_status_update, 'yyyymm') >= to_char(".date( 'Ym', strtotime( '-6 month', strtotime( $this->search->inspection_ym_from . "01" ) ) ).", 'yyyymm') OR "
//                                            . " ( tgc.created_at <= '".config('original.daigae_six_month')."' ) ) AND ";
//
//                //６ヶ月ロック対象フラグ
//                $this->search->six_rock_flg = True;
//            }
//
//        }else if( $inspectionType == "tenken" ){ // 点検の時の区分
//            $this->search->inspection_div = " tgc.tgc_inspection_id in ( '1', '2', '3' ) AND ";
//
//            // 車点検区分が検索された時の動作
//            if( isset( $this->search->inspect_divs ) == True ){
//                // 検索された値を元に区分を取得
//                if( count( $this->search->inspect_divs ) > 0 && !empty( $this->search->inspect_divs[0] ) ) {
//                    $this->search->inspection_div = " in ('" . implode( "','", $this->search->inspect_divs ) . "')";
//                }
//            }
//
//        }else{
//            $this->search->inspection_div = "";
//        }
//
//        //翌月の年月をIN句で指定できる形にして取得
//        $yy = substr( $this->search->inspection_ym_from, 0, 4 );
//        $mm = substr( $this->search->inspection_ym_from, 4, 2 );
//        for($i=1;$i<=1;$i++){
//            //指定した年月の次の月を取得
//            $mm += $i;
//
//            if( $mm >= 13 ){
//                $yy++;
//                $mm = '1';
//            }
//
//            $this->search->next_syaken_ym = $yy . substr( '0'. $mm, -2 );
//        }
//
//    }
//
//    /**
//     * メインの処理
//     * @return [type] [description]
//     */
//    public function handle(){
//        //過去6ヶ月の年月をIN句で指定できる形にして取得
//        $yy = substr( $this->search->inspection_ym_from, 0, 4 );
//        $mm = substr( $this->search->inspection_ym_from, 4, 2 );
//        for($i=1;$i<=6;$i++){
//
//            if( $i !== 1 ){
//                $this->search->next_syaken_ym = $this->search->now_syaken_ym;
//            }
//            $this->search->now_syaken_ym = $yy . substr( '0'. $mm, -2 );
//            if( $mm <= 0 ){
//                $yy--;
//                $mm = '12';
//            }
//
//            if( $i === 1 ){ //当月データ取得
//                // 拠点別の集計データの取得
//                $showData = collect( ResultDB::summaryStaff( $this->search ) );
//
//                // 合計の値を取得する
//                $totalData = collect( ResultDB::summaryStaff( $this->search, "total" ) )[0];
//
//            }else{ //当月以降のデータ取得＋マージ
//
//                // 拠点別の集計データの取得
//                $tmpData = collect( ResultDB::summaryStaff( $this->search ) );
//
//                // 合計の値を取得する
//                $tmpTotalData = collect( ResultDB::summaryStaff( $this->search, "total" ) )[0];
//
//                //拠点別集計データの各項目を加算
//                foreach( $tmpData as $num => $data ){
//                    $add_flg = false;
//                    foreach( $showData as $baseNum => $baseData){
//                        //各拠点ごとに値を加算
//                        if( $baseData->user_name == $data->user_name ){
//                            foreach($baseData as $key => $val){
//                                //数値を加算(数値以外の項目は除く)
//                                if( array_search($key, array('user_name','base_code','base_short_name','tgc_car_name') ) == true ){
//                                    continue;
//                                }else if( isset($baseData->{$key}) && isset($data->{$key}) ){
//                                    //取得した値を加算
//                                    $showData[$baseNum]->{$key} += $data->{$key};
//                                }else if( !isset($baseData->{$key}) ){
//                                    //存在しない項目は追加
//                                    $showData[$baseNum]->{$key} = $data->{$key};
//                                }
//                                $showData[$baseNum]->user_name = $data->user_name;
//                            }
//
//                            $add_flg = true;
//                        }
//                    }
//                    //対応する拠点がない場合追加用配列に追加
//                    if($add_flg == false){
//                        $addDate[] = $data;
//                    }
//                }
//
//                //片方に存在しない拠点データを追加
//                if( isset($addDate) && !empty($addDate)){
//                    array_push($showData, $addDate);
//                }
//
//                //トータルデータの合計
//                foreach ($tmpTotalData as $key => $val){
//                    //数値を加算(数値以外の項目は除く)
//                    if(array_search($key, ['user_name','base_code','base_short_name','tgc_car_name']) ){
//                        continue;
//                    }else if( isset($tmpTotalData->{$key}) && isset($totalData->{$key}) ){
//                        //取得した値を加算
//                        $totalData->{$key} += $tmpTotalData->{$key};
//                    }else if( !isset($baseData->{$key}) ){
//                        //存在しない項目は追加
//                        $totalData->{$key} = $tmpTotalData->{$key};
//                    }
//                }
//
//            }
//            $totalData->user_name = "合計";
//            //指定した年月の前月を取得
//            $mm -= 1;
//        }
//
//        // コレクションの先頭に値を追加
//        $showData->prepend( $totalData );
//
//        return $showData;
//    }
}
