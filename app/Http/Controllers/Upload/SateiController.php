<?php

namespace App\Http\Controllers\Upload;

use App\Original\Util\SessionUtil;
use App\Commands\Upload\Satei\ListCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;

/**
 * 査定データのみを抽出するコントローラー
 * ※tExtractを使っていないのは、対象月という概念がないため
 */
class SateiController extends Controller
{
    use tInitSearch;

    /**
     * コンストラクタ
     */
    public function __construct(){
        // 表示部分で使うオブジェクトを作成
        $this->initDisplayObj();

        // 管理者権限を有しないとアクセス不可
        $this->middleware(
            'RoleAdmin',
            ['only' => ['getSearch', 'postSearch']]
        );
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
        $this->displayObj->category = "upload";
        // 画面名
        $this->displayObj->page = "satei";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Upload\SateiController";
    }

    #######################
    ## 一覧画面
    #######################
    
    /**
     * 一覧画面のデータを表示
     * @param  array $search      [description]
     * @param  array $sort        [description]
     * @param  object $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj ){
        // 
        $showData = $this->dispatch(
            new ListCommand(
                $sort,
                $requestObj
            )
        );

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
        
        return view(
            $this->displayObj->tpl .'.index',
            compact(
                'search',
                'sortTypes',
                'showData'
            )
        )
        ->with( "title", "査定リスト" )
        ->with( 'displayObj', $this->displayObj )
        ->with( "sortUrl", action( $this->displayObj->ctl . '@getSort' ) );
    }
    
}
