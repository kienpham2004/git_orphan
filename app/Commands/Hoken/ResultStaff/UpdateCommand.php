<?php

namespace App\Commands\Hoken\ResultStaff;

use App\Models\Hoken\InsuranceStaffDB;
use App\Commands\Command;
use App\Http\Requests\SearchRequest;

/**
 * 個人販売計画登録を更新するコマンド
 *
 * @author yhatsutori
 */
class UpdateCommand extends Command{
    
    /**
     * コンストラクタ
     * @param [type]      $id         [description]
     * @param SearchRequest $requestObj [description]
     */
    public function __construct( SearchRequest $requestObj ){
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        //
        $requestObj = $this->requestObj;

        // 検索条件を配列で取得
        $search = $requestObj->all();
        
        // 編集の時に動作
        if( $requestObj->action == "edit" && $requestObj->year != "" ){
            // 計画の値を取得
            foreach( $search as $key => $value ){
                // キーの値の出力
                //echo $key;
                if ( strstr( $key, 'plan_' ) == True ) {
                    // inputタグの値を分割
                    $splitKey = explode( "_", $key );
                    // 月の値を取得
                    $month = $splitKey[1];
                    //拠点コードを取得
                    $base_code = $splitKey[2];

                    // 値が空の時の動作
                    if( empty( $value ) == True ){
                        $value = 0;
                    }
                    
                    // 値の検索または追加
                    if( !empty( $base_code ) == True && !empty( $month ) == True ){
                        // 成約計画の値を更新
                        InsuranceStaffDB::updatePlanList(
                            $value, $base_code, $requestObj->year, $month
                        );

                    }
                    
                }
            }
        }

    }

}
