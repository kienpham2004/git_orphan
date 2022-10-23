<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ログイン認証の入力値の取得とエラーチェック
 *
 * @author yhatsutori
 *
 */
class LoginRequest extends FormRequest {

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
        return [
            'id' => 'required',
            'password' => 'required'
        ];
    }
    
    public function messages()
    {
        // エントリーフォームの入力値確認
        $messages = [
            'id.required' => 'ログインIDを入力してください。',
            'password.required' => 'パスコードを入力してください。'
        ];
        
        return $messages;
    }

}
