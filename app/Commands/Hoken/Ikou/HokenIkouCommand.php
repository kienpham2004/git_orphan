<?php

namespace App\Commands\Hoken\Ikou;

use App\Lib\Util\DateUtil;
use App\Original\Codes\Intent\IntentSyatenkenCodes;
use App\Models\Hoken\HokenGraphDB;
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

        $this->insu_customer_name = null;
        $this->insu_car_base_number = null;
        $this->insu_inspection_make_ym = null;
        $this->insu_status = null;
        $this->insu_jisya_tasya = null;
        $this->insu_add_tetsuduki_date = null;
        $this->insu_status_gensen = null;
        $this->target_id = null;
        //$this->insu_company_name = null;
        //$this->insu_syaryo_type = null;
        //$this->insu_daisya = null;
        $this->insu_pair_fleet = null;
        $this->insu_alert_memo = null;

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
        $this->insu_customer_name = $values->insu_customer_name;
        // 任意保険終期
        $this->insu_insurance_end_date = $values->insu_insurance_end_date;
        // 意向
        $this->insu_status = $values->insu_status;
        // 自社・他社
        $this->insu_jisya_tasya = $values->insu_jisya_tasya;
        // 手続き完了日
        $this->insu_add_tetsuduki_date = $values->insu_add_tetsuduki_date;
        // 獲得源泉
        $this->insu_status_gensen = $values->insu_status_gensen;
        
        // 任意保険会社コード名称
        //$this->insu_company_name = $values->insu_company_name;
        // 車両
        //$this->insu_syaryo_type = $values->insu_syaryo_type;
        // 代車
        //$this->insu_daisya = $values->insu_daisya;
        // target cars id
        $this->target_id = $values->target_id;
        // ペアフリート
        $this->insu_pair_fleet = $values->insu_pair_fleet;
        // 伝達事項
        $this->insu_alert_memo = $values->insu_alert_memo;

    }
    
}

/**
 * トレジャーボード（車検）のサマリーを取得するコマンド
 * @author yhatsutori
 */
class HokenIkouCommand extends Command{

    /** 対象反映（月数） */
    const YM_TARGET = 3;
    
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
        $allCustomerValues = HokenGraphDB::getHokenIkou( $this->search );
        // 担当者に紐づくデータを取得
        $userSortValues = collect( $allCustomerValues )->groupBy( 'user_name' );
        // 退職者を取得する。
        $deletedAccountList =  UserAccount::staffOptionsDeleted( $this->search->base_code );

        $userValuesTmp = collect();// 退職者の一時保管
        // 担当者単位
        foreach ( $userSortValues as $user_name => $userValues ) {
            // 表示用オブジェクトをインスタンス化
            $userObj = app('stdClass');
            // 担当者名
            $userObj->user_name = $user_name;
            // 月単位の行
            $userObj->rows = collect();
            // 担当者の中での最大表示セル数
            $userObj->maxCols = 1;
            // 拠点コード
            $userObj->base_id = $userValues[0]->base_id;
            // 拠点コード
            $userObj->base_code = $userValues[0]->base_code;
            // 担当者コード            
            $userObj->user_id = $userValues[0]->user_id;

            // 退職者の場合
            if(CodeUtil::checkExistInList($deletedAccountList, $userValues[0]->user_id)) {
                //データをまとめる
                $userValuesTmp = $userValuesTmp->merge($userValues);
            }else {
                // 行単位（月）のデータを生成する
                $userObj = $this->getMonthValues($userObj, $userValues, $this->search->inspection_ym_from);
                // セルの位置をずらす
                $userObj = $this->exchangeCellValues($userObj);

                // 表示用オブジェクトに担当者単位で詰め込む
                $treasureValues->push($userObj);
            }
        }
        // 退職者のデータを追加
        if(!$userValuesTmp->isEmpty()) {
            // 表示用オブジェクトに担当者単位で詰め込む
            $treasureValues->push( self::getAllDataOfDeleteAccount($userValuesTmp) );
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
            
            // 表示するお客様の対象月が、指定された検索月の時に2回、処理を行う
            if( $targetYm == $inspection_ym ){
                
                ###########################
                ## 当月の時の純新規を取得
                ###########################
                
                // 月事の対象のお客様のデータを取得する
                $monthObj = $this->getMonthObj( $targetYm, $userValues, "純新規" );

                // 担当者単位で最大の表示セル数を取得
                // 現状以上（同じ場合も更新（空白分がふくまれているため））
                if( $userObj->maxCols <= count( $monthObj->cells ) ) {
                    $userObj->maxCols = count( $monthObj->cells ) + 1;
                }

                // 年月を設定しコレクションに追加
                $userObj->rows->push( $monthObj );

                ###########################
                ## 当月の時の他社分を取得
                ###########################

                // 月事の対象のお客様のデータを取得する
                $monthObj = $this->getMonthObj( $targetYm, $userValues, "他社分" );

                // 担当者単位で最大の表示セル数を取得
                // 現状以上（同じ場合も更新（空白分がふくまれているため））
                if( $userObj->maxCols <= count( $monthObj->cells ) ) {
                    $userObj->maxCols = count( $monthObj->cells ) + 1;
                }

                // 年月を設定しコレクションに追加
                $userObj->rows->push( $monthObj );

            }else{
                // 月事の対象のお客様のデータを取得する
                $monthObj = $this->getMonthObj( $targetYm, $userValues, "" );

                // 担当者単位で最大の表示セル数を取得
                // 現状以上（同じ場合も更新（空白分がふくまれているため））
                if( $userObj->maxCols <= count( $monthObj->cells ) ) {
                    $userObj->maxCols = count( $monthObj->cells ) + 1;
                }

                // 年月を設定しコレクションに追加
                $userObj->rows->push( $monthObj );

            }
        }

