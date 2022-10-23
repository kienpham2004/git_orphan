<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * CSVアップロード時の入力値の取得とエラーチェック
 *
 * @author yhatsutori
 *
 */
class CsvUploadRequest extends FormRequest {

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
            'file_type' => 'required',
//            'csv_file' => 'required|mimes:csv,txt'    // mimes: csvがうまく動作しない
            'csv_file' => 'required'    // mimes: csvがうまく動作しない
        ];
    }

    public function messages()
    {
        // エントリーフォームの入力値確認
        $messages = [
            'file_type.required' => 'CSVファイルの種類を選択してください。',
            'csv_file.required' => 'CSVファイルを選択してください。'
//            'csv_file.mimes' => 'CSVファイルをアップロードしてください。'
        ];

        return $messages;
    }
}
