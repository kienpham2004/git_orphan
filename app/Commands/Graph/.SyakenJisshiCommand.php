<?php
//
//namespace App\Commands\Graph;
//
//use App\Models\Graph\SyakenGraphDB;
//use App\Commands\Command;
//
///**
// * トレジャーボード（車検状況）のサマリーを取得するコマンド
// *
// * @author yhatsutori
// */
//class SyakenJisshiCommand extends Command{
//
//    /**
//     * コンストラクタ
//     * @param [type] $search     [description]
//     * @param [type] $requestObj リクエスト
//     */
//    public function __construct( $search, $requestObj ) {
//        $this->search = (object)$search;
//        $this->requestObj = $requestObj;
//    }
//
//    /**
//     * メインの処理
//     * @return
//     */
//    public function handle() {
//
//        ###################
//        ## 表示する値を格納したオブジェクト
//        ###################
//
//        $showObj = app('stdClass');
//        // 表示を行う担当者一覧を取得
//        $showObj->staffNames = array();
//        // 実際に表示する値を格納する配列(表のx軸 y軸を入れ替える)
//        $showObj->showTableValues = array();
//        // 高さ
//        $showObj->ycount = array();
//        $showObj->ycountTotal = 0;
//
//        // 表示する値を取得
//        list( $showObj->staffNames, $tableValues ) = $this->getMainTableValues( $this->search );
//
//        ###################
//        ## 表示する値の整形
//        ###################
//
//        // 横・縦の幅数を取得
//        $x_sum = count( $showObj->staffNames );
//
//        if( !empty( $x_sum ) ){
//            // 不要な値を削除
//            $tableValues[0] = array_filter( $tableValues[0] );
//
//            for( $x_key = 0; $x_key < $x_sum; $x_key++ ){
//                foreach( $tableValues[0] as $y_key => $y_value ){
//                    // 値が空の時
//                    if( !is_null( $tableValues[$x_key][$y_key] ) ){
//                        if( !isset( $showObj->ycount[$x_key] ) == true ){
//                            $showObj->ycount[$x_key] = array(
//                                "sum" => 0
//                            );
//                        }
//                        $showObj->ycount[$x_key]["sum"]++;
//                    }
//
//                    // 表のx軸 y軸を入れ替える
//                    $showObj->showTableValues[$y_key][$x_key] = $tableValues[$x_key][$y_key];
//                }
//            }
//        }
//
//        // 総数を取得
//        foreach ( $showObj->ycount as $key => $value ) {
//            $showObj->ycountTotal += intval( $value["sum"] );
//        }
//
//        return $showObj;
//    }
//
//    /**
//     * 画面表示のメインとなる項目の値を取得
//     * @return [type] [description]
//     */
//    public function getMainTableValues( $search ){
//        // 自車検に該当する担当者を取得
//        $userList = SyakenGraphDB::getSyakenJisshiUsers( $search->inspection_ym_from, $search->base_code );
//
//        ###########################
//        ## 担当者名だけを取得
//        ###########################
//
//        // 担当者を格納する配列
//        $staffNames = array();
//
//        // 担当者名の格納
//        foreach( $userList as $key => $value ){
//            $staffNames[$key] = $value->tgc_user_name;
//        }
//
//        ###########################
//        ## 担当者のデータを取得
//        ###########################
//
//        // 表の配列
//        $tableValues  = array();
//
//        // 最も多い顧客総数を格納
//        $maxCustomerSum = 0;
//
//        foreach( $userList as $x_key => $value ){
//            if( $maxCustomerSum < $value->kensu ){
//                $maxCustomerSum = $value->kensu;
//            }
//        }
//
//        /*
//        back_num
//        5 -- 当月実施
//        4 -- 先行実施
//        3 -- 当月予約
//        2 -- 先行予約
//        1 -- 車点検意向有
//        */
//        // 担当者別にデータを取得
//        foreach( $userList as $x_key => $value ){
//            /*
//            // 先頭の担当者（最も件数の多い）の件数は保持しておく
//            if( $x_key == 0 ){
//                $maxCustomerSum = $value->kensu;
//                d($maxCustomerSum);
//            }
//            */
//
//            // データの取得
//            $tableMotoValues = SyakenGraphDB::getSyakenJisshi( $search->inspection_ym_from, $value->tgc_user_id );
//
//            // 担当者の抱える顧客総数を格納
//            $userCustomerSum = 0;
//
//            // 担当下のデータをHTMLテーブル出力用の配列に格納する。
//            foreach( $tableMotoValues as $y_key => $y_value ){
//                $tableValues[$x_key][$y_key] = array(
//                    "id"                        => $y_value->id,                        //対象車ID
//                    "tgc_customer_name_kanji"   => $y_value->tgc_customer_name_kanji,   //顧客名
//                    "tgc_car_model"             => $y_value->tgc_car_model,             //型式
//                    "tgc_car_name"              => $y_value->tgc_car_name,              //車名
//                    "tgc_car_frame_number"      => $y_value->tgc_car_frame_number,      //フレームナンバー
//
//                    "tgc_inspection_id"         => $y_value->tgc_inspection_id,         //車点検区分
//                    "tgc_inspection_ym"         => $y_value->tgc_inspection_ym,         //対象月
//                    "tgc_car_manage_number"     => $y_value->tgc_car_manage_number,     //車両統合管理No
//
//                    "back_num"                  => $y_value->back_num                   //背景の色
//                );
//
//                $userCustomerSum++;
//            }
//
//            // 最も件数の多い担当者に併せるため、縦の配列を埋める。
//            for( $j = $userCustomerSum; $j <= $maxCustomerSum; $j++ ){
//                $tableValues[$x_key][$j] = null;
//            }
//
//            // HTML出力用に逆順に並べ替える
//            $tableValues[$x_key] = array_reverse( $tableValues[$x_key] );
//
//        }
//
//        return array( $staffNames, $tableValues );
//    }
//
//}
