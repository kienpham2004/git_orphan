<?php

namespace App\Http\Controllers\Result;

use App\Lib\Util\Constants;
use App\Original\Util\SessionUtil;
use App\Http\Controllers\tInitSearch;

trait tResultSearch {

    use tInitSearch;

    #######################
    ## 検索・並び替え
    #######################

    /**
     * 並び替え部分のデフォルト値を指定
     * @return [type] [description]
     */
    public function extendSortParams() {
        // 複数テーブルにあるidが重複するため明示的にエイリアス指定
        $sort = [ 'tgc_car_name' => 'asc' ];

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
            // 検索項目を保存したセッションを取得
            $search = SessionUtil::getSearch();
            // エラーチェックフラグを取得する
            $errorCheck = SessionUtil::hasValidation();

            // セッションのinspectoin_ym_fromが空ならデフォルトを設定とチェックエラー無しの場合
            if ( empty( $search['inspection_ym_from']) &&  $errorCheck == false) {
                $search['inspection_ym_from'] = $this->selectedYm();
            }

        }else{
            // ユーザー情報を取得(セッション)
            $loginAccountObj = SessionUtil::getUser();

            // 店長以下の権限の時
            if ( ! in_array( $loginAccountObj->getRolePriority(), [1,2,3] ) ) {
                $search['base_code'] = $this->selectedBaseCode();
            }

            $search['inspection_ym_from'] = $this->selectedYm();
        }

        // デフォルトを車検に指定
        $search['inspect_divs'] = [];

        return $search;
    }

    #######################
    ## Controller method
    #######################

}
