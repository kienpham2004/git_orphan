<?php

namespace App\Commands\Honsya\Base;

use App\Models\Base;
use App\Commands\Command;
use App\Http\Requests\BaseRequest;

/**
 * 拠点情報を更新するコマンド
 *
 * @author yhatsutori
 */
class UpdateCommand extends Command{
    
    /**
     * コンストラクタ
     * @param [type]      $id         [description]
     * @param BaseRequest $requestObj [description]
     */
    public function __construct( $id, BaseRequest $requestObj ){
        $this->id = $id;
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 指定したIDのモデルオブジェクトを取得
        $baseMObj = Base::findOrFail( $this->id );
        
        // 更新する値の配列を取得
        $setValues = $this->requestObj->all();
		//0埋め処理
		$work = trim($setValues["base_code"]);
		if(mb_strlen($work) == 1){
            $work = "0".$work;
            $setValues["base_code"] = $work;
		}
        
        // 編集されたデータを持つモデルオブジェクトを取得
        $baseMObj->update( $setValues );
        
        // 削除が選択されている時
        if( isset( $setValues["del_flg"] ) == True ){
            $baseMObj->delete();            
        }
        
        return $baseMObj;
    }

}
