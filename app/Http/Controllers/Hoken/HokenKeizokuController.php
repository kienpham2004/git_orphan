<?php

namespace App\Http\Controllers\Hoken;

use App\Original\Util\SessionUtil;
use App\Commands\Hoken\Hoken\ListCommand;
use App\Commands\Hoken\Hoken\ListCsvCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;
use App\Http\Controllers\tEdit;
use App\Http\Controllers\tEditHoken;
use App\Lib\Util\DateUtil;
use App\Lib\Util\Constants;

class HokenKeizokuController extends Controller{

    use tInitSearch, tEdit, tEditHoken;

    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
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
        $this->displayObj->page = "hoken_keizoku";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Hoken\HokenKeizokuController";
        // 出力するcsvファイル名
        $this->displayObj->csvFileName = "自社継続推進リスト_".date("Ymd_His").".csv";
        //　同じ処理画面チェック
        tInitSearch::checkCurrentScreen(Constants::H00);
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
            'tb_insurance.insu_inspection_target_ym' => 'asc',
            'base_code' => 'asc',
            'user_name' => 'asc',
            'tb_insurance.insu_insurance_end_date' => 'asc'
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
        if ( SessionUtil::hasSearch() == True ){
            // 検索値を取得(セッション)
            $search = SessionUtil::getSearch();

        }else{

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

        // デフォルトに当月を指定
        //$search['insu_inspection_target_ym_from'] = $this->selectedYm();
        //$search['insu_inspection_target_ym_to'] = $this->selectedYm();

        // 満了年月も指定
        $search['insu_inspection_target_ym_from'] = $this->selectedYm();

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

        // セッションが存在ないの場合
        if(!SessionUtil::has(Constants::SEC_INSURANCE_JISHA_DATA_MIN)) {
            $minInsData = 0;
            $maxInsData = 0;
            tInitSearch::getMaxMinData(Constants::TB_INSURANCE, Constants::INSU_INSPECTION_TARGET_YM, $minInsData, $maxInsData);
            // 対象データのセッションに格納する
            SessionUtil::put(Constants::SEC_INSURANCE_DATA_MIN, $minInsData);
            SessionUtil::put(Constants::SEC_INSURANCE_DATA_MAX, $maxInsData);

            $minInsData = 0;
            $maxInsData = 0;
            $where = "WHERE insu_jisya_tasya = '".Constants::CONS_JISYA."' " ;
            tInitSearch::getInsuranceContactMinData($minInsData, $maxInsData, $where);
            // 対象データのセッションに格納する
            SessionUtil::put(Constants::SEC_INSURANCE_JISHA_DATA_MIN, $minInsData);
            SessionUtil::put(Constants::SEC_INSURANCE_JISHA_DATA_MAX, $maxInsData);
        }

        // そもそも取得できないので引数はnullにしている
        $showData = $this->dispatch(new ListCommand($sort, $requestObj, "自社分"));
        
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
        $screenID = Constants::H00;
        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'runTime',
                'screenID',
                'downloadCsv'
            )
        )
        ->with( "title", "自社継続推進リスト" )
        ->with( 'displayObj', $this->displayObj )
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
                    "自社分",
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
        
}
