<?php

namespace App\Http\Controllers\DmHanyou;

use App\Original\Util\SessionUtil;
use App\Commands\DmHanyou\Dm\ListHanyouCommand;
use App\Commands\DmHanyou\Dm\UpdateDmFlgCommand;
use App\Commands\DmHanyou\Dm\UpdateConfirmFlgCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;

class DmHanyouController extends Controller{

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
        $this->displayObj->category = "dm_hanyou";
        // 画面名
        $this->displayObj->page = "dm";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "DmHanyou\DmHanyouController";
    }

    #######################
    ## 検索・並び替え
    #######################

    /**
     * [extendSortParams description]
     * @return [type] [description]
     */
    public function extendSortParams() {
        // 複数テーブルにあるidが重複するため明示的にエイリアス指定
        $sort = [
            'base_code' => 'asc',
            'user_id' => 'asc'
        ];

        return $sort;
    }

    /**
     * 検索部分のデフォルト値を指定
     * @return [type] [description]
     */
    public function extendSearchParams(){
        // 検索の値を格納する配列
        $search = [];
        
        // 検索値のセッションの確認
        if ( SessionUtil::hasSearch() == True ){
            // 検索値を取得(セッション)
            $search = SessionUtil::getSearch();

        }else{
            // 担当者情報を取得
            $loginAccountObj = SessionUtil::getUser();
            // 店長以下の権限の時
            if ( !in_array( $loginAccountObj->getRolePriority(), [1,2,3] ) ) {
                $search['base_code'] = $this->selectedBaseCode();
                $search['user_id'] = $this->selectedUserId();
            }

        }
        
        return $search;
    }

    #######################
    ## 一覧画面
    #######################

    /**
     * 一覧画面のデータを表示
     * @param  [type] $search  [description]
     * @param  array $sort    [description]
     * @param  object $requestObj [description]
     * @return [type]             [description]
     */
    public function showListData( $search, $sort, $requestObj ){
        //
        $showData = $this->dispatch(
            new ListHanyouCommand(
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
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData'
            )
        )
        ->with( "title", "イベントDM送付リスト" )
        ->with( 'displayObj', $this->displayObj )
        ->with( "sortUrl", action( $this->displayObj->ctl . '@getSort' ) );
    }

    ######################
    ## DMフラグの作成
    ######################

    /**
     * 不要チェックの一括更新処理
     * @param  SearchRequest $requestObj [description]
     * @return json                    [description]
     */
    public function postBulkUpdate( SearchRequest $requestObj ) {
        $this->dispatch(
            new UpdateDmFlgCommand(
                $requestObj
            )
        );
    }

    /**
     * 確認チェックボタンの処理
     * ログインしているユーザーが不要チェックを完了させたというときに使います。
     *
     * @return json
     */
    public function postDmCheck(){
        $this->dispatch(
            new UpdateConfirmFlgCommand()
        );
    }

}
