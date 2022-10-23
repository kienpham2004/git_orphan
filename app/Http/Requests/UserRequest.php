<?php

namespace App\Http\Requests;

use App\Http\Requests\SearchRequest;

/**
 * 担当者の登録・編集用の入力値の取得とエラーチェック
 *
 * @author yhatsutori
 *
 */
class UserRequest extends SearchRequest {

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
        $request = $this->request->all();
        $rules = [
            //'user_id' => 'required|unique:tb_user_account'
            'user_login_id' => 'required|changeUnique:App\Models\UserAccount@unique_login_id|isAlnum:App\Models\UserAccount@is_alnum',
            'user_password' => 'required|isAlnum:App\Models\UserAccount@is_alnum',
            'user_name' => 'required',
            'account_level' => 'required',
            'base_id' => 'required',
            //画像チェック
            'face-image' => 'mimes:jpeg,jpg,png'
        ];
        if ($request['type'] == "create") {
            $rules['user_code'] = 'required|max:3|changeUnique:App\Models\UserAccount@unique|isAlnum:App\Models\UserAccount@is_alnum';
        }
        return $rules;
    }
    
    public function messages()
    {
        // エントリーフォームの入力値確認
        $messages = [
            'user_code.required' => '担当者コードを入力してください。',
            'user_login_id.required' => 'ログインIDを入力してください。',
            'user_password.required' => 'パスコードを入力してください。',
            'user_name.required' => '担当者を入力してください。',
            'account_level.required' => '機能権限を入力してください。',
            'base_id.required' => '拠点コードを入力してください。',
            'face-image.mimes' => '顔写真はjpeg,jpg,pngをアップロードしてください'
        ];

        return $messages;
    }

}
