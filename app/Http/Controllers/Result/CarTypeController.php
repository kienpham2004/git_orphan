<?php

namespace App\Http\Controllers\Result;

use App\Original\Util\SessionUtil;
use App\Http\Requests\SearchRequest;
use App\Commands\Result\SumCarCommand;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Result\tResultSearch;
use App\Lib\Util\DateUtil;
use App\Http\Controllers\tInitSearch;
use Illuminate\Support\Facades\Redirect;
use App\Lib\Util\Constants;
/**
 * 実績分析の車種別集計のコントローラー
 */
class CarTypeController extends Controller {

    use tResultSearch;

    /**
     * コンストラクタ
     */
    function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();
        $this->title = "車種別防衛率";
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
        $this->displayObj->page = "car";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Result\CarTypeController";
        //　同じ処理画面チェック
        tInitSearch::checkCurrentScreen(Constants::B30);
    }
    
    #######################
    ## 検索・並び替え
    #######################
    
    #######################
    ## 一覧画面
    #######################
    
    /**
     * 一覧画面のデータを表示
     * @param  object $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj ){
//        // 入力エラーチェック
//        $validator = tInitSearch::checkValidate($requestObj,"車検年月");
//        if ($validator != null ) {
//            return Redirect::to('/result/car/search')->withErrors($validator);
//        }
        $showData = null;
        // 入力エラーチェック
        $check = tInitSearch::checkValidate($validator, $search,"車検年月");
        // チェック問題がない場合、データを取得
        if ($check == Constants::CONS_OK ) {
            $showData = $this->dispatch(new SumCarCommand($search, $requestObj, $sort, "syaken"));
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

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData',
                'runTime'
            )
        )
        ->with( "title", $this->title )
        ->with( 'displayObj', $this->displayObj )
        ->with( "sortUrl", action( $this->displayObj->ctl . '@getSort' ) )
        ->withErrors($validator);
    }

}
