<?php

namespace App\Commands\Honsya\Base;

use App\Models\Base;
use App\Commands\Command;
use App\Http\Requests\BaseRequest;

/**
 * 拠点を追加する処理
 *
 * @author yhatsutori
 */
class CreateCommand extends Command{
    
    /**
     * コンストラクタ
     * @param BaseRequest $requestObj [description]
     */
    public function __construct( BaseRequest $requestObj ){

        $this->requestObj = $requestObj;
  
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 追加する値の配列を取得
        $setValues = $this->requestObj->all();
		//0埋め処理
		$work = trim($setValues["base_code"]);
		if(mb_strlen($work) == 1){
            $work = "0".$work;
            $setValues["base_code"] = $work;
		}
        // 登録されたデータを持つモデルオブジェクトを取得
        $baseMObj = Base::create( $setValues );

        return $baseMObj;
    }
    
}
