<?php

namespace App\Http\Controllers\Dm;

use App\Original\Util\SessionUtil;
use App\Models\TargetCars;
use App\Commands\Dm\Confirm\ListCommand;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;

class DmConfirmController extends Controller{

    use tInitSearch;

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
        $this->displayObj->category = "dm";
        // 画面名
        $this->displayObj->page = "confirm";
        // 基本のテンプレート
        $this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
        // コントローラー名
        $this->displayObj->ctl = "Dm\DmConfirmController";
    }

    #######################
    ## 検索・並び替え
    #######################

    /**
     * [extendSortParams description]
     * @return [type] [description]
     */
    public function extendSortParams() {
        $sort = [
            'tb_base.base_code' => 'asc',
            'tb_user_account.user_id' => 'asc'
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
                $search['user_id'] = $this->selectedUserId();
                $search['base_code'] = $this->selectedBaseCode();
            }

        }
        
        // デフォルトを当月に指定
        //$search['tgc_shipping_ym'] = $this->selectedYm();
        
        // デフォルトを1ヶ月後に指定
        $search['tgc_shipping_ym'] = date("Ym", strtotime( date("Y-m-01"). "+2 month" ) );
        
        // 1日までは前月も表示
        if( date("d") == "01" ){
            $search["tgc_shipping_ym"] = date("Ym", strtotime( date("Y-m-01"). "+1 month" ));
        }

        return $search;
    }

    #######################
    ## 一覧画面
    #######################

    /**
     * 一覧画面のデータを表示
     * @param  array $search  [description]
     * @param  array $sort    [description]
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
        
        $dm_ym = date('Y年m月中旬', strtotime( date("Y-m-01"). "+1 month" ) );
        // 1日までは前月表示
        if( date("d") == "01" ){
            $dm_ym = date('Y年m月中旬');
        }

        return view(
            $this->displayObj->tpl . '.index',
            compact(
                'search',
                'sortTypes',
                'showData'
            )
        )
        ->with( "title", $dm_ym."発送分 チェック報告リスト" )
        ->with( 'displayObj', $this->displayObj )
        ->with( "sortUrl", action( $this->displayObj->ctl . '@getSort' ) );
    }

}
