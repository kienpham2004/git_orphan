<?php

namespace App\Http\Controllers\Main;

use App\Original\Util\SessionUtil;
use App\Models\TargetCars;
use App\Commands\Main\Tenken\ListCommand;
use App\Commands\Main\Tenken\ListCsvCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;
use App\Http\Controllers\tEdit;
use App\Lib\Util\DateUtil;
use App\Lib\Util\Constants;

class TenkenController extends Controller{

    use tInitSearch, tEdit;

    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
        $this->title = "点検リスト";
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
        $this->displayObj->page = "tenken";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Main\TenkenController";
        // 出力するcsvファイル名
        $this->displayObj->csvFileName = "実施リスト(点検リスト)_".date("Ymd_His").".csv";
        //　同じ処理画面チェック
        tInitSearch::checkCurrentScreen(Constants::J10);
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
        list( $showData, $list_tmr_name, $list_cre_type ) = $this->dispatch(
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
        $screenID = Constants::J10;
        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'list_cre_type',
                'list_tmr_name',
                'list_ciao_type',
                'runTime',
                'screenID',
                'downloadCsv'
            )
        )
        ->with( "title", $this->title )
        ->with( 'displayObj', $this->displayObj )
        ->with( "tenkenObj", new TenkenController ) // 独自関数対策
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
            $csv = $this->dispatch(
                new ListCsvCommand(
                    $sort,
                    $requestObj,
                    $this->displayObj->csvFileName
                )
            );

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
        // 入庫済
        if( $value->mi_rstc_reserve_status == '出荷済' ){
            return 'glaphResultBg2';
        }
        // 予約済
        elseif( isset($value->mi_rstc_reserve_status)
            && (   $value->mi_rstc_reserve_status =='完成'
                || $value->mi_rstc_reserve_status =='入庫'
                || $value->mi_rstc_reserve_status =='診断'
                || $value->mi_rstc_reserve_status =='作業待ち'
                || $value->mi_rstc_reserve_status =='作業中'
                || $value->mi_rstc_reserve_status =='検査'
                || $value->mi_rstc_reserve_status =='作業完了'
                || $value->mi_rstc_reserve_status =='出庫待ち'
                || $value->mi_rstc_reserve_status =='予約確定（承認）'
                || $value->mi_rstc_reserve_status =='納品書発行済'))
        {
            return 'glaphResultBg1';
        }
        // 自社代替
        elseif( $value->tgc_status == '23' ){
            return 'glaphResultBg3';
        }
        // その他
        elseif( $value->tgc_status == '21' || $value->tgc_status == '22' )
        {
            return 'glaphResultBg5';
        }
        // 未確認
        elseif( $value->tgc_status == '1' || $value->tgc_status == ''){
            // return 'glaphEigyoTenkenBg1';
            return '';
        // 入庫意向
        }elseif( $value->tgc_status == '2' ){
            return 'glaphEigyoBg1';
        // 代替意向
        }elseif( $value->tgc_status == '3' ){
            return 'glaphEigyoBg3';
        // 点検意向無
        }elseif( $value->tgc_status == '4' ){
            return 'glaphEigyoBg2';
        // 抹消・転売
        }elseif( $value->tgc_status == '5' ){
            return 'glaphEigyoBg6';
        }
        return '';
    }

}
