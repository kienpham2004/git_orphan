<?php

namespace App\Commands\Result;

use App\Models\Result\ResultDB;
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
    public function __construct( $search, $requestObj, $inspectionType="all" ){
        $this->search = (object)$search;
        $this->requestObj = $requestObj;
        
        // 対象月の1ヶ月先(先行実施台数対策)
        $this->search->nextInspection_ym_from = date( 'Ym', strtotime( '+1 month', strtotime( $this->search->inspection_ym_from . "01" ) ) );
        
        // 対象月の1ヶ月前(既に実施済台数対策)
        $this->search->beforeInspection_ym_from = date( 'Ym', strtotime( '-1 month', strtotime( $this->search->inspection_ym_from . "01" ) ) );
        
        // 代替え台数取得条件の設定
        $this->search->bouei_daigae = "";
        
        // ６ヶ月ロック対象フラグ
        $this->search->six_rock_flg = False;
        
        // 攻略対象車を除く
        $this->search->kouryaku_flg = True;

        $this->search->inspection_type = $inspectionType;
        
        // 対象車点検区分
        if( $inspectionType == "all" ){
            $this->search->inspection_div = " tgc.tgc_inspection_id in ( 1, 2, 3, 4 ) AND ";

        }else if( $inspectionType == "syaken" || $inspectionType == "syaken_bouei"){ // 車検の時の区分
            $this->search->inspection_div = " tgc.tgc_inspection_id = 4  AND ";

            //６ヶ月ロック対象フラグ
            $this->search->six_rock_flg = True;
            
            if($inspectionType == "syaken_bouei"){
                //拠点・担当者別防衛率の場合の代替え台数取得条件対応フラグ
//                $this->search->bouei_daigae = "( to_char(tgc.tgc_status_update, 'yyyymm') >= to_char(".date( 'Ym', strtotime( '-6 month', strtotime( $this->search->inspection_ym_from . "01" ) ) ).", 'yyyymm') OR "
//                                            . " ( tgc.created_at <= '".config('original.daigae_six_month')."' ) ) AND ";
                $this->search->bouei_daigae = " tgc_lock_flg6 = 0 AND ";
                //６ヶ月ロック対象フラグ
                $this->search->six_rock_flg = True;
            }

        }else if( $inspectionType == "tenken" ){ // 点検の時の区分
            $this->search->inspection_div = " tgc.tgc_inspection_id in ( 1, 2, 3 ) AND ";
            //2021/03/21 add tgc.tgc_customer_kouryaku_flg <> '1' OR tgc.tgc_customer_kouryaku_flg IS NULL
            $this->search->inspection_div .= " ( tgc.tgc_customer_kouryaku_flg <> '1' OR tgc.tgc_customer_kouryaku_flg IS NULL ) AND ";

            // 車点検区分が検索された時の動作    
            if( isset( $this->search->inspect_divs ) == True ){
                // 検索された値を元に区分を取得
                if( count( $this->search->inspect_divs ) > 0 && !empty( $this->search->inspect_divs[0] ) ) {
                    $this->search->inspection_div = " tgc.tgc_inspection_id in ('" . implode( "','", $this->search->inspect_divs ) . "') AND ";
                }
            }
            
        }else{
            $this->search->inspection_div = "";
        }
        
        //翌月の年月をIN句で指定できる形にして取得
//        $next_syaken_ym = array();
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
//            $next_syaken_ym[] = $yy . substr( '0'. $mm, -2 );
//        }
//        $this->search->next_syaken_ym = "('" . implode( "','", $next_syaken_ym ) . "')";
        $this->search->next_syaken_ym = "'". CodeUtil::getNextYearMonth($this->search->inspection_ym_from,1,2)."'";
        
    }
    
    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){        
        // 拠点別の集計データの取得
        $showData = collect( ResultDB::summaryBase( $this->search ) );
        
        // 合計の値を取得する
        //$totalData = collect( ResultDB::summaryBase( $this->search, "total" ) )[0];
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
            'base_code' => '',
            'base_short_name' => '合計',
            'tgc_car_name' => '合計'
        ];
        return CodeUtil::getTotalOfAllRecord($dataList,$totalData);
    }
}
