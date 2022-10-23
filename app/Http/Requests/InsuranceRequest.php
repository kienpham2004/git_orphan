<?php

namespace App\Http\Requests;

use App\Http\Requests\SearchRequest;

/**
 * 保険の登録・編集用の入力値の取得とエラーチェック
 *
 * @author yhatsutori
 *
 */
class InsuranceRequest extends SearchRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // リクエストを取得
        $request = $this->request->all();

        $rules = [
            //'user_id' => 'required|unique:tb_user_account'
            'base_code' => 'required|max:2',
            'user_id' => 'required',
            'insu_customer_name' => 'required',
            'insu_insurance_end_date' => 'required',
            'insu_syadai_number' => 'required',
            'insu_status' => 'required',
            'insu_status_gensen' => 'required',
        ];
        
        // 情報トスが有の時
        if( isset( $request['insu_staff_info_toss'] ) == True && $request['insu_staff_info_toss'] == "1" ){
            // スタッフ名が選択されていないとエラー
            if( $request['insu_toss_staff_name'] == "" ){
                $rules['insu_toss_staff_name'] = 'required';
            }

        }

        return $rules;
    }
    
    public function messages()
    {
        // エントリーフォームの入力値確認
        $messages = [
            'base_code.required' => '拠点を入力してください。',
            'user_id.required' => '担当者名を入力してください。',
            'insu_customer_name.required' => '契約者名を入力してください。',
            'insu_insurance_end_date.required' => '獲得推進予定日を入力してください。',
            'insu_syadai_number.required' => '車台番号を入力してください。',
            'insu_status.required' => '獲得状況を選択してください。',
            'insu_status_gensen.required' => '新規区分を選択してください。',

            'insu_toss_staff_name.required' => '情報トススタッフ名を入力してください。',
        ];

        return $messages;
    }

}
