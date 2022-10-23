<?php
namespace App\Commands\Graph;

use App\Lib\Util\DateUtil;
use App\Models\Graph\SyakenGraphDB;
use App\Commands\Command;
use App\Models\UserAccount;
use App\Original\Util\CodeUtil;

/**
 * 実際の表示を行う値を格納するクラス
 * (セルの事です。)
 */
class CellClass{

    /**
     * コンストラクタ
     * @param [type]  $values  [description]
     * @param boolean $isDummy [description]
     */
    public function __construct( $values, $isDummy=False ) {
        // ダミーかどうかのフラグ
        $this->isDummy = $isDummy;

        $this->tgc_customer_name_kanji = null;
        $this->tgc_car_name = null;
        $this->tgc_syaken_times = null;
        $this->tgc_syaken_next_date = null;
        $this->tgc_customer_kouryaku_flg = null;
        $this->tmr_status = null;
        $this->target_status = null;
        $this->tgc_status_update = null;
        // アラートメモ
        $this->tgc_alert_memo = null;
        // 査定データ
        $this->mi_smart_day = null;

        $this->target_id = null;
        $this->base_id = null;
        $this->base_code = null;
        $this->tgc_user_id = null;
        // チャオコース
        $this->tgc_ciao_course = null;
        $this->tgc_ciao_end_date = null;
        
        $this->umu_csv_flg = null;
        // クレジット
        $this->tgc_credit_hensaihouhou_name = null;
        $this->tgc_credit_manryo_date_ym = null;
        // 入庫日
        $this->mi_rstc_put_in_date = null;
        // 次回車検日
        $this->tgc_syaken_next_date = null;
        // 状況
        $this->mi_rstc_reserve_status = null;
        // 最終実績
        $this->mi_ik_final_achievement = null;
        // 作業進捗：納車済日時
        $this->mi_rstc_delivered_date = null;
        
        $this->mi_rcl_recall_flg = null;
        $this->mi_rstc_web_reserv_flg = null;
        $this->mi_ctc_seiyaku_flg = null;

        // 2022/02/17 add jisshi_flg
        $this->mi_shijo_6m1m = null;
        $this->mi_syodan_6m1m = null;
        $this->mi_satei_6m1m = null;
        $this->mi_dsya_syaken_jisshi_date = null;
        $this->jisshi_flg = null;

        // 値が空の時に動作
        if( empty( $this->isDummy ) == True ){
            // 引数で受け取った値をメンバ変数に格納
            $this->setVariables( $values );
        }
    }

    /**
     * 変数の初期化
     * @return [type] [description]
     */
    public function setVariables( $values ){
        // 配列が渡されることがあるのでキャスト
        if( is_array( $values ) == True ){
            $values = (object)( $values );
        }
        
        // 顧客名
        $this->tgc_customer_name_kanji = $values->tgc_customer_name_kanji;
        // 車種名
        $this->tgc_car_name = $values->tgc_car_name;
        // 車検回数
        $this->tgc_syaken_times = $values->tgc_syaken_times;
        // 次回車検日
        $this->tgc_syaken_next_date = $values->tgc_syaken_next_date;
        // 顧客名・攻略対象
        $this->tgc_customer_kouryaku_flg = $values->tgc_customer_kouryaku_flg;
        // tmr意向
        $this->tmr_status = $values->tmr_status;
        // 意向
        $this->target_status = $values->target_status;
        $this->tgc_status_update = $values->tgc_status_update;
        // アラートメモ
        $this->tgc_alert_memo = $values->tgc_alert_memo;
        // 査定日
        $this->mi_smart_day = $values->mi_smart_day;

        // target cars id
        $this->target_id = $values->target_id;
        // base id
        $this->base_id = $values->base_id;
        // base code
        $this->base_code = $values->base_code;
        // user id
        $this->tgc_user_id = $values->user_id;
        // チャオコース
        $this->tgc_ciao_course = $values->tgc_ciao_course;
        // チャオコース終期
        $this->tgc_ciao_end_date = $values->tgc_ciao_end_date;
        // umu_csv_flg
        $this->umu_csv_flg = $values->umu_csv_flg;
        // クレジット返済方法名称
        $this->tgc_credit_hensaihouhou_name = $values->tgc_credit_hensaihouhou_name;
        // クレジット契約満了日
        $this->tgc_credit_manryo_date_ym = $values->tgc_credit_manryo_date_ym;
        // 入庫日
        $this->mi_rstc_put_in_date = $values->mi_rstc_put_in_date;
        // 次回車検日
        $this->tgc_syaken_next_date = $values->tgc_syaken_next_date;
        // 状況
        $this->mi_rstc_reserve_status = $values->mi_rstc_reserve_status;
        // 最終実績
        $this->mi_ik_final_achievement = $values->mi_ik_final_achievement;
        // 作業進捗：納車済日時
        $this->mi_rstc_delivered_date = $values->mi_rstc_delivered_date;
        // リコール判定
        $this->mi_rcl_recall_flg = $values->mi_rcl_recall_flg;
        // web予約判定
        $this->mi_rstc_web_reserv_flg = $values->mi_rstc_web_reserv_flg;
        // 成約有無フラグ
        $this->mi_ctc_seiyaku_flg = $values->mi_ctc_seiyaku_flg;

        // 2022/02/17 add jisshi_flg
        $this->mi_shijo_6m1m = $values->mi_shijo_6m1m;
        $this->mi_syodan_6m1m = $values->mi_syodan_6m1m;
        $this->mi_satei_6m1m = $values->mi_satei_6m1m;
        $this->mi_dsya_syaken_jisshi_date = $values->mi_dsya_syaken_jisshi_date;
        $this->jisshi_flg = $values->jisshi_flg;
    }
    
}

