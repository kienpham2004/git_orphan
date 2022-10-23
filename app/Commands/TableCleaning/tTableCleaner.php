<?php

namespace App\Commands\TableCleaning;

use DB;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * テーブルの重複データ解消
 * 
 * @author ahmad
 */
trait tTableCleaner {
    
    /**
     * 重複データを検索
     * 
     * @param string $tableName テーブル名
     * @param object $item レコードデータ
     * @return array
     */
    private function getDuplicationRecord( $tableName, $equalFields, $fieldsType, $item ) {
        // 比較条件
        $fieldCount = count($equalFields) - 1;
        $i = 0;
        $criteria = '';
        // 比較条件を作成
        foreach ($equalFields as $idx => $field) {
            $value = $item->{$field};
            $value = str_replace("'", "''", $value);
            
            if (empty($item->{$field}) || $item->{$field} == NULL) {
                if ($fieldsType[$field] == 'integer') {
                    $criteria .= "({$field} IS NULL OR {$field} = 0)";
                } else if ($fieldsType[$field] == 'date') {
                    $criteria .= "({$field} IS NULL OR {$field} = '1970-01-01')";
                } else if ($fieldsType[$field] == 'datetime') {
                    $criteria .= "({$field} IS NULL OR {$field} = '1970-01-01 09:00:00')";
                } else {
                    $criteria .= "({$field} IS NULL OR {$field} = '{$value}')";
                }
            } else if ($fieldsType[$field] == 'datetime' && $item->{$field} == '1970-01-01 09:00:00') {
                $criteria .= "({$field} IS NULL OR {$field} = '1970-01-01 09:00:00')";
            } else if ($fieldsType[$field] == 'date' && $item->{$field} == '1970-01-01') {
                $criteria .= "({$field} IS NULL OR {$field} = '1970-01-01')";
            } else {
                $criteria .= "{$field} = '{$value}'";
            }
            if ($i < $fieldCount) {
                $criteria .= ' AND ';
            }
            $i++;
        }
        
        // 作成日時と更新日時の期間
        $margin = 5;
        $timestamp = strtotime( $item->created_at );
        $timeBefore = date( 'Y-m-d H:i:s', $timestamp - $margin );
        $timeAfter = date( 'Y-m-d H:i:s', $timestamp + $margin );
        $criteria .= " AND created_at >= '{$timeBefore}' AND created_at <= '{$timeAfter}'";
        $criteria .= " AND updated_at >= '{$timeBefore}' AND updated_at <= '{$timeAfter}'";
        $criteria .= " AND id <> '{$item->id}'";

        $sql = "SELECT * FROM {$tableName} WHERE {$criteria} LIMIT 1";
        
        return DB::select( $sql );
    }

    /**
     * 当日に更新されるレコードを収集
     * 
     * @param string $tableName テーブル名
     */
    private function getTodayUpdatedRecords( $tableName ) {
        // 当日に更新されるレコードを絞り込む
        $today = date('Ymd');
        $criteria = "to_char(created_at, 'yyyymmdd') = '{$today}' AND to_char(updated_at, 'yyyymmdd') = '{$today}'";
        $sql = "SELECT * FROM {$tableName} WHERE {$criteria}";
        
        return DB::select( $sql );
    }
    
    /**
     * 仮データを生成して、レコードを取得
     * 
     * @param string $tableName テーブル名
     */
    private function makeFakeDataAndGetRecords( $tableName ) {
        // 絞り込み
        $queries = array(
          'tb_result' => "SELECT * FROM tb_result 
            WHERE to_char(created_at, 'yyyymm') IN ('201902','201903','201904') 
            AND to_char(updated_at, 'yyyymm') IN ('201902','201903','201904')
            ORDER BY created_at ASC
            LIMIT 3000",
          'tb_result_cars' => "SELECT * FROM tb_result_cars
            WHERE to_char(created_at, 'yyyymm') IN ('201902','201904') 
            AND to_char(updated_at, 'yyyymm') IN ('201902','201904')
            ORDER BY created_at ASC
            LIMIT 2000",
          'tb_manage_info' => "SELECT * FROM tb_manage_info
            WHERE to_char(created_at, 'yyyymm') IN ('201903','201905') 
            AND to_char(updated_at, 'yyyymm') IN ('201903','201905')
            ORDER BY created_at ASC
            LIMIT 10000"
        );
        // 全部
//        $queries = array(
//          'tb_result' => "SELECT * FROM tb_result",
//          'tb_result_cars' => "SELECT * FROM tb_result_cars",
//          'tb_manage_info' => "SELECT * FROM tb_manage_info"
//        );
        return DB::select( $queries[$tableName] );
    }
    
    /**
     * テーブルのカラム名を収集
     * 
     * @param string $tableName テーブル名
     * @return array
     */
    private function getTableColumns( $tableName ) {
        return DB::getSchemaBuilder()->getColumnListing( $tableName );
    }
    
