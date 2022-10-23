<?php

namespace App\Commands\Hoken\Kaiyaku;

use App\Lib\Util\DateUtil;
use App\Original\Util\SessionUtil;
use App\Models\Base;
use App\Models\UserAccount;
use App\Models\InsuranceKaiyaku;
use App\Commands\Command;
use App\Http\Requests\InsuranceKaiyakuRequest;

/**
 * 保険の追加を新規作成するコマンド
 *
 * @author yhatsutori
 */
class CreateKaiyakuCommand extends Command{
    
    /**
     * コンストラクタ
     * @param InsuranceKaiyakuRequest $requestObj [description]
     */
    public function __construct( InsuranceKaiyakuRequest $requestObj ){
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){        
        // 追加する値の配列を取得
        $setValues = $this->requestObj->all();
        
        // 異動・変更日が空の時値をNULLにする
        if( empty( $setValues["insu_change_date"] ) == True ){
            $setValues["insu_change_date"] = NULL;
        }

        // 保険終期が空でない時に登録処理
        if( !empty( $setValues["insu_change_date"] ) == True ){
            // 既に登録されていないかを確認
            $insuranceCnt = InsuranceKaiyaku::where('insu_change_date', '=', $setValues["insu_change_date"])
                                     ->where('insu_customer_name', '=', $setValues["insu_customer_name"])
                                     ->where('insu_syoken_number', '=', $setValues["insu_syoken_number"])
                                     ->count();
            
            // 同じ対象月、車台番号、契約者名の人がいない時に登録処理
            if( $insuranceCnt == 0 ){
                // ユーザー情報を取得(セッション)
                $loginAccountObj = SessionUtil::getUser();
                $user_id = $loginAccountObj->getUserId();

                // 登録されたデータを持つモデルオブジェクトを取得
                $insuranceMObj = InsuranceKaiyaku::create( $setValues );

                return $insuranceMObj;
            }
        }

        return NULL;
    }

}
