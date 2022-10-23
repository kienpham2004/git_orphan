<?php

namespace App\Http\Controllers\Hoken;

use App\Original\Util\SessionUtil;
use App\Commands\Hoken\Ikou\HokenIkouCommand;
use App\Commands\Hoken\Hoken\ListCsvCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;
use App\Http\Controllers\tEdit;
use App\Http\Controllers\tEditHoken;
use App\Lib\Util\DateUtil;
use App\Lib\Util\Constants;
use Illuminate\Support\Facades\Redirect;
use App\Models\Base;

/**
 * トレジャーボード(保険意向)
 *
 * @author yhatsutori
 *
 */
class HokenIkouController extends Controller {

    use tInitSearch, tEdit, tEditHoken;

    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
        $this->title = "他社・新規管理トレジャーボード";
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
        $this->displayObj->category = "hoken";
        // 画面名
        $this->displayObj->page = "hoken_ikou";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Hoken\HokenIkouController";
        // 出力するcsvファイル名
        //$this->displayObj->csvFileName = "他社・新規管理トレジャーボード()_".date("Ymd_His").".csv";
    }

    #######################
    ## 検索・並び替え
    #######################

    /**
     * 並び替え部分のデフォルト値を指定
     * @return [type] [description]
     */
    public function extendSortParams() {
        // 複数テーブルにあるidが重複するため明示的にエイリアス指定
        $sort = [
            'base_code' => 'asc',
            'user_name' => 'asc'
        ];

        return $sort;
    }
    
    /**
     * 検索部分のデフォルト値を指定
     * @return [type] [description]
     */
    public function extendSearchParams(){
        // 検索の値を格納する配列
        $search = array();

        // 検索値のセッションの確認
        if( SessionUtil::hasSearch() == True ){
            // 検索値を取得(セッション)
            $search = SessionUtil::getSearch();
            // エラーチェックフラグを取得する
            $errorCheck = SessionUtil::hasValidation();

            // 対象月が指定されていない時の処理
            if( empty( $search['inspection_ym_from'] ) == True && $errorCheck == false ){
                $search['inspection_ym_from'] = $this->selectedYm();
            }

        }else{
            // 対象月
            $search['inspection_ym_from'] = $this->selectedYm();
            
            // 権限を調べ為の値を取得
            $loginAccountObj = SessionUtil::getUser();

            // 店長権限よりも上の階層の時の処理
            if ( !in_array( $loginAccountObj->getRolePriority(), [1,2,3] ) ) {
                $search['base_code'] = $this->selectedBaseCode();

                // いずれ、ユーザー名をデフォルト検索値とする可能性があり
                if( !in_array( $loginAccountObj->getRolePriority(), [4,5] ) ) {
                    $search['user_id'] = $this->selectedUserId();
                }
            }
        }
        
        // 初期状態の検索条件
        // 拠点コードを限定
        if( empty( $search['base_code'] ) ) {
            $search['base_code'] = '01';
        }
                
        return $search;
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

        // 満期年月の値を取得する。
        $minInsData = 0;
        $maxInsData = 0;
        $where = "WHERE insu_jisya_tasya = '".Constants::CONS_TASYA. "' " ;
        $where .= "  OR insu_jisya_tasya = '".Constants::CONS_SHINKI."' " ;
        tInitSearch::getInsuranceContactMinData($minInsData, $maxInsData, $where);
        // 対象データのセッションに格納する
        SessionUtil::put(Constants::SEC_INSURANCE_TASHA_DATA_MIN, $minInsData);
        SessionUtil::put(Constants::SEC_INSURANCE_TASHA_DATA_MAX, $maxInsData);

        $treasureValues = null;
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"満期年月");
        // チェック問題がない場合、データを取得
        if ($check == Constants::CONS_OK ) {
            // 担当者毎のデータを取得
            $treasureValues = $this->dispatch(new HokenIkouCommand($search, $requestObj));
        }

        // データがあるフラグ
        $displayFlag = false;
        if($treasureValues != null ) {
            foreach( $treasureValues as $ym => $list ){
                if( isset($list)){
                    $displayFlag = true;
                    break;
                }
            }
        }

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
        $screenID = Constants::H10;
        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'treasureValues',
                'runTime',
                'screenID',
                'displayFlag'
            )
        )
        ->with( "title", $this->title )
        ->with( 'displayObj', $this->displayObj )
        ->with( "ikouObj", new HokenIkouController ) // 独自関数対策
        ->withErrors($validator);
    }

    /**
     * CSVダウンロード機能
     * @param  SearchRequest $requestObj [description]
     * @return [type]                 [description]
     */
    public function getCsv( SearchRequest $requestObj ){

        // 検索値を登録(セッション)
        SessionUtil::putSearch( $requestObj->all() );

        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $requestObj->all(),"満期年月");
        if ($check == Constants::CONS_ERROR) {
            return Redirect::to('/hoken/ikou/search')
                ->withInput()
                ->withErrors($validator);
        }

        // 並び替えの値を取得
        $sort = $this->getSortParams();
        // トレジャーボード用の検索の値を置き換えました。
        $requestObj->insu_inspection_ym_from = $requestObj->inspection_ym_from;
        
        //try {
            $baseName = Base::getBaseNameByCode($requestObj->base_code) ;
            $csvFileName = "他社・新規管理トレジャーボード(".$baseName[0].")_".date("Ymd_His").".csv";
            $csv = $this->dispatch(new ListCsvCommand($sort, $requestObj, "他社分", $csvFileName));
            return $csv;

        /*
        } catch ( \Exception $e ) {
            \Log::debug( $e->getMessage() );

            return redirect( action( $this->displayObj->ctl . '@getSearch' ) )
                    ->withErrors( $e->getMessage() );
        }
        */
    }

    /**
     * CSVダウンロード機能
     * @param  SearchRequest $requestObj [description]
     * @return [type]                 [description]
     */
    public function getCsvAll( SearchRequest $requestObj ){
        // 検索値を登録(セッション)
        SessionUtil::putSearch( $requestObj->all() );

        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $requestObj->all(),"満期年月");
        if ($check == Constants::CONS_ERROR) {
            return Redirect::to('/hoken/ikou/search')
                ->withInput()
                ->withErrors($validator);
        }

        // 並び替えの値を取得
        $sort = $this->getSortParams();
        // トレジャーボード用の検索の値を置き換えました。
        $requestObj->insu_inspection_ym_from = $requestObj->inspection_ym_from;
        // CSVダウンロードをするときは全拠点
        $requestObj->base_code = NULL;
        $requestObj->user_id = NULL;

        //try {
            $csvFileName = "他社・新規管理トレジャーボード(全拠点)_".date("Ymd_His").".csv";
            $csv = $this->dispatch(new ListCsvCommand($sort, $requestObj, "他社分", $csvFileName));
            return $csv;
        /*
        } catch ( \Exception $e ) {
            \Log::debug( $e->getMessage() );

            return redirect( action( $this->displayObj->ctl . '@getSearch' ) )
                    ->withErrors( $e->getMessage() );
        }
        */
    }

    ######################
    ## 横積みグラフの色の指定
    ######################
    
    /**
     * 縦積みグラフで表示するテーブルのレイアウトのcssを出力(意向)
     * @param  [type]$insu_status [description]
     * @return [type]             [description]
     */
    function actionCss( $insu_status ) {
        if( !empty( $insu_status ) ) {
            if( $insu_status == "6" ) {
                return 'glaphHokenBackBg0';

            } else if( $insu_status == "5" ) {
                return 'glaphHokenBackBg1';

            } else if( $insu_status == "4" ) {
                return 'glaphHokenBackBg2';

            } else if( $insu_status == "3" ) {
                return 'glaphHokenBackBg3';

            } else if( $insu_status == "2" ) {
                return 'glaphHokenBackBg4';

            } else if( $insu_status == "7" ) {
                return 'glaphHokenBackBg5';

            } else if( $insu_status == "1" ) {
                return 'glaphHokenBackBg6';

            } else if( $insu_status == "99" ) {
                return 'glaphHokenBackBg99';

            } else if( $insu_status == "100" ) {
                return 'glaphHokenBackBg100';

            }
            
        } else {
            return 'glaphHokenBackBg0';
        }
    }

    /**
     * 縦積みグラフで表示するテーブルのレイアウトのcssを出力(意向)
     * @param  [type]$insu_status [description]
     * @return [type]             [description]
     */