/**
 * トレジャーボード（車検）のサマリーを取得するコマンド
 * @author yhatsutori
 */
class SyakenIkouCommand extends Command{

    /** 対象反映（月数） */
    const YM_TARGET = 6;
    
    /**
     * コンストラクタ
     * @param [type] $search     [description]
     * @param [type] $requestObj [description]
     */
    public function __construct( $search, $requestObj ) {
        $this->search = (object)$search;
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     */
    public function handle() {
        // 担当者毎のデータを格納する
        $treasureValues = collect();

        // トレジャーボード（車検）を取得する
        \DB::enableQueryLog();

        // 担当者に紐づくデータを取得
        $allCustomerValues = SyakenGraphDB::getSyakenIkou( $this->search );
        // 担当者に紐づくデータを取得
        $userSortValues = collect( $allCustomerValues )->groupBy( 'user_name' );
        // 退職者を取得する。
        $deletedAccountList =  UserAccount::staffOptionsDeleted( $this->search->base_code );

        // 表示形式は月別表示の場合
        if( !empty($this->search->display_flg) && $this->search->display_flg == '1') {
            //表示用の形式に整形
            $userSortValues = self::getMonthUserValues($userSortValues, $deletedAccountList);
            
            // 月別単位
            foreach ($userSortValues as $user_name => &$userValues) {
                // セルの位置をずらす
               $userValues = $this->exchangeCellValues($userValues);
                
                // 表示用オブジェクトに詰め込む
                $treasureValues->push($userValues);
            }
        } else { // 担当者別表示の場合
            $userValuesTmp = collect();// 退職者の一時保管
            // 担当者単位
            foreach ( $userSortValues as $user_name => $userValues ) {
                // 表示用オブジェクトをインスタンス化
                $userObj = app('stdClass');
                // 月単位の行
                $userObj->rows = collect();
                // 担当者の中での最大表示セル数
                $userObj->maxCols = 1;

                if( $this->search->inspection_ym_from ){
                    $search_ym = $this->search->inspection_ym_from;
                } else {
                    $search_ym = date('Ym');
                }

                // 退職者の場合
                if(CodeUtil::checkExistInList($deletedAccountList, $userValues[0]->user_id)) {
                    //データをまとめる
                    $userValuesTmp = $userValuesTmp->merge($userValues);
                }else{
                    // 担当者名
                    $userObj->user_name = $user_name;

                    // 行単位（月）のデータを生成する
                    $userObj = $this->getMonthValues( $userObj, $userValues, $search_ym );

                    // セルの位置をずらす
                    $userObj = $this->exchangeCellValues( $userObj );

                    // 表示用オブジェクトに担当者単位で詰め込む
                    $treasureValues->push( $userObj );
                }
            }
            // 退職者のデータを追加
            if(!$userValuesTmp->isEmpty()) {
                // 表示用オブジェクトに担当者単位で詰め込む
                $treasureValues->push( self::getAllDataOfDeleteAccount($userValuesTmp) );
            }
        }
        return $treasureValues;
    }

    /**
     * 各月毎のコレクションを取得する
     * @return [type] [description]
     */
    public function getMonthValues( $userObj, $userValues, $inspection_ym ){
        // 指定月分のデータを作る(縦軸のデータ)
        for( $num = 0; $num <= self::YM_TARGET; $num++ ){
            // 指定された未来年月日を取得 
            $targetYmd = DateUtil::monthLater( $inspection_ym . '01', $num );
            // 指定された未来年月を取得
            $targetYm = date( 'Ym', strtotime( $targetYmd ) );
            
            #########################
            ## 毎月のデータを格納するオブジェクト
            #########################
            
            // 表示用オブジェクトをインスタンス化
            $monthObj = app('stdClass');
            // 年月
            $monthObj->targetYm = $targetYm;
            // テンプレートで表示する月
            $monthObj->showDate = date( 'n月', strtotime( $targetYm . '01' ) );
            // セル
            $monthObj->cells = collect();

            #########################
            ## 毎月のデータを取得
            #########################

            // 車検月に並び替えを行った値を取得
            $inspectionValues = collect( $userValues )->groupBy( 'tgc_inspection_ym' ); // whereってないのかな？

            $cellCount = 0;
            // 車検月別の値をセルに追加
            foreach ( $inspectionValues as $key => $inspectionValues ) {
                // 行データとトレジャーボードの年月が一致したら
                if ( $monthObj->targetYm == $key ) {
                    // 同一のトレジャーボードのデータ（この変数名変えたほうがいいです、すいません）
                    foreach ( $inspectionValues as $value ) {
                        // 行にセルオブジェクトを追加する
                        $monthObj->cells->push( new CellClass( $value ) );
                        $cellCount = $cellCount + 1;
                    }
                }
            }

            $monthObj->cellCount = $cellCount;
            // 担当者単位で最大の表示セル数を取得
            // 現状以上（同じ場合も更新（空白分がふくまれているため））
            if( $userObj->maxCols <= count( $monthObj->cells ) ) {
                $userObj->maxCols = count( $monthObj->cells ) + 1;
            }

            // 年月を設定しコレクションに追加
            $userObj->rows->push( $monthObj );
        }

        return $userObj;
    }

    /**
     * セルの位置をずらす
     * @return [type] [description]
     */
    public function exchangeCellValues( $userObj ){
        // 担当者の表示用セルを生成する
        foreach ( $userObj->rows as $key => $monthObj ) {

            #########################
            ## ダミー用のセルを取得
            #########################

            // ダミーのセル
            $dummyNum = $userObj->maxCols - count( $monthObj->cells );
            // ダミー用のオブジェクトを格納する配列
            $dummyList = collect();
            // ダミーの数だけダミー用のオブジェクトを格納
            for( $i = 0; $i < $dummyNum; $i++ ) {
                // ダミーの値を格納
                $dummyList->push( new CellClass( [], True ) );
            }

            #########################
            ## ダミー用のセルを取得
            #########################

            $leftList = collect();
            $rightList = collect();

            // セルが右側か左側かを分けて格納する
            foreach ( $monthObj->cells as $cell ) {
                
                // 車両無判定用
                $umuFlg = ($cell->umu_csv_flg != '1');
                
                // 意向の判定用のフラグ
                $statusFlg = (
                    // 2022/01/28 update right to left
                    $cell->target_status == "14" ||
                    $cell->target_status == "15" ||
                    $cell->target_status == "16" ||
                    $cell->target_status == "17" ||
                    $cell->target_status == "103" ||
                    $cell->target_status == "102" ||
                    $cell->target_status == "101"
                );

                // ボードを右側に移動
                if( $umuFlg ){
                    $rightList->push( $cell );
                }
                elseif( $statusFlg ){
                    $rightList->push( $cell );
                }else{
                    // 左側にセルを追加
                    $leftList->push( $cell );
                }
            }
            
            // セルを一つにまとめる
            $cells1 = array_merge( $leftList->toArray(), $dummyList->toArray() );
            $cells2 = array_merge( $cells1, $rightList->toArray() );
            
            // 左,ダミー,右側のセルを格納する
            $userObj->rows[$key]->cells = $cells2;
            //$userObj->rows[$key]->cells = $unite_cells;
        }

        return $userObj;
    }
    
    /**
     * TMR意向と活動意向、活動実績の件数をカウント
     *
     * @return [type] [description]
     */
    public function countTmrTgcIkou($userObj){
        // 表示用オブジェクトをインスタンス化
        $ikou_count = app('stdClass');
        $ikou_count->recall[0] = 0;
        
        //担当者ごとにカウント
        foreach ($userObj->rows as $value) {
            foreach ($value->cells as $key => $val) {
                //TMR意向
                if(empty($ikou_count->tmr[$val->tmr_status])){
                    $ikou_count->tmr[$val->tmr_status] = 0;
                }
                
                $ikou_count->tmr[$val->tmr_status] += 1;
                
                //活動意向・活動実績
                if(empty($val->target_status)){
                    if(empty($ikou_count->tgc[0])){
                        $ikou_count->tgc[0] = 0;
                    }
                    $ikou_count->tgc[0] += 1;
                }else{
                    if(empty($ikou_count->tgc[$val->target_status])){
                        $ikou_count->tgc[$val->target_status] = 0;
                    }
                    $ikou_count->tgc[$val->target_status] += 1;
                }
                
                // リコール台数
                if(!empty($val->mi_rcl_recall_flg)){
                    $ikou_count->recall[0] += 1;
                }
            }
        }
        return $ikou_count;
    }
    
    /**
     * 月別->担当者別の順にデータ整形
     * @param object ユーザーのデータ
     * @param bool 退職者のフラグ
     * @return [type] [description]
     */
    public function getMonthUserValues($userSortValues,$deletedAccountArray){
        $cell_list = array();
        foreach ($userSortValues as $user_name => $value) {
            foreach ($value as $cell) {
                $data = app('stdClass');
//                $data->isQuitted = $user_name === 0;
                $data->cell = $cell;
                $cell_list[] = $data;
            }
        }
        
        $allObj = collect();
        
        // 指定月分のデータを作る(縦軸のデータ)
        for ($num = 0; $num <= self::YM_TARGET; $num ++) {
            $monthObj = app('stdClass');
            $userObj = array();
            $deletedUserObj = array();
            
            // 指定された未来年月日を取得
            $targetYmd = DateUtil::monthLater($this->search->inspection_ym_from . '01', $num);
            // 指定された未来年月を取得
            $targetYm = date('Ym', strtotime($targetYmd));
            
            // 年月
            $monthObj->targetYm = $targetYm;
            // テンプレートで表示する月
            $monthObj->showDate = date('n月', strtotime($targetYm . '01'));
            // セル
            $monthObj->rows = collect();
            //最大列数
            $monthObj->maxCols = 0;

            //セルの一覧から対象年月の対象担当者のものを抽出する
            foreach ($cell_list as $value) {
                if($targetYm == $value->cell->tgc_inspection_ym){
                    // 退職者を判断する
                    if(CodeUtil::checkExistInList($deletedAccountArray, $value->cell->user_id)) {
                        $user_name = '退職者';
                        if(empty($deletedUserObj[$user_name])){
                            $deletedUserObj[$user_name] = app('stdClass');
                        }
                        $deletedUserObj[$user_name]->cells[] = $value->cell;

                        if (isset($deletedUserObj[$user_name]->cellCount)) {
                            $deletedUserObj[$user_name]->cellCount = $deletedUserObj[$user_name]->cellCount + 1;
                        }else{
                            $deletedUserObj[$user_name]->cellCount = 1;
                        }

                    } else {
                        $user_name = $value->cell->user_name;
                        if(empty($userObj[$user_name])){
                            $userObj[$user_name] = app('stdClass');
                        }
                        $userObj[$user_name]->cells[] = $value->cell;

                        if (isset($userObj[$user_name]->cellCount)) {
                            $userObj[$user_name]->cellCount = $userObj[$user_name]->cellCount + 1;
                        }else{
                            $userObj[$user_name]->cellCount = 1;
                        }
                    }
                }
            }
            // 退職者は一番下を追加する。
            $userObj = array_merge($userObj,$deletedUserObj);

            //最大列数取得
            foreach($userObj as $user_val){
                if( count($user_val->cells) > $monthObj->maxCols){
                    $monthObj->maxCols = count($user_val->cells);
                }
            }
            $monthObj->rows = $userObj;
            $monthObj->ikou_count = self::countTmrTgcIkou($monthObj);
            $allObj->push($monthObj);
        }
        return $allObj;
    }

    /**
     * 退職者のデータを追加
     * @param $userValues
     * @return object
     */
    private function getAllDataOfDeleteAccount($userValues)
    {
        // 表示用オブジェクトをインスタンス化
        $userObj = app('stdClass');
        // 担当者名
        $userObj->user_name = "退職者";
        // 月単位の行
        $userObj->rows = collect();
        // 担当者の中での最大表示セル数
        $userObj->maxCols = 1;

        if( $this->search->inspection_ym_from ){
            $search_ym = $this->search->inspection_ym_from;
        } else {
            $search_ym = date('Ym');
        }

        // 行単位（月）のデータを生成する
        $userObj = $this->getMonthValues( $userObj, $userValues, $search_ym );
        // セルの位置をずらす
        $userObj = $this->exchangeCellValues( $userObj );

        return $userObj;
    }
}
