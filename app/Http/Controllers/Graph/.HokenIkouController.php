<?php
//
//namespace App\Http\Controllers\Graph;
//
//use App\Original\Codes\Intent\IntentSyatenkenCodes;
//use App\Original\Util\SessionUtil;
//use App\Commands\Graph\HokenIkouCommand;
//use App\Http\Requests\SearchRequest;
//use App\Http\Controllers\Controller;
//use App\Http\Controllers\Graph\tGraphSearch;
//use App\Http\Controllers\tEdit;
//use App\Models\TargetCars;
//
///**
// * トレジャーボード(保険意向)
// *
// * @author yhatsutori
// *
// */
//class HokenIkouController extends Controller {
//
//    use tGraphSearch, tEdit;
//
//    /**
//     * コンストラクタ
//     */
//    function __construct(){
//        // 表示部分で使うオブジェクトを作成
//        $this->initDisplayObj();
//    }
//
//    #######################
//    ## initalize
//    #######################
//
//    /**
//     * 表示部分で使うオブジェクトを作成
//     * @return [type] [description]
//     */
//    public function initDisplayObj(){
//        // 表示部分で使うオブジェクトを作成
//        $this->displayObj = app('stdClass');
//        // カテゴリー名
//        $this->displayObj->category = "graph";
//        // 画面名
//        $this->displayObj->page = "hoken_ikou";
//        // 基本のテンプレート
//        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
//        // コントローラー名
//        $this->displayObj->ctl = "Graph\HokenIkouController";
//    }
//
//    #######################
//    ## グラフ画面
//    #######################
//
//    /**
//     * 一覧画面のデータを表示
//     * @param  [type] $search  [description]
//     * @param  object $requestObj [description]
//     * @return [type]             [description]
//     */
//    public function showListData( $search, $sort, $requestObj ){
//        // 担当者毎のデータを取得
//        $treasureValues = $this->dispatch(
//            new HokenIkouCommand(
//                $search,
//                $requestObj
//            )
//        );
//
//        return view(
//            $this->displayObj->tpl . '.index',
//            compact(
//                'search',
//                'treasureValues'
//            )
//        )
//        ->with( "title", "トレジャーボード(保険意向)" )
//        ->with( 'displayObj', $this->displayObj )
//        ->with( "ikouObj", new HokenIkouController ); // 独自関数対策
//    }
//
//    ######################
//    ## 横積みグラフの色の指定
//    ######################
//
//    /**
//     * 縦積みグラフで表示するテーブルのレイアウトのcssを出力(意向)
//     * @param  [type]$insu_status [description]
//     * @return [type]             [description]
//     */
//    function actionCss( $insu_status ) {
//        if( !empty( $insu_status ) ) {
//            if( $insu_status == "12" ) {
//                return 'glaphHokenBackBg2';
//
//            } else if( $insu_status == "13" ) {
//                return 'glaphHokenBackBg3';
//
//            } else if( $insu_status == "16" ) {
//                return 'glaphHokenBackBg4';
//
//            }
//
//        } else {
//            return 'glaphHokenBackBg1';
//        }
//    }
//
//    /**
//     * 縦積みグラフで表示するテーブルのレイアウトのcssを出力(意向)
//     * @param  [type]$insu_status [description]
//     * @return [type]             [description]
//     */
//    function actionTextColor( $insu_status ) {
//        if( !empty( $insu_status ) ) {
//            if($insu_status == "12" ) {
//                return '#fff';
//
//            }else if( $insu_status == "13" ) {
//                return '#000';
//
//            }else if( $insu_status == "16" ) {
//                return '#fff';
//
//            }
//
//        } else {
//            return '#000';
//        }
//    }
//}
