<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 入力値の取得とエラーチェック
 *
 * @author yhatsutori
 *
 */
class SearchRequest extends FormRequest {

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
//        // リクエストを取得
//        $request = $this->request->all();

        $rules = [
            'row_num' => ''
        ];

//        // 車検年月チェック
//        if( isset( $request['inspection_ym_from'] ) == True ){
//            $rules['inspection_ym_from'] = 'required';
//        }
        return $rules;
    }

//    public function messages()
//    {
//        // エントリーフォームの入力値確認
//        $messages = [
//            'inspection_ym_from.required' => '車検年月を入力してください。',
//        ];
//        return $messages;
//    }

    /**
     * [getInstance description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public static function getInstance( $array=null ) {
        $request = static::setCommonCondition();

        if( !empty( $array ) ) {
            foreach ( $array as $key => $value ) {
                $request->{$key} = $value;
            }
        }
        
        return $request;
    }

    /**
     * [setCommonCondition description]
     */
    private static function setCommonCondition() {

        $request = new SearchRequest();
        $request->row_num = 20;

        return $request;
    }
    
}
