<?php

namespace App\Http\Requests;

use App\Http\Requests\SearchRequest;

/**
 * 拠点の登録・編集用の入力値の取得とエラーチェック
 *
 * @author yhatsutori
 *
 */
class BaseRequest extends SearchRequest {

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
        $rules = [
            'base_code' => 'required|max:2|changeUnique:App\Models\Base@unique',
            'base_name' => 'required',
            'base_short_name' => 'required',
            'work_level' => 'required'
        ];
        
        return $rules;
    }
    
    public function messages()
    {
        // エントリーフォームの入力値確認
        $messages = [
            'base_code.required' => '拠点コードを入力してください。',
            'base_name.required' => '拠点名を入力してください。',
            'base_short_name.required' => '拠点略称を入力してください。',
            'work_level.required' => '新中区分を入力してください。'
        ];
        
        return $messages;
    }

}
