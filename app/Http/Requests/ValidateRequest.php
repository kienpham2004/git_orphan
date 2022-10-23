<?php


namespace App\Http\Requests;

use Illuminate\Validation\Factory;
use App\Lib\Util\Constants;
use App\Original\Util\SessionUtil;

/**
 * Class ValidateUploadRequest
 * @package App\Http\Requests
 *
 */
class ValidateRequest
{
    private $validator;

    //constructor
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * validateの処理を行う
     *
     * @param $file
     * @return \Illuminate\Validation\Validator
     */
    public function validate( $search, $yearMonthName = "車検年月" ) {
        $rules = ['inspection_ym_from' => 'required'];
        //validation array
        $validation_array = ['inspection_ym_from' => $search['inspection_ym_from']];
        //error messages
        $messages = ['inspection_ym_from.required' => $yearMonthName.'を入力してください。'];

        // 車検回数から
        $flagCarTimesError = false; // 両方エラーの場合、メッセージは１回がでる。
        if( isset( $search['car_times_1']) &&  !is_numeric($search['car_times_1']) && $search['car_times_1'] != ""){
            $rules['car_times_1'] = 'numeric';
            $validation_array['car_times_1'] = $search['car_times_1'];
            $messages['car_times_1.numeric'] = '車検回数は半角数字を入力してください。';
            $flagCarTimesError = true;
        }
        // 車検回数まで
        if( isset( $search['car_times_2']) &&  !is_numeric($search['car_times_2']) && $flagCarTimesError == false){
            $rules['car_times_2'] = 'numeric';
            $validation_array['car_times_2'] = $search['car_times_2'];
            $messages['car_times_2.numeric'] = '車検回数は半角数字を入力してください。';
        }

        return $this->validator->make(
            $validation_array,//validateしたい値
            $rules,//ルール
            $messages//error messages
        );
    }
}