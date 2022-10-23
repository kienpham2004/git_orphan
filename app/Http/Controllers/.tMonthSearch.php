<?php
//
//namespace App\Http\Controllers;
//
//use App\Lib\Util\DateUtil;
//
///**
// * 前月・当月・次月
// */
//trait tMonthSearch {
//
//    public function prevRequest($requestObj) {
//        $requestObj->{$this->targetItemName()} = DateUtil::monthAgo(
//            $requestObj->{$this->targetItemName()} . '01' , 1, 'Ym'
//        );
//
//        return $requestObj;
//    }
//
//    public function nextRequest($requestObj) {
//        $requestObj->{$this->targetItemName()} = DateUtil::monthLater(
//            $requestObj->{$this->targetItemName()} . '01' , 1, 'Ym'
//        );
//
//        return $requestObj;
//    }
//
//    public function currentRequest($requestObj) {
//        $requestObj->{$this->targetItemName()} = DateUtil::currentYm();
//
//        return $requestObj;
//    }
//
//    public function simpleSearchCondition($requestObj) {
//        $search = $requestObj->all();
//        $search[$this->targetItemName()] = $requestObj->{$this->targetItemName()};
//
//        return $search;
//    }
//
//    protected abstract function targetItemName();
//}
