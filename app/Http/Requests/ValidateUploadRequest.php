<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Factory;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;

/**
 * Class ValidateUploadRequest
 * @package App\Http\Requests
 * upload file validation
 */
class ValidateUploadRequest
{
    private $validator;

    private $rules = [
        'csv_extension' => 'in:csv'//CSVファイルだけ評価されるルール
    ];

    //constructor
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * ファイルをvalidateの処理
     *
     * @param $file
     * @return \Illuminate\Validation\Validator
     */
    public function validate( $file ) {
        $csv_extension = $file->getClientOriginalExtension();//ファイルの拡張子を取得
        //validation array
        $validation_array = [
            'csv_extension' => $csv_extension//CSV拡張子のチェック
        ];

        //error messages
        $messages = [
            //CSVではないファイルを掲載の場合のmessage
            'in' => 'CSVファイルをアップロードしてください。',
        ];

        return $this->validator->make(
            $validation_array,//validateしたい値
            $this->rules,//ルール
            $messages//error messages
        );
    }

}