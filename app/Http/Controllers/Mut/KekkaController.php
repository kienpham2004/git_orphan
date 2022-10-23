<?php

namespace App\Http\Controllers\Mut;

use App\Lib\Util\DateUtil;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\tInitSearch;
use App\Http\Controllers\Controller;
use Session;
// 独自
use OhInspection;

// 最大1時間
set_time_limit( 3600 );

// 実行時間を無限に
ini_set('max_execution_time', 0);

// メモリ制限値を上げておく。
ini_set('memory_limit', '1024M');

class KekkaController extends Controller {

    use tInitSearch;

    /**
     * コンストラクタ
     */
    public function __construct(){
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
        $this->displayObj->category = "mut";
        // 画面名
        $this->displayObj->page = "kekka";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Mut\KekkaController";
    }
    
    #######################
    ## Controller method
    #######################

    /**
     * 一覧画面のデータを表示
     * @param  [type] $search     [description]
     * @return [type]             [description]
     */
    public function showListData( $requestObj ){
        // 検索部分の値を格納した配列を取得
        $search = $this->getSearchParams();

        return view(
            $this->displayObj->tpl . '.upload',
            compact(
                'search'
            )
        )
        ->with( "title", "データ抽出画面" )
        ->with( 'displayObj', $this->displayObj );
    }

    #######################
    ## Excelアップロード
    #######################

    /**
     * Excelアップロード
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function postUpload( SearchRequest $requestObj ){
        // 検索の値を取得
        $search = $requestObj->all();

        // 必要な値がある時に処理
        if( isset( $search["target_ym"] ) == True && !empty( $search["target_ym"] ) == True ){
            $img_name = OhInspection::getImageName('csv_file');
            
            if (! is_null($img_name)) {
                /** @var string 画像の拡張子 */
                $extension = OhInspection::getImageExtension($img_name);
                /** @var string 新しいファイル名（例：999.jpg） */
                $new_name = $search["target_ym"].'.'.$extension;
                /** 画像ファイルを移動させる処理 */
                OhInspection::moveImage( 'csv_file', 'DmKekka', $new_name );
            }

        }

        // 更新のセッションを用意
        Session::put('update', 1);
        
        return view(
            $this->displayObj->tpl . '.upload',
            compact(
                'search'
            )
        );
    }
    
}
