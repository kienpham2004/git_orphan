<?php

namespace App\Http\Controllers\Main;

use App\Original\Util\SessionUtil;
use App\Models\TargetCars;
use App\Commands\Main\Syaken\ListCommand;
use App\Commands\Main\Syaken\ListCsvCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;
use App\Http\Controllers\tEdit;
use App\Lib\Util\DateUtil;
use App\Lib\Util\Constants;

class SyakenController extends Controller{

    use tInitSearch, tEdit;

    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
        $this->title = "車検リスト";
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
        $this->displayObj->category = "main";
        // 画面名
        $this->displayObj->page = "syaken";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Main\SyakenController";
        // 出力するcsvファイル名
        $this->displayObj->csvFileName = "実施リスト(車検リスト)_".date("Ymd_His").".csv";
        //　同じ処理画面チェック
        tInitSearch::checkCurrentScreen(Constants::J00);
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
        $sort = [ TargetCars::getTableName() . '.tgc_customer_name_kata' => 'asc' ];

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
        if ( SessionUtil::hasSearch() == True ){
            // 検索値を取得(セッション)
            $search = SessionUtil::getSearch();

        }else{

            $search['tgc_inspection_ym_from'] = $this->selectedYm();
            $search['tgc_inspection_ym_to'] = $this->selectedYm();

            // 権限を調べ為の値を取得
            $loginAccountObj = SessionUtil::getUser();

            // 店長権限よりも上の階層の時の処理
            if ( !in_array( $loginAccountObj->getRolePriority(), [1,2,3] ) ) {
                $search['base_code'] = $this->selectedBaseCode();
                if( !in_array( $loginAccountObj->getRolePriority(), [4,5] ) ) {
                    $search['user_id'] = $this->selectedUserId();
                }
            }
            // デフォルト6ヶ月ロックを非表示
            $search['tgc_lock_flg6'] = 1;
        }

        return $search;
    }

    #######################
    ## 一覧画面
    #######################

    /**
     * 一覧画面のデータを表示
     * @param  [type] $search     [description]
     * @param  [type] $sort   [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj ){
        // そもそも取得できないので引数はnullにしている
        list($showData, $list_tmr_name, $list_tgc_action_code, $list_cre_type) = $this->dispatch(
            new ListCommand($sort, $requestObj));

        //dd($showData);
        //　表示用に、並び替え情報を取得
        if( isset( $sort['sort'] ) == True && !empty( $sort['sort'] ) == True ){
            foreach ( $sort['sort'] as $key => $value ) {
                // 並び替え情報を格納
                $sortTypes = [
                    'sort_key' => $key,
                    "sort_by" => $value
                ];
            }
        }

        // ダウンロード設定
        $downloadCsv = false;
        if($showData != null && !$showData->isEmpty()){
            $downloadCsv = true;
        }

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
        $screenID = Constants::J00;
        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'list_tmr_name',
                'list_tgc_action_code',
                'list_cre_type',
                'list_ciao_type',
                'runTime',
                'screenID',
                'downloadCsv'
            )
        )
        ->with( "title", $this->title )
        ->with( 'displayObj', $this->displayObj )
        ->with( "syakenObj", new SyakenController ) // 独自関数対策
        ->with( "sortUrl", action( $this->displayObj->ctl . '@getSort' ) );
    }

    /**
     * CSVダウンロード機能
     * @param  SearchRequest $requestObj [description]
     * @return [type]                 [description]
     */
    public function getCsv( SearchRequest $requestObj ){
        // 並び替えの値を取得
        $sort = $this->getSortParams();

        //try {
            $csv = $this->dispatch(new ListCsvCommand($sort, $requestObj, $this->displayObj->csvFileName));

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
     * TMR意向
     * @param   object  $value
     * @return  string  cssのクラス名
     */
    public function addCssTmr( $value ) {
        // 2022/03/17 add field htc
        if($value->mi_htc_login_flg == "1"){
            return 'glaphTmrBg7';
        }elseif($value->mi_tmr_in_sub_intention == "入庫意向有"){
            return 'glaphTmrBg1';
            
        }elseif($value->mi_tmr_in_sub_intention == "自社予約済"){
            return 'glaphTmrBg2';
            
        }elseif($value->mi_tmr_in_sub_intention == "代替意向有"){
            return 'glaphTmrBg3';
            
        }elseif($value->mi_tmr_in_sub_intention == "他社予約済"){
            return 'glaphTmrBg4';
            
        }elseif($value->mi_tmr_in_sub_intention == "代替意向無"){
            return 'glaphTmrBg5';
            
        }elseif($value->mi_tmr_in_sub_intention == "入庫意向無"){
            return 'glaphTmrBg6';
        }
    }

    /**
     * 活動意向・活動実績
     * @param   object  $value
     * @return  string  cssのクラス名
     */
    public function addCssTgc( $value ) {
        // 代替済
        if( isset($value->mi_dsya_keiyaku_car) ){
            return 'glaphResultBg3';
        }
        // 入庫済
        elseif( isset($value->mi_dsya_syaken_jisshi_date) ){
            return 'glaphResultBg2';
        }
        // 予約済
        // 2022/03/22 update
        elseif( isset($value->mi_dsya_syaken_reserve_date) && date("Ym", strtotime( $value->tgc_syaken_next_date . '-12 month')) < date("Ym", strtotime($value->mi_dsya_syaken_reserve_date)) ){
            return 'glaphResultBg1';
        }
        // 自社車検
        elseif( $value->tgc_status == "11" ){
            return 'glaphEigyoBg1';
        // 他社車検
        }elseif( $value->tgc_status == "12" ){
            return 'glaphEigyoBg2';
        // 自社代替
        }elseif( $value->tgc_status == "13" ){
            return 'glaphEigyoBg3';
        // 他社代替
        }elseif( $value->tgc_status == "14" ){
            return 'glaphEigyoBg4';
        // 廃車転売
        }elseif( $value->tgc_status == "15" ){
            return 'glaphEigyoBg5';
            // 転居予定
        }
        elseif( $value->tgc_status == "16" ){
            return 'glaphEigyoBg6';
        // 拠点移管
        }elseif( $value->tgc_status == "17" ){
            return 'glaphEigyoBg7';
        }
    }

}