        return $userObj;
    }

    /**
     * 月事の対象のお客様のデータを取得する
     * @param  [type] $targetYm   [description]
     * @param  [type] $userValues [description]
     * @return [type]             [description]
     */
    public function getMonthObj( $targetYm, $userValues, $showFlg="" ){

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
        $inspectionValues = collect( $userValues )->groupBy( 'insu_inspection_make_ym' ); // whereってないのかな？

        $cellCount = 0;
        // 車検月別の値をセルに追加
        foreach ( $inspectionValues as $key => $inspectionValues ) {
            // 行データとトレジャーボードの年月が一致したら
            if ( $monthObj->targetYm == $key ) {
                // 同一のトレジャーボードのデータ（この変数名変えたほうがいいです、すいません）
                foreach ( $inspectionValues as $value ) {
                    if( empty( $showFlg ) == True ){
                        // 行にセルオブジェクトを追加する
                        $monthObj->cells->push( new CellClass( $value ) );

                    }else if( $showFlg == "純新規" ){
                        if( $value->insu_jisya_tasya == "純新規" ){
                            // 行にセルオブジェクトを追加する
                            $monthObj->cells->push( new CellClass( $value ) );
                        }
                        
                    }else if( $showFlg == "他社分" ){
                        if( $value->insu_jisya_tasya == "他社分" ){
                            // 行にセルオブジェクトを追加する
                            $monthObj->cells->push( new CellClass( $value ) );
                        }
                    }
                    $cellCount = $cellCount + 1;
                }
            }
        }
        // 件数設定
        $monthObj->cellCount = $cellCount;

        ######################
        ## 獲得数の値を格納
        ######################

        $monthObj->kakutoku = 0;
        $monthObj->kakutokuTasya = 0;
        $monthObj->kakutokuTsuika = 0;
        
        // 獲得数を取得
        foreach ( $monthObj->cells as $key => $value ) {
            // 獲得の時(他社分)
            if( $value->insu_status == 1 && $value->insu_jisya_tasya == "他社分" ){
                $monthObj->kakutokuTasya = $monthObj->kakutokuTasya + 1;
            }
            // 獲得の時(純新規)
            if( $value->insu_status == 1 && $value->insu_jisya_tasya == "純新規" ){
                $monthObj->kakutokuTsuika = $monthObj->kakutokuTsuika + 1;
            }
        }
        // 獲得の総数を取得
        $monthObj->kakutoku = $monthObj->kakutokuTasya + $monthObj->kakutokuTsuika;

        return $monthObj;
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
                // 意向の判定用のフラグ
                $statusFlg = (
                    $cell->insu_status == "7" ||
                    //$cell->insu_status == "6" ||
                    $cell->insu_status == "99" ||
                    $cell->insu_status == "100"
                );
                
                // 敗戦、獲得済は右表示、それ以外は左表示
                if( $statusFlg ){
                    // 右側にセルを追加
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
        }
        return $userObj;
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
        // 拠点Id
        $userObj->base_id = $userValues[0]->base_id;
        // 拠点コード
        $userObj->base_code = $userValues[0]->base_code;
        // 担当者コード
        $userObj->user_id = $userValues[0]->user_id;

        // 行単位（月）のデータを生成する
        $userObj = $this->getMonthValues($userObj, $userValues, $this->search->inspection_ym_from);
        // セルの位置をずらす
        $userObj = $this->exchangeCellValues($userObj);

        return $userObj;
    }
}
