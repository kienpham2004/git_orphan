<?php

namespace App\Commands\Hoken\Tsuika;

use App\Models\Insurance;
use App\Commands\Command;
use App\Http\Requests\InsuranceRequest;

/**
 * 保険の追加を更新するコマンド
 *
 * @author yhatsutori
 */
class UpdateTsuikaCommand extends Command{
    
    /**
     * コンストラクタ
     * @param [type]      $id         [description]
     * @param InsuranceRequest $requestObj [description]
     */
    public function __construct( $id, InsuranceRequest $requestObj ){
        $this->id = $id;
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 指定したIDのモデルオブジェクトを取得
        $insuranceMObj = Insurance::findOrFail( $this->id );
        
        // 更新する値の配列を取得
        $setValues = $this->requestObj->all();
        
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
            
            // 編集されたデータを持つモデルオブジェクトを取得
            $insuranceMObj->update( $setValues );
        }

        return $insuranceMObj;
    }

}
