<?php

namespace App\Http\Controllers\Graph;

use App\Original\Util\SessionUtil;
use App\Http\Controllers\tInitSearch;
use App\Lib\Util\Constants;
use Session;

trait tGraphSearch {
    
    use tInitSearch;
    
    #######################
    ## 検索・並び替え
    #######################
    
    /**
     * 検索部分のデフォルト値を指定
     * @return [type] [description]
     */
    public function extendSearchParams(){
        // 検索の値を格納する配列
        $search = array();

        // 検索値のセッションの確認
        if( SessionUtil::hasSearch() == True ){
            // 検索値を取得(セッション)
            $search = SessionUtil::getSearch();

            // エラーチェックフラグを取得する
            $errorCheck = SessionUtil::hasValidation();
            // 対象月が指定されていない時の処理
            if( empty( $search['inspection_ym_from'] ) == True &&  $errorCheck ==  false ){
                $search['inspection_ym_from'] = $this->selectedYm();
            }

        }else{
            // 対象月
            $search['inspection_ym_from'] = $this->selectedYm();
            
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
        
        // 初期状態の検索条件
        // 拠点コードを限定
        if( empty( $search['base_code'] ) ) {
            $search['base_code'] = config('original.default_office_code');
        }
        
        // 点検区分
        if( empty( $search['inspect_divs'] ) ) {
            $search['inspect_divs'] = '3';
        }
        
        return $search;
    }
    
    #######################
    ## Controller method
    #######################
    
}
