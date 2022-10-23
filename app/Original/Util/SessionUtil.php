<?php

namespace App\Original\Util;

use Session;

class SessionUtil {

    /**
     * セッションを登録する
     * @param  [type] $session セッションキー
     * @param  [type] $value セッション値
     * @return [type]        [description]
     */
    public static function put($session, $value ) {
        Session::put($session, $value);
    }

    /**
     * セッションを取得する
     * @param  [type] $session セッションキー
     * @return [type] [description]
     */
    public static function get($session) {
        return Session::get($session);
    }

    /**
     * ユーザー情報を削除(セッション)
     * @return [type] [description]
     */
    public static function remove($session) {
        Session::forget($session);
    }

    /**
     * 検索値のセッションを保持するかを調べる
     * @return boolean [description]
     */
    public static function has($session){
        if (Session::has($session)) {
            return true;
        } else {
            return false;
        }
    }

    ######################
    ## user_info
    ######################
    
    /**
     * ユーザー情報を登録(セッション)
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public static function putUser( $value ) {
        Session::put('user_info', $value);
    }

    /**
     * ユーザー情報を取得(セッション)
     * @return [type] [description]
     */
    public static function getUser() {
        return Session::get('user_info');
    }

    /**
     * ユーザー情報を削除(セッション)
     * @return [type] [description]
     */
    public static function removeUser() {
        Session::forget('user_info');
    }

    ######################
    ## sort
    ######################

    /**
     * 並び順を登録(セッション)
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public static function putSort( $value ) {
        Session::put('sort', $value);
    }
    
    /**
     * 並び順を取得(セッション)
     * @return [type] [description]
     */
    public static function getSort() {
        return Session::get('sort');
    }

    /**
     * 並び順を削除(セッション)
     * @return [type] [description]
     */
    public static function removeSort() {
        Session::forget('sort');
    }

    ######################
    ## search
    ######################
    
    /**
     * 検索値を登録(セッション)
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public static function putSearch( $array ) {
        Session::put('search', $array);
    }

    /**
     * 検索値を取得(セッション)
     * @return [type] [description]
     */
    public static function getSearch() {
        return Session::get('search');
    }

    /**
     * 検索値を削除(セッション)
     * @return [type] [description]
     */
    public static function removeSearch() {
        Session::forget('search');
    }

    /**
     * 検索値のセッションを保持するかを調べる
     * @return boolean [description]
     */
    public static function hasSearch(){
        if ( Session::has('search') ) {
            return true;
        } else {
            return false;
        }
    }


    ######################
    ## チェックエラー
    ######################

    /**
     * チェックエラーを登録(セッション)
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public static function putValidation( $array ) {
        Session::put('validationError', $array);
    }

    /**
     * チェックエラーを取得(セッション)
     * @return [type] [description]
     */
    public static function getValidation() {
        return Session::get('validationError');
    }

    /**
     * チェックエラーを削除(セッション)
     * @return [type] [description]
     */
    public static function removeValidation() {
        Session::forget('validationError');
    }

    /**
     * チェックエラーを保持するかを調べる
     * @return boolean [description]
     */
    public static function hasValidation(){
        if ( Session::has('validationError') ) {
            return true;
        } else {
            return false;
        }
    }

    ######################
    ## remove
    ######################
    
    /**
     * 検索情報と並び替え情報を削除
     * @return [type] [description]
     */
    public static function removeSession() {
        // 検索情報を初期化
        Session::forget('search');
        // 並び替え情報を初期化
        Session::forget('sort');
    }

}
