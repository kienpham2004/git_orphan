<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Commands\TableCleaning;

use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use DB;

/**
 * Description of ManageInfoNotFullCommand
 *
 * @author ohishi
 */
class ResultCarsNotFullCommand extends Command implements ShouldBeQueued {
    
    use tTableCleaner;
    
    /**
     * 今日のチェック
     * 
     * @var int
     */
    const CHECKING_TODAY = 0;
    
    /**
     * 全てチェック
     * 
     * @var int
     */
    const CHECKING_ALL   = 1;

    /**
     * The table name
     * 
     * @var string
     */
    private $tableName = 'tb_result_cars';
    
    /**
     * The prefix of table
     * 
     * @var string
     */
    private $tablePrefix = 'rstc';

    /**
     * 値が同じの比較
     */
    private $equalFields = [
        'rstc_manage_number',
        'rstc_inspection_ym',
        'rstc_inspection_id',
    ];

    /**
     * 重複データのフラグリスト
     * 
     * @var array
     */
    private $duplicated_data = array();
    
    /**
     * クラスのコンストラクタ
     * 
     * @param int $checkingType チェックのタイプ
     */
    public function __construct( $checkingType = self::CHECKING_ALL ) {
        // チェックのタイプ
        $this->checkingType = $checkingType;
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '8196M');

        $this->comment(date('Y-m-d H:i:s') . ' Duplication check started');
        
        // チェックフラグをリセット
        $this->resetCheckFlag( $this->tableName );
        
        // チェックのタイプ判定
        if ( $this->checkingType == self::CHECKING_TODAY ) {
            $sql = "SELECT * FROM {$this->tableName}";
            $sql .= " WHERE true ";
            // 当日に更新されるレコードを絞り込む
            $today = date('Ymd');
            $sql .= "AND to_char(created_at, 'yyyymmdd') = '{$today}'";

            $sql .= " ORDER BY created_at DESC, id DESC";
        } else if ( $this->checkingType == self::CHECKING_ALL ) {
            $sql = "select * from 
                (
                    select * from {$this->tableName}
                    where
                    (rstc_inspection_id, rstc_inspection_ym, rstc_manage_number) in
                    (
                        select rstc_inspection_id, rstc_inspection_ym, rstc_manage_number from {$this->tableName}
                        group by rstc_inspection_id, rstc_inspection_ym, rstc_manage_number
                        having count(rstc_inspection_id) > 1
                    )
                    order by id, rstc_inspection_id, rstc_inspection_ym, rstc_manage_number
                ) as x";
        }

        $items = DB::select( $sql );
        $total = count($items);
        $this->comment("Checking {$total} data ...");
        
        foreach ($items as $index => $item) {
            if (isset($this->duplicated_data[$item->id])) {
                continue;
            }

            // 重複データの絞り込み条件を作成
            $criteria = "({$this->tablePrefix}_is_checked IS NULL OR ({$this->tablePrefix}_is_checked IS NOT NULL AND {$this->tablePrefix}_is_duplicated IS NULL)) AND ";
            $filteredFields = array();
            foreach ($this->equalFields as $i => $field) {
                $value = $item->{$field};
                if ($value != NULL) {
                    $filteredFields[$field] = str_replace("'", "''", $value);
                }
            }
            $fieldCount = count($filteredFields) - 1;
            $i = 0;
            foreach ($filteredFields as $field => $value) {
                $criteria .= "{$field} = '{$value}'";
                if ($i < $fieldCount) {
                    $criteria .= ' AND ';
                }
                $i++;
            }
        
            // 重複データを絞り込み
            $comparingSql = "SELECT * FROM {$this->tableName}";
            $comparingSql .= " WHERE {$criteria}";
            $comparingItems = DB::select( $comparingSql );
            $comparingCount = count($comparingItems);

            if ($comparingCount == -1) {
                $this->comment(date('Y-m-d H:i:s') . ' ERROR');
                $this->comment("#{$index} {$item->id} -> {$comparingCount}");
                exit;
            }

            if ($comparingCount > 1) {

                // 重複データのフラグを付けるチェック
                $executionInfo = array();
                // GET MAX UPDATED AT
                $maxTime = strtotime($item->updated_at);
                // SELECTED ID
                $selectedId = $item->id;
                
                foreach ($comparingItems as $x => $comparingItem) {
                    if ($comparingItem->id == $item->id) {
                        continue;
                    }
                    $this->duplicated_data[$comparingItem->id] = true;
                    
                    $time = strtotime($comparingItem->updated_at);
                    if ($time > $maxTime) {
                        $maxTime = $time;
                        $selectedId = $comparingItem->id;
                    }
                }
                // MARKS DUPLICATION FLAG
                foreach ($comparingItems as $x => $comparingItem) {
                    $executionInfo[] = array(
                        'id' => (int)$comparingItem->id,
                        'duplicated' => $selectedId != $comparingItem->id,
                    );
                }

                // 削除対象の実行
                foreach ($executionInfo as $info) {
                    $delFlag = $info['duplicated'] ? 1 : '';
                    $dupFlag = $info['duplicated'] ? 1 : 0;
                    if ($delFlag == 1) {
                        $this->comment(date('Y-m-d H:i:s') . " DELETE --> {$info['id']}");
                    }
                    $this->updateFlag($info['id'], $dupFlag, $item->id, $delFlag);
                }

            } else {
                $this->updateFlag($item->id, '', '', '');
            }
        }
        
        $this->comment(date('Y-m-d H:i:s') . ' Duplication check ended');
    }

    /**
     * レコードの削除対象のフラグを更新する関数
     */
    private function updateFlag($id, $flag, $sourceId, $deleteFlag)
    {
        if ($sourceId != '') {
            $checkId = "'{$sourceId}'";
        } else {
            $checkId = 'NULL';
        }
        if ($deleteFlag == 1) {
            $flag = 1;
        }
        $sql = "UPDATE {$this->tableName} SET
            {$this->tablePrefix}_check_id = {$checkId}, 
            {$this->tablePrefix}_delete_target = '{$deleteFlag}',
            {$this->tablePrefix}_checked_at = current_timestamp
            WHERE id = '{$id}' AND ({$this->tablePrefix}_delete_target IS NULL OR {$this->tablePrefix}_delete_target <> '1')";
        DB::select( $sql );
    }
    
}