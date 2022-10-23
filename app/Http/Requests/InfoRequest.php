<?php

namespace App\Http\Requests;

use App\Http\Requests\SearchRequest;

/**
 * お知らせの登録・編集用の入力値の取得とエラーチェック
 *
 * @author yhatsutori
 *
 */
class InfoRequest extends SearchRequest {

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
            'info_target_date' => 'required',
            'info_user_id' => 'required',
            'info_title' => 'required|max:100',
            'info_body' => 'required|max:300'
        ];

        // 記載期間チェック
        if(isset( $request['info_view_date_min'] ) && isset( $request['info_view_date_max'] )){
            if (str_replace('/','-',$request['info_view_date_min']) != str_replace('/','-',$request['info_view_date_max'])){
                $rules['info_view_date_max'] = 'after:info_view_date_min';
            }
        }

        return $rules;
    }

    public function messages()
    {
        // エントリーフォームの入力値確認
        $messages = [
            'info_target_date.required' => '対象日を入力してください。',
            'info_user_id.required' => '記載者を入力してください。',
            'info_title.required' => 'タイトルを入力してください。',
            'info_body.required' => '内容を入力してください。',
            'info_view_date_max.after' => '正しい掲載期間を入力してください。',
        ];

        return $messages;
    }

}
