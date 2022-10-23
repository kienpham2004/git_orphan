<?php

namespace App\Original\Util;

use App\Lib\Util\DateUtil;

class ViewUtil {

    // つかってない
    public static function ymFromOpt( $span ) {
        $current = DateUtil::now();
        for( $i = 0; $i < $span; $i++ ) {
            $key = DateUtil::toYm( DateUtil::monthLater( $current, $i ) );
            $value = DateUtil::toJpDateYm( $key . "01" );
            $result[$key] = $value;
        }
        return $result;
    }

    public static function defaultYmOptions() {
        $start = DateUtil::nowMonthAgo(6);
        $start = date( "Y-m-01", strtotime( $start ) );
        $span = 13;
        for( $i = 0; $i < $span; $i++ ) {
            $key = DateUtil::toYm( DateUtil::monthLater( $start, $i ) );
            $value = DateUtil::toJpDateYm( $key . "01" );
            $result[$key] = $value;
        }
        return $result;
    }

    public static function ymOptions( $start, $span ) {
        for( $i = 0; $i < $span; $i++ ) {
            $key = DateUtil::toYm( DateUtil::monthLater( $start, $i ) );
            $value = DateUtil::toJpDateYm( $key . "01" );
            $result[$key] = $value;
        }
        return $result;
    }

    public static function genSelectTag( $options, $isEmptyRow=false, $default=null ) {
        $select = new ViewOrgSelect();
        if( $isEmptyRow ) {
            $options = static::addEmptyRow( $options );
        }
        $select->setOptions( $options );
        $select->setEmptyRow( $isEmptyRow );
        $select->setDefaultValue( $default );

        return $select;
    }

    public static function genSelectMultiTag( $options, $isEmptyRow=false, $default=null ) {
        $select = ViewUtil::genSelectTag( $options, $isEmptyRow );
        $select->setMulti(true);
        return $select;

    }

    public static function addEmptyRow( $options ) {
        // array_unshift, array_mergeだと添え字が振り直されるので
        // 以下のように変なことをしています
        // うまく表示されないのでコメントアウト
        //$options->prepend( '----', '' );
        
        // collectionオブジェクトの時の処理
        if( is_object( $options ) ){
            // 一度値を配列に変更
            $optionsAry = $options->all();
            
            $optionsAry = array_reverse( $optionsAry, true );
            $optionsAry[null] = '----';
            $optionsAry = array_reverse( $optionsAry, true );
            
            $options = collect( $optionsAry );
        }
        
        // 配列の時の処理
        if( is_array( $options ) ){
            $options = array_reverse( $options, true );
            $options[null] = '----';
            $options = array_reverse( $options, true );

        }

        return $options;
    }
}

class ViewOrgSelect {

    // タグ全般の要素の変数
    public $id;
    public $name;
    public $value;
    public $defaultValue;

    // selectタグの要素の変数
    public $isEmptyRow;
    public $options;
    public $isMulti = false;

    public function __construct( $id=null, $name=null, $value=null ) {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
    }

    ######################
    ## タグ全般用のメソッド
    ######################

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function getDefaultValue() {
        return $this->defaultValue;
    }

    public function setDefaultValue($value) {
        $this->defaultValue = $value;
    }

    ######################
    ## selectタグ用のメソッド
    ######################

    public function setEmptyRow($value) {
        return $this->isEmptyRow = $value;
    }

    public function isEmptyRow() {
        return $this->isEmptyRow;
    }

    public function setOptions($values) {
        $this->options = $values;
    }

    public function getOptions() {
        return $this->options;
    }

    public function setMulti($value) {
        $this->isMulti = $value;
    }

    public function isMulti() {
        return $this->isMulti;
    }
}
