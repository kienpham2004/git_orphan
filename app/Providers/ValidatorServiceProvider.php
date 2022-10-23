<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Lib\Util\ReflectionUtil;

class ValidatorServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        \Validator::resolver(function($translator, $data, $rules, $messages) {
            return new CustomValidator($translator, $data, $rules, $messages);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}

class CustomValidator extends \Illuminate\Validation\Validator {

    /**
     * [validateChangeUnique description]
     * @param  [type] $attribute  [description]
     * @param  [type] $value      [description]
     * @param  [type] $parameters [description]
     * @return [type]             [description]
     */
    public function validateChangeUnique($attribute, $value, $parameters) {

        /*
         * parameters
         * 0: テーブル名
         * 1: カラム名（引き当て先）
         * 2: 条件オペランド
         */
        $original = \Input::get($attribute.'_original');

        $items = explode('@', $parameters[0]);
        $class = $items[0];
        $method = $items[1];
        if($original != $value) {
            return ReflectionUtil::invoke($class, $method, $value);
        }
        return true;
    }

	/**
     * [validateIsAlnum description]
     * @param  [type] $attribute  [description]
     * @param  [type] $value      [description]
     * @param  [type] $parameters [description]
     * @return [type]             [description]
     */
    public function validateIsAlnum($attribute, $value, $parameters) {

        /*
         * parameters
         * 0: テーブル名
         * 1: カラム名（引き当て先）
         * 2: 条件オペランド
         */
        $original = \Input::get($attribute.'_original');

        $items = explode('@', $parameters[0]);
        $class = $items[0];
        $method = $items[1];
        if($original != $value) {
            return ReflectionUtil::invoke($class, $method, $value);
        }
        return true;
    }
}
