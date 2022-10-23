<?php

namespace App\Http\Controllers\Graph;

use App\Commands\Graph\SyakenIkouCommand;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Graph\tGraphSearch;
use App\Http\Controllers\tEdit;
use App\Lib\Util\Constants;
use App\Lib\Util\DateUtil;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\tInitSearch;
use App\Original\Util\CodeUtil;
/**
 * トレジャーボード(車検)
 *
 * @author yhatsutori
 *
 */
class SyakenIkouController extends Controller {

    use tGraphSearch, tEdit;

    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
        $this->title = "トレジャーボード(車検)";
    }

    #######################
    ## initalize
    #######################

    /**
     * 表示部分で使うオブジェクトを作成
     * @return [type] [description]
     */
    public function initDisplayObj(){
        // 表示部分で使うオブジェクトを作成
        $this->displayObj = app('stdClass');
        // カテゴリー名
        $this->displayObj->category = "graph";
        // 画面名
        $this->displayObj->page = "syaken_ikou";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Graph\SyakenIkouController";
        //　同じ処理画面チェック
        tInitSearch::checkCurrentScreen(Constants::J00);
    }

    #######################
    ## グラフ画面
    #######################

    /**
     * 一覧画面のデータを表示
     * @param  [type] $search  [description]
     * @param  object $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj ){
        $treasureValues = null;
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"対象年月");
        // チェック問題がない場合、担当者毎のデータを取得
        if ($check == Constants::CONS_OK ) {
            $treasureValues = $this->dispatch(new SyakenIkouCommand($search, $requestObj));
        }

        // データがあるフラグ
        $displayFlag = false;
        if($treasureValues != null ) {
            if (!isset($search['display_flg']) || $search['display_flg'] == "0") { // 担当者別表示の場合
                foreach ($treasureValues as $ym => $list) {
                    if (isset($list)) {
                        $displayFlag = true;
                        break;
                    }
                }
            } else { // 月別表示の場合
                foreach ($treasureValues as $item => $list) {
                    if (count($list->rows) > 0) {
                        $displayFlag = true;
                        break;
                    }
                }
            }
        }

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'treasureValues',
                'runTime',
                'displayFlag'
            )
        )
        ->with( "title", $this->title )
        ->with( 'displayObj', $this->displayObj )
        ->with( "ikouObj", new SyakenIkouController ) // 独自関数対策
        ->withErrors($validator);
    }
    
    ######################
    ## 横積みグラフの色の指定
    ######################
    
    /**
     * 横積みグラフで表示するテーブルのレイアウトのcssを出力(意向)
     * @param  [type] $tmr_status [description]
     * @return [type]             [description]
     */
    function tmrCss( $tmr_status ){
        if( !empty( $tmr_status ) ) {
            if( $tmr_status == "1" ) {
                return 'glaphTmrBg1';
                
            }elseif( $tmr_status == "2" ) {
                return 'glaphTmrBg2';

            }elseif( $tmr_status == "3" ) {
                return 'glaphTmrBg3';

            }elseif( $tmr_status == "4" ) {
                return 'glaphTmrBg4';

            }elseif( $tmr_status == "5" ) {
                return 'glaphTmrBg5';

            }elseif( $tmr_status == "6" ) {
                return 'glaphTmrBg6';

            }elseif( $tmr_status == "7" ) {
                return 'glaphTmrBg7';

            }

        }else{
            return '';
        }
    }

    /**
     * 横積みグラフで表示するテーブルのレイアウトのcssを出力(意向)
     * @param  [type] $target_status [description]
     * @return [type]             [description]
     */
    function actionCss( $target_status ) {
        if( !empty( $target_status ) ) {
            if( $target_status == "103" ){      // 代替済
                return 'glaphResultBg3';
                
            }elseif( $target_status == "102" ){ // 入庫済
                return 'glaphResultBg2';
                
            }elseif( $target_status == "101" ){ // 予約済
                return 'glaphResultBg1';
                
            }elseif( $target_status == "11" ){  // 自社車検
                return 'glaphEigyoBg1';


            }elseif( $target_status == "12" ){  // 他社車検
                return 'glaphEigyoBg2';

            }elseif( $target_status == "13" ){  // 自社代替
                return 'glaphEigyoBg3';

            }elseif( $target_status == "14" ){  // 他社代替
                return 'glaphEigyoBg4';

            }elseif( $target_status == "15" ){  // 廃車・転売
                return 'glaphEigyoBg5';
            }
            elseif( $target_status == "16" ){  // 拠点移管
                return 'glaphEigyoBg6';

            }elseif( $target_status == "17" ){  // 転居予定
                return 'glaphEigyoBg7';
            }
        }
//        else {
//            return 'glaphEigyoBg_other';
//        }
    }
}
