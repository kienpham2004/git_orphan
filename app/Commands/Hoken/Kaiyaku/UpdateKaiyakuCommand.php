<?php

namespace App\Commands\Hoken\Kaiyaku;

use App\Models\InsuranceKaiyaku;
use App\Commands\Command;
use App\Http\Requests\InsuranceKaiyakuRequest;

/**
 * 保険の追加を更新するコマンド
 *
 * @author yhatsutori
 */
class UpdateKaiyakuCommand extends Command{
    
    /**
     * コンストラクタ
     * @param [type]      $id         [description]
     * @param InsuranceKaiyakuRequest $requestObj [description]
     */
    public function __construct( $id, InsuranceKaiyakuRequest $requestObj ){
        $this->id = $id;
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 指定したIDのモデルオブジェクトを取得
        $insuranceMObj = InsuranceKaiyaku::findOrFail( $this->id );
        
        // 更新する値の配列を取得
        $setValues = $this->requestObj->all();
        
        // 異動・変更日が空の時値をNULLにする
        if( empty( $setValues["insu_change_date"] ) == True ){
            $setValues["insu_change_date"] = NULL;
        }

        // 編集されたデータを持つモデルオブジェクトを取得
        $insuranceMObj->update( $setValues );
                
        return $insuranceMObj;
    }

}