    /**
     * カラムのタイプを収集
     * 
     * @param string $tableName
     * @param array $fields
     * @return array
     */
    private function getTableFieldsType( $tableName, $fields ) {
        $types = array();
        foreach ( $fields as $fieldName ) {
            $fieldType = DB::connection()->getDoctrineColumn($tableName, $fieldName)->getType()->getName();
            $types[$fieldName] = $fieldType;
        }
        return $types;
    }
    
    /**
     * レコードを削除
     * 
     * @param string $tableName テーブル名
     * @param string $id ID
     * @return bool
     */
    private function deleteRecord( $tableName, $id, $checkId ) {
        $sql = "DELETE FROM {$tableName} WHERE id = '{$id}'";
        return DB::statement( $sql );
    }


    /**
     * チェックするカラム名を収集
     * 
     * @return array
     */
    private function collectEqualFields( $tableName, $fields ) {
        $exclusionFields = array('id', 'updated_at', 'created_at', 'updated_by',
            'deleted_at', 'created_by');
        $prefixes = array(
            'tb_manage_info' => 'mi',
            'tb_result' => 'rst',
            'tb_result_cars' => 'rstc',
            'tb_target_cars' => 'tgc',
        );
        $dupCheckHelpers = ['is_duplicated', 'is_checked', 'check_id', 'delete_target', 'checked_at'];
        foreach ($dupCheckHelpers as $fieldName) {
            $fieldName = $prefixes[$tableName] . '_' . $fieldName;
            $exclusionFields[] = $fieldName;
        }
        return $this->removeArrayItemByValue( $fields, $exclusionFields );
    }
    
    /**
     * 値で配列のデータを削除
     * 
     * @param array $arr 配列データ
     * @param array $values 値リスト
     * @return array
     */
    private function removeArrayItemByValue( $arr, $values ) {
        foreach ( $values as $value ) {
            $offset = array_search( $value, $arr );
            array_splice( $arr, $offset, 1 );
        }
        return $arr;
    }


    /**
     * バックアップファイルを書き込み
     */
    private function appendFileContent( $myFile, $stringData ) {
        $stringData .= "\n";
        $fh = fopen($myFile, 'a') or die("can't open file");
        fwrite($fh, $stringData);
        fclose($fh);
    }
    
    /**
     * メッセージを表示
     * @param string $message
     */
    private function comment( $message ) {
        print("{$message}\n");
    }

    /**
     * レコードのバックアップのクエリーを作成
     * @param object $item レコード
     * @return string
     */
    private function buildBackupSql( $tableName, $fields, $item ) {
        $copyKeys = '';
        $copyValues = '';
        $index = 0;
        $fieldCount = count($fields) - 1;

        foreach ($fields as $key) {
            $copyKeys .= $key;
            $value = $item->{$key};
            $value = str_replace("'", "''", $value);
            
            if (!empty($value)) {
                $value = str_replace("'", "''", $value);
                $copyValues .= "'{$value}'";
            } else {
                $copyValues .= "NULL";
            }
            if ($index < $fieldCount) {
                $copyKeys .= ', ';
                $copyValues .= ', ';
            }
            $index++;
        }
        
        return "INSERT INTO {$tableName}({$copyKeys}) VALUES({$copyValues});";
    }

    /**
     * サンプルとしてレコードを重複する関数
     * @param object $item レコード
     */
    private function duplicateRecord( $tableName, $fields, $item )
    {
        $copyKeys = '';
        $copyValues = '';
        $i = 0;
        $count = count($fields) - 1;
        foreach ($fields as $key) {
            
            if ($key == 'id') {
                $i++;
                continue;
            }
            $copyKeys .= "{$key}";
            
            $value = $item->{$key};

            if (!empty($value)) {
                if ($key == 'updated_at') {
                    $copyValues .= "'{$item->created_at}'";
                } else {
                    $value = str_replace("'", "''", $value);
                    $copyValues .= "'{$value}'";
                }
            } else {
                $copyValues .= "NULL";
            }
            if ($i < $count) {
                $copyKeys .= ', ';
                $copyValues .= ', ';
            }
            $i++;
        }
        
        $sql = "INSERT INTO {$tableName}({$copyKeys}) VALUES({$copyValues})";
        
        DB::statement( $sql );
    }
    
    /**
     * チェックのフラグをリセット
     */
    public function resetCheckFlag( $tableName ) {
        // 絞り込み
        $queries = array(
          'tb_target_cars' => "UPDATE tb_target_cars SET tgc_check_id = NULL WHERE tgc_check_id IS NOT NULL ",
          'tb_result' => "UPDATE tb_result SET rst_check_id = NULL WHERE rst_check_id IS NOT NULL ",
          'tb_result_cars' => "UPDATE tb_result_cars SET rstc_check_id = NULL WHERE rstc_check_id IS NOT NULL ",
          'tb_manage_info' => "UPDATE tb_manage_info SET mi_check_id = NULL WHERE mi_check_id IS NOT NULL ",
        );
        DB::statement( $queries[$tableName] );
    }
}