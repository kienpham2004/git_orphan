<?php

namespace App\Lib\Util;

/**
 * ・・・関連のユーティリティー
 *
 * @author yhatsutori
 *
 */
class ReflectionUtil {

    /**
     * インタフェースをインプリメントしているか確認する
     *
     * @param unknown $object
     * @param unknown $ifname
     * @return boolean
     */
    public static function isImplements( $object, $ifname ) {
        $implements = class_implements( $object, $ifname );
        foreach( $implements as $impl ) {
            $fullName = explode( '\\', $impl );
            $name = $fullName[count( $fullName ) -1];
            \Log::debug( $fullName );
            \Log::debug( $name );
            if( $ifname == $name ) {
                return true;
            }
        }
        return false;
    }

    /**
     * クラス名文字列からインスタンスを生成する
     *
     * @param unknown $classsName
     * @return unknown
     */
    public static function instance( $className ) {
        $class = new \ReflectionClass( $className );
        return new $class;
    }

    /**
     * クラス名文字列からインスタンスを生成し、
     * メソッド名文字列からそのインスタンスのメソッドを実行する
     * 戻り値は実行したメソッドに応じる
     *
     * @param string $className
     * @param string $methodName
     * @param string $arg
     * @return mixed
     */
    public static function invoke( $className, $methodName, $arg ) {
        //$class = static::instance($className);
        $method = new \ReflectionMethod( $className, $methodName );
        return $method->invoke( new $className, $arg );
    }

    // 動作未確認(必要になったら確認します）
    public static function invokeArgs( $className, $methodName, $args ) {
        //$class = static::instance($classsName);
        $method = new \ReflectionMethod( $className, $methodName );
        return $method->invokeArgs( new $className, $args );
    }
}
