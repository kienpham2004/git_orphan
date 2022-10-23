<?php

namespace App\Http\Controllers\Result;

use App\Original\Util\SessionUtil;
use App\Http\Requests\SearchRequest;
use App\Commands\Result\Status\SumBaseCommand;
use App\Commands\Result\Status\SumUserCommand;
use App\Commands\Main\Syatenken\ListCsvCommand;
use App\Commands\Result\StatusCsvCommand;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Result\tResultSearch;
use App\Lib\Util\DateUtil;
// 独自
use OhPHPExcel;
use App\Http\Controllers\tInitSearch;
use Illuminate\Support\Facades\Redirect;
use App\Lib\Util\Constants;
Use Request;

/**
  * 実績分析の意向結果のコントローラー
 */
class StatusController extends Controller {
    
    use tResultSearch;
    
    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
        $this->title = "意向結果";
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
        $this->displayObj->category = "result";
        // 画面名
        $this->displayObj->page = "status";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Result\StatusController";
        //　同じ処理画面チェック
        tInitSearch::checkCurrentScreen(Constants::B40);
    }

    #######################
    ## 一覧画面
    #######################
    
    /**
     * 一覧画面のデータを表示
     * @param  object $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj ){

        $isStaffPage = Request::is("result/{$this->displayObj->page}/each-staff*");
        $showData = null;
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"車点検年月");
        // チェック問題がない場合、データを取得
        if ($check == Constants::CONS_OK ) {
            $showData = $this->dispatch(new SumBaseCommand($search, $requestObj));
        }

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
        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'runTime',
                'downloadCsv',
                'isStaffPage'
            )
        )
        ->with( "title", "意向結果" )
        ->with( 'displayObj', $this->displayObj )
        ->with( 'monthFlg', "True" )
        ->withErrors($validator);
    }
        
    /**
     * 実績分析の各画面で担当者ごとに表示
     * @param  SearchRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function getEachStaff( SearchRequest $requestObj ) {

        $isStaffPage = Request::is("result/{$this->displayObj->page}/each-staff*");
        $search = $requestObj->all();
        // 検索値を登録(セッション)
        SessionUtil::putSearch( $search );

        $showData = null;
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"車点検年月");
        // チェック問題がない場合、データを取得
        if ($check == Constants::CONS_OK ) {
            $showData = $this->dispatch(new SumUserCommand($search, $requestObj));
        }

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

        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'downloadCsv',
                'isStaffPage'
            )
        )
        ->with( "title", $this->title )
        ->with( 'displayObj', $this->displayObj )
        ->with( 'monthFlg', "True" )
        ->withErrors($validator);
    }

    /**
     * CSVダウンロード機能
     * @param  SearchRequest $requestObj [description]
     * @return [type]                 [description]
     */
    public function getCsv( SearchRequest $requestObj ){
        // 検索の値を取得
        $search = $requestObj->all();
        // 検索値を登録(セッション)
        SessionUtil::putSearch( $search );

        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"車点検年月");
        if ($check == Constants::CONS_ERROR) {
            return Redirect::to('/result/status/search')
                ->withInput()
                ->withErrors($validator);
        }
        
        //CSVファイル名
        $filename="実績分析_意向結果_".date("Ymd_His").".csv";

        $csv = $this->dispatch(
            new StatusCsvCommand(
                $requestObj,
                $filename
            )
        );
        
        return $csv;

    }

}