//    function actionTextColor( $insu_status ) {
//        if( !empty( $insu_status ) ) {
//            if( $insu_status == "1" ) {
//                return '#000';
//
//            }elseif( $insu_status == "2" ) {
//                return '#fff';
//
//            }elseif( $insu_status == "3" ) {
//                return '#000';
//                
//            }elseif( $insu_status == "4" ) {
//                return '#000';
//                
//            }elseif( $insu_status == "5" ) {
//                return '#000';
//                
//            }elseif( $insu_status == "6" ) {
//                return '#000';
//
//            }elseif( $insu_status == "7" ) {
//                return '#fff';
//                
//            }elseif( $insu_status == "99" ) {
//                return '#fff';
//                
//            }elseif( $insu_status == "100" ) {
//                return '#fff';
//                
//            }else{
//                return '#000';    
//            }
//
//        } else {
//            return '#000';
//        }
//    }

    /**
     * 縦積みグラフで手続き完了日が入力されている時に表示するテーブルの外枠のcssを出力
     * @param  [type]$insu_status [description]
     * @return [type]             [description]
     */
    function tetsudukiCss( $tetsuduki_date ) {
        if( !empty( $tetsuduki_date ) ) {
            return 'glaphHokenTetsudukiBg';

        } else {
            return '';
        }
    }

    /**
     * 縦積みグラフでペアフリート有の場合に表示するマークのレイアウトのcssを出力
     * @param  [type]$insu_status [description]
     * @return [type]             [description]
     */
    function pairFleetCss( $insu_pair_fleet ) {
        if( !empty( $insu_pair_fleet ) ) {
            if( $insu_pair_fleet == "ペア" ) {
                return 'pairFleetMark';

            } else {
                return '';

            }
            
        } else {
            return '';
        }
    }
}
