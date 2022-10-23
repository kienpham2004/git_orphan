<?php

namespace App\Commands\Hoken\Tsuika;

use App\Account\Account;
use App\Lib\Util\DateUtil;
use App\Original\Util\SessionUtil;
use App\Models\Base;
use App\Models\UserAccount;
use App\Models\Insurance;
use App\Commands\Command;
use App\Http\Requests\InsuranceRequest;

/**
 * 保険の追加を新規作成するコマンド
 *
 * @author yhatsutori
 */
class CreateTsuikaCommand extends Command{
    
    /**
     * コンストラクタ
     * @param InsuranceRequest $requestObj [description]
     */
    public function __construct( InsuranceRequest $requestObj ){
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){        
        // 追加する値の配列を取得
        $setValues = $this->requestObj->all();
        
        // 新中区分名称
        $setValues["insu_jisya_tasya"] = "純新規";
        
        // 保険終期が空の時値をNULLにする
        if( empty( $setValues["insu_insurance_end_date"] ) == True ){
            $setValues["insu_insurance_end_date"] = NULL;
        }

        // 獲得状況を0にする
        if( empty( $setValues["insu_status_gensen"] ) == True ){
            $setValues["insu_status_gensen"] = 0;
        }
        
        // 獲得済の時に日付を追加
        if( in_array( $setValues["insu_status"], ["6"] ) == True ){
            // 現在日時を指定
            $setValues["insu_add_tetsuduki_date"] = date("Y-m-d");

        }else{
            // 空の値を指定
            $setValues["insu_add_tetsuduki_date"] = NULL;
            
        }

        // 獲得源泉がその他以外の時
        if( $setValues["insu_status_gensen"] != "3" ){
            // 状況の詳細を空にする
            $setValues["insu_status_gensen_detail"] = NULL;
        }

        // スタッフ情報トス
        // チェックボックスが選択されていないときデフォルト値に0を入れる
        if( !isset( $setValues["insu_staff_info_toss"] ) == True || empty( $setValues["insu_staff_info_toss"] ) == True ){
            $setValues["insu_staff_info_toss"] = 0;
        }
        
        // スタッフ情報のトスが無の時
        if( $setValues["insu_staff_info_toss"] == 0 ){
            // スタッフ名を空にする
            $setValues["insu_toss_staff_name"] = NULL;
        }
        
        // 保険終期が空でない時に登録処理
        if( !empty( $setValues["insu_insurance_end_date"] ) == True ){
            // 満了月
            $setValues["insu_inspection_ym"] = date( "Ym", strtotime( $setValues["insu_insurance_end_date"] ) );
            // 対象月を取得
            $setValues["insu_inspection_target_ym"] = $setValues["insu_inspection_ym"];
            // 計上予定月を取得
            $setValues["insu_contact_keijyo_ym"] = $setValues["insu_inspection_ym"];

            // 拠点名を拠点コードから格納
            //$setValues["insu_base_code"] = $setValues["base_code"];
            //$setValues["insu_base_name"] = Base::options()[$setValues["base_code"]];
            // 担当者情報
            $setValues["insu_user_id"] = $setValues["user_id"];
            //$setValues["insu_user_name"] = UserAccount::options()[$setValues["user_id"]];
            
            // 既に登録されていないかを確認
            $insuranceCnt = Insurance::where('insu_inspection_ym', '=', $setValues["insu_inspection_ym"])
                                     ->where('insu_customer_name', '=', $setValues["insu_customer_name"])
                                     ->where('insu_syadai_number', '=', $setValues["insu_syadai_number"])
                                     ->count();
            
            // 同じ対象月、車台番号、契約者名の人がいない時に登録処理
            if( $insuranceCnt == 0 ){
                // ユーザー情報を取得(セッション)
                $loginAccountObj = SessionUtil::getUser();
                $user_id = $loginAccountObj->getUserId();

                // 登録されたデータを持つモデルオブジェクトを取得
                $insuranceMObj = Insurance::create( $setValues );

                return $insuranceMObj;
            }
        }

        return NULL;
    }

}
