<?php
//
//namespace App\Commands\Graph;
//
//use App\Lib\Util\DateUtil;
//use App\Original\Codes\Intent\IntentSyatenkenCodes;
//use App\Models\Graph\HokenGraphDB;
//use App\Commands\Command;
//
///**
// * 実際の表示を行う値を格納するクラス
// * (セルの事です。)
// */
//class CellClass{
//
//    /**
//     * コンストラクタ
//     * @param [type]  $values  [description]
//     * @param boolean $isDummy [description]
//     */
//    public function __construct( $values, $isDummy=False ) {
//        // ダミーかどうかのフラグ
//        $this->isDummy = $isDummy;
//
//        $this->insu_customer_name_kanji = null;
//        $this->insu_car_name = null;
//        $this->insu_car_base_number = null;
//        $this->insu_inspection_ym = null;
//        $this->insu_customer_kouryaku_flg = null;
//        $this->insu_status = null;
//        $this->target_id = null;
//
//        // 値が空の時に動作
//        if( empty( $this->isDummy ) == True ){
//            // 引数で受け取った値をメンバ変数に格納
//            $this->setVariables( $values );
//        }
//    }
//
//    /**
//     * 変数の初期化
//     * @return [type] [description]
//     */
//    public function setVariables( $values ){
//        // 配列が渡されることがあるのでキャスト
//        if( is_array( $values ) == True ){
//            $values = (object)( $values );
//        }
//
//        // 顧客名
//        $this->insu_customer_name_kanji = $values->insu_customer_name_kanji;
//        // 車種名
//        $this->insu_car_name = $values->insu_car_name;
//        // 車両No
//        $this->insu_car_base_number = $values->insu_car_base_number;
//        // 任意保険加入区分名称
//        $this->insu_customer_insurance_type = $values->insu_customer_insurance_type;
//        // 任意保険会社コード名称
//        $this->insu_customer_insurance_company = $values->insu_customer_insurance_company;
//        // 任意保険終期
//        $this->insu_customer_insurance_end_date = $values->insu_customer_insurance_end_date;
//        // 攻略対象
//        $this->insu_customer_kouryaku_flg = $values->insu_customer_kouryaku_flg;
//        // 意向
//        $this->insu_status = $values->insu_status;
//        // target cars id
//        $this->target_id = $values->target_id;
//    }
//
//}
//
///**
// * トレジャーボード（車検）のサマリーを取得するコマンド
// * @author yhatsutori
// */
//class HokenIkouCommand extends Command{
//
//    /** 対象反映（月数） */
//    const YM_TARGET = 6;
//
//    /**
//     * コンストラクタ
//     * @param [type] $search     [description]
//     * @param [type] $requestObj [description]
//     */
//    public function __construct( $search, $requestObj ) {
//        $this->search = (object)$search;
//        $this->requestObj = $requestObj;
//    }
//
//    /**
//     * メインの処理
//     */
//    public function handle() {
//        // 担当者毎のデータを格納する
//        $treasureValues = collect();
//
//        // トレジャーボード（車検）を取得する
//        \DB::enableQueryLog();
//
//        // 担当者に紐づくデータを取得
//        $allCustomerValues = HokenGraphDB::getHokenIkou( $this->search );
//
//        // 担当者に紐づくデータを取得
//        $userSortValues = collect( $allCustomerValues )->groupBy( 'user_name' );
//
//        // 担当者単位
//        foreach ( $userSortValues as $user_name => $userValues ) {
//            // 表示用オブジェクトをインスタンス化
//            $userObj = app('stdClass');
//            // 担当者名
//            $userObj->user_name = $user_name;
//            // 月単位の行
//            $userObj->rows = collect();
//            // 担当者の中での最大表示セル数
//            $userObj->maxCols = 1;
//
//            // 行単位（月）のデータを生成する
//            $userObj = $this->getMonthValues( $userObj, $userValues, $this->search->inspection_ym_from );
//
//            // セルの位置をずらす
//            $userObj = $this->exchangeCellValues( $userObj );
//
//            // 表示用オブジェクトに担当者単位で詰め込む
//            $treasureValues->push( $userObj );
//        }
//
//        return $treasureValues;
//    }
//
//    /**
//     * 各月毎のコレクションを取得する
//     * @return [type] [description]
//     */
//    public function getMonthValues( $userObj, $userValues, $inspection_ym ){
//        // 指定月分のデータを作る(縦軸のデータ)
//        for( $num = 0; $num <= self::YM_TARGET; $num++ ){
//            // 指定された未来年月日を取得
//            $targetYmd = DateUtil::monthLater( $inspection_ym . '01', $num );
//            // 指定された未来年月を取得
//            $targetYm = date( 'Ym', strtotime( $targetYmd ) );
//
//            #########################
//            ## 毎月のデータを格納するオブジェクト
//            #########################
//
//            // 表示用オブジェクトをインスタンス化
//            $monthObj = app('stdClass');
//            // 年月
//            $monthObj->targetYm = $targetYm;
//            // テンプレートで表示する月
//            $monthObj->showDate = date( 'n月', strtotime( $targetYm . '01' ) );
//            // セル
//            $monthObj->cells = collect();
//
//            #########################
//            ## 毎月のデータを取得
//            #########################
//
//            // 車検月に並び替えを行った値を取得
//            $inspectionValues = collect( $userValues )->groupBy( 'insu_inspection_ym' ); // whereってないのかな？
//
//            // 車検月別の値をセルに追加
//            foreach ( $inspectionValues as $key => $inspectionValues ) {
//                // 行データとトレジャーボードの年月が一致したら
//                if ( $monthObj->targetYm == $key ) {
//                    // 同一のトレジャーボードのデータ（この変数名変えたほうがいいです、すいません）
//                    foreach ( $inspectionValues as $value ) {
//                        // 行にセルオブジェクトを追加する
//                        $monthObj->cells->push( new CellClass( $value ) );
//                    }
//                }
//            }
//
//            // 担当者単位で最大の表示セル数を取得
//            // 現状以上（同じ場合も更新（空白分がふくまれているため））
//            if( $userObj->maxCols <= count( $monthObj->cells ) ) {
//                $userObj->maxCols = count( $monthObj->cells ) + 1;
//            }
//
//            // 年月を設定しコレクションに追加
//            $userObj->rows->push( $monthObj );
//        }
//
//        return $userObj;
//    }
//
//    /**
//     * セルの位置をずらす
//     * @return [type] [description]
//     */
//    public function exchangeCellValues( $userObj ){
//        // 担当者の表示用セルを生成する
//        foreach ( $userObj->rows as $key => $monthObj ) {
//
//            #########################
//            ## ダミー用のセルを取得
//            #########################
//
//            // ダミーのセル
//            $dummyNum = $userObj->maxCols - count( $monthObj->cells );
//            // ダミー用のオブジェクトを格納する配列
//            $dummyList = collect();
//            // ダミーの数だけダミー用のオブジェクトを格納
//            for( $i = 0; $i < $dummyNum; $i++ ) {
//                // ダミーの値を格納
//                $dummyList->push( new CellClass( [], True ) );
//            }
//
//            #########################
//            ## ダミー用のセルを取得
//            #########################
//
//            $leftList = collect();
//            $rightList = collect();
//
//            // セルが右側か左側かを分けて格納する
//            foreach ( $monthObj->cells as $cell ) {
//                // 意向の判定用のフラグ
//                $statusFlg = (
//                    $cell->insu_status == "12" ||
//                    $cell->insu_status == "16"
//                );
//
//                // 未連絡、他社代替と他社予約済は左表示用へ、それ以外は右表示用へ
//                if( $statusFlg ){
//                    // 右側にセルを追加
//                    $rightList->push( $cell );
//
//                }else{
//                    // 左側にセルを追加
//                    $leftList->push( $cell );
//
//                }
//            }
//
//            // セルを一つにまとめる
//            $cells1 = array_merge( $leftList->toArray(), $dummyList->toArray() );
//            $cells2 = array_merge( $cells1, $rightList->toArray() );
//
//            // 左,ダミー,右側のセルを格納する
//            $userObj->rows[$key]->cells = $cells2;
//        }
//
//        return $userObj;
//    }
//
//}
