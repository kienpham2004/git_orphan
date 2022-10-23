<?php
//
//namespace App\Http\Controllers\Graph;
//
//use App\Original\Util\SessionUtil;
//use App\Commands\Graph\SyakenJisshiCommand;
//use App\Http\Requests\SearchRequest;
//use App\Http\Controllers\Controller;
//use App\Http\Controllers\Graph\tGraphSearch;
//
///**
// * トレジャーボード(車検)
// *
// * @author yhatsutori
// *
// */
//class SyakenJisshiController extends Controller {
//
//    use tGraphSearch;
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
//        $this->displayObj->page = "syaken_jisshi";
//        // 基本のテンプレート
//        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
//        // コントローラー名
//        $this->displayObj->ctl = "Graph\SyakenJisshiController";
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
//        // 表示する値を取得
//        $showObj = $this->dispatch(
//            new SyakenJisshiCommand(
//                $search,
//                $requestObj
//            )
//        );
//
//        // 総数を取得
//        $ycountMax = 0;
//        if( !empty( $showObj->ycount ) ){
//            $ycountMax = $showObj->ycount[0]["sum"];
//        }
//
//        return view(
//            $this->displayObj->tpl . '.index',
//            compact(
//                'search',
//                'showObj',
//                'ycountMax'
//            )
//        )
//        ->with( "title", "車検状況" )
//        ->with( "displayObj", $this->displayObj )
//        ->with( "jisshiObj", new SyakenJisshiController ); // 独自関数対策
//    }
//
//    ######################
//    ## 縦積みグラフの色の指定
//    ######################
//
//    /**
//     * 縦積みグラフで表示するテーブルのレイアウトのcssを出力(意向と実績)
//     * @param  string $back_num 背景表示の値
//     * @return 背景の色
//     */
//    public function getShowStyle( $back_num ){
//        if( $back_num == 4 ){ // 期日先行実施
//            return "glaphCell glaphBackBg1";
//        }else if( $back_num == 5 ){ // 当月実施
//            return "glaphCell glaphBackBg2";
//        }
//
//        return "custBox";
//    }
//
//    /**
//     * 文字の色を出力
//     * @param  string $back_num 背景表示の値
//     * @return 文字の色
//     */
//    public function getTextColor( $back_num ){
//        if( $back_num == 4 ){ // 期日先行実施
//            return 'glaphTextBg1';
//        }else if( $back_num == 5 ){ // 当月実施
//            return 'glaphTextBg2';
//        }
//
//        return 'glaphTextBg1';
//    }
//
//}
