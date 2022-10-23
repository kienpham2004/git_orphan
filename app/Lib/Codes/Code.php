<?php

namespace App\Lib\Codes;

abstract class Code {

    private $values;

    /**
     * コンストラクタ
     * @param [type] $codes [description]
     */
    public function __construct($codes) {
        $this->values = collect($codes);
    }

    /**
     * 値の取得
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    public function getValue($code) {
        return $this->values->get($code);
    }

    /**
     * 値の一覧を取得
     * @return [type] [description]
     */
    public function getVaues() {
        return $this->values;
    }

    /**
     * 値の一覧を配列で取得
     * @return [type] [description]
     */
    public function getOptions() {
        return $this->getVaues()->toArray();
    }

    /**
     * 配列を取得
     * @return [type] [description]
     */
    public function getArray() {
        return $this->values->toArray();
    }

    /**
     * キーの一覧を配列で取得
     * @return [type] [description]
     */
    public function getCode($name) {
        return  array_search($name, $this->getVaues()->toArray());
    }

    /**
     * キーの一覧を配列で取得
     * @return [type] [description]
     */
    public function getCodes() {
        $filter = array("[", "\"", "]");
        $keys = str_replace($filter, "", $this->values->keys());
        return explode(',', $keys);
    }

    /**
     * 使ってない？
     * @return [type] [description]
     */
    public static function getDefault() {
        return null;
    }
    
    /**
     * 使ってない？
     * @return boolean [description]
     */
    public static function isLastValue() {
        return true;
    }
    
}
