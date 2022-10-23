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
 * Description of TargetCarsCleaningNotFullCommand
 *
 * @author ohishi
 */
class TargetCarsNotFullCommand extends Command implements ShouldBeQueued {
    
    use tTableCleaner;

    /**
     * テーブル名
     * 
     * @var string
     */
    private $tableName = 'tb_target_cars';
    
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
     * チェックのタイプ
     * 
     * @var int
     */
    private $checkingType = self::CHECKING_ALL;

    /**
     * 値が同じの比較
     */
    private $equalFields = [
        'tgc_inspection_id',
        'tgc_inspection_ym',
        'tgc_car_manage_number',
        'tgc_customer_code',
        'tgc_customer_name_kanji',
        'tgc_syaken_next_date',
    ];

    /**
     * NULLの絞り込み条件
     */
    private $nullFields = [
        'tgc_action',
        'tgc_memo',
        'tgc_daigae_car_name',
        'tgc_alert_memo',
    ];

    /**
     * 値が空の比較
     */
    private $nullContents = [
        'tgc_dm_flg',
        'tgc_dm_unnecessary_reason',
        'tgc_status',
        'tgc_action',
        'tgc_memo',
        'tgc_daigae_car_name',
        'tgc_alert_memo',
        'tgc_status_update'
    ];

    /**
     * 値がばらばらの比較
     */
    private $contentCheckFields = [
        'tgc_car_type',
        'tgc_dm_flg',
        'tgc_dm_unnecessary_reason',
        'tgc_status',
        'tgc_status_update'
    ];

    /**
     * スキャンした重複データのリスト
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
            $sql .= " WHERE true";
            // 空の条件
            $criteria_null = '';
            foreach ($this->nullFields as $i => $field) {
                $criteria_null .= "AND {$field} IS NULL ";
            }

            $sql .= ' ' . $criteria_null;
            // 当日に更新されるレコードを絞り込む
            $today = date('Ymd');
            $sql .= "AND to_char(created_at, 'yyyymmdd') = '{$today}'";
            $sql .= " AND tgc_check_id is null";
            $sql .= " ORDER BY created_at DESC, id DESC";
        } else if ( $this->checkingType == self::CHECKING_ALL ) {
            $sql = "select * from 
                (
                    select * from {$this->tableName}
                    where
                    (tgc_customer_code, tgc_inspection_id, tgc_inspection_ym) in
                    (
                        select tgc_customer_code, tgc_inspection_id, tgc_inspection_ym from {$this->tableName}
                        group by tgc_customer_code, tgc_inspection_id, tgc_inspection_ym
                        having count(tgc_customer_code) > 1
                    )
                    order by id, tgc_customer_code, tgc_inspection_id, tgc_inspection_ym
                ) as x";
            // 空の条件
            $criteria_null = ' WHERE true ';
            foreach ($this->nullFields as $i => $field) {
                $criteria_null .= "AND x.{$field} IS NULL ";
            }

            $sql .= ' ' . $criteria_null;
        }

        $items = DB::select( $sql );
        $total = count($items);
        $this->comment("Checking {$total} data ...");
        
        foreach ($items as $index => $item) {
            if (isset($this->duplicated_data[$item->id])) {
                continue;
            }
            $this->duplicated_data[$item->id] = true;

            // 重複データの絞り込み条件を作成
            $criteria = 'true AND ';
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

            if ($comparingCount <= 1) {
                $this->updateFlag($item->id, '', '', '');
                continue;
            }

            // 重複データのフラグを付けるチェック
            $executionInfo = array();
            $isNullcheckId = $this->isNullValues($item);
            foreach ($comparingItems as $x => $comparingItem) {
                if ($comparingItem->id != $item->id) {
                    if ($this->isNullValues($comparingItem)) {
                        $isDuplicated = true;
                        $this->comment(date('Y-m-d H:i:s') . " NULL --> {$item->id}");
                    } else if ($this->isEqual($comparingItem, $item) &&
                        $comparingItem->id < $item->id) 
                    {
                        $isDuplicated = true;
                    } else {
                        $isDuplicated = false;
                    }
                } else {
                    $isDuplicated = $isNullcheckId;
                    if ($isDuplicated) {
                        $this->comment(date('Y-m-d H:i:s') . " NULL --> {$item->id}");
                    }
                }
                $executionInfo[] = array(
                    'id' => (int)$comparingItem->id,
                    'duplicated' => $isDuplicated,
                    'data' => $comparingItem
                );
            }

            // 重複データの件数のチェック
            $isAllDuplicated = true;
            $maxId = 0;
            $notDuplicatedRecords = 0;
            $duplicatedRecords = 0;
            foreach ($executionInfo as $info) {
                if (!$info['duplicated']) {
                    $isAllDuplicated = false;
                    $notDuplicatedRecords++;
                } else {
                    $duplicatedRecords++;
                }
                if ($info['id'] > $maxId) {
                    $maxId = $info['id'];
                }
            }

            // 削除対象のフラグを付けるチェック
            for ($i = 0; $i < $comparingCount; $i++) {
                // 1 1 1
                if ($isAllDuplicated) {
                    if ($maxId == $executionInfo[$i]['id']) {
                        $executionInfo[$i]['delete'] = false;
                    } else {
                        $executionInfo[$i]['delete'] = true;
                    }
                } else {
                    // 1 0 OR 1 1 0
                    if ($notDuplicatedRecords == 1 && $duplicatedRecords == 1 ||
                        $notDuplicatedRecords == 1 && $duplicatedRecords > 1
                    ) {
                        $executionInfo[$i]['delete'] = $executionInfo[$i]['duplicated'];
                    } else {
                        // 0 0 1
                        $executionInfo[$i]['delete'] = $executionInfo[$i]['duplicated'] ? true : 0;
                    }
                }
            }

            // 更新可能なレコードのチェック
            if ($comparingCount == 2) {
                $normalizedData = [];
                $isAutoNormalizable = true;

                $data1 = $executionInfo[0]['data'];
                $data2 = $executionInfo[1]['data'];

                foreach ($this->contentCheckFields as $fieldName) {
                    if (!empty($data1->{$fieldName}) && empty($data2->{$fieldName})) {
                        $normalizedData[$fieldName] = $data1->{$fieldName};
                    } else if (empty($data1->{$fieldName}) && !empty($data2->{$fieldName})) {
                        $normalizedData[$fieldName] = $data2->{$fieldName};
                    } else if (!empty($data1->{$fieldName}) && !empty($data2->{$fieldName})
                        && $data1->{$fieldName} != $data2->{$fieldName}) 
                    {
//                            echo "{$item->id} {$fieldName} -> {$data1->{$fieldName}} AND {$data2->{$fieldName}}\n";
                        $isAutoNormalizable = false;
                    }
                }
                echo "{$item->id} " . count($normalizedData) . "\n";
                if ($isAutoNormalizable && count($normalizedData) > 0) {
                    if ($executionInfo[0]['id'] < $executionInfo[1]['id'] &&
                        $executionInfo[1]['duplicated'] &&
                        $this->isNormalizedFieldNull($executionInfo[0]['data'], $normalizedData))
                    {
                        $executionInfo[1]['normalizedValues'] = $normalizedData;
                        $executionInfo[1]['normalized'] = true;
                        $executionInfo[1]['delete'] = false;
                        $executionInfo[0]['delete'] = true;
                    } else if ($executionInfo[0]['duplicated'] &&
                        $this->isNormalizedFieldNull($executionInfo[1]['data'], $normalizedData))
                    {
                        $executionInfo[0]['normalizedValues'] = $normalizedData;
                        $executionInfo[0]['normalized'] = true;
                        $executionInfo[0]['delete'] = false;
                        $executionInfo[1]['delete'] = true;
                    } else if ($this->isHaveDifferenceValues(
                        $executionInfo[0]['data'],
                        $executionInfo[1]['data'])
                    ) {
                        $this->comment(date('Y-m-d H:i:s') . " MANUAL --> {$executionInfo[0]['id']}");
                        $this->comment(date('Y-m-d H:i:s') . " MANUAL --> {$executionInfo[1]['id']}");
                        $executionInfo[0]['manualCheck'] = true;
                        $executionInfo[1]['manualCheck'] = true;
                    } else {
                        if ($executionInfo[0]['id'] < $executionInfo[1]['id']) {
                            $executionInfo[1]['delete'] = false;
                            $executionInfo[0]['delete'] = true;
                        } else {
                            $executionInfo[0]['delete'] = false;
                            $executionInfo[1]['delete'] = true;
                        }
                    }
                } else if (!$isAutoNormalizable) {
                    if (!$this->isHaveDifferenceValues(
                        $executionInfo[0]['data'],
                        $executionInfo[1]['data'])) {
                        if ($executionInfo[0]['id'] < $executionInfo[1]['id']) {
                            $executionInfo[1]['delete'] = false;
                            $executionInfo[0]['delete'] = true;
                        } else {
                            $executionInfo[0]['delete'] = false;
                            $executionInfo[1]['delete'] = true;
                        }
                    }
                } else {
                    $this->comment(date('Y-m-d H:i:s') . " MANUAL --> {$executionInfo[0]['id']}");
                    $this->comment(date('Y-m-d H:i:s') . " MANUAL --> {$executionInfo[1]['id']}");
                    $executionInfo[0]['manualCheck'] = true;
                    $executionInfo[1]['manualCheck'] = true;
                    $executionInfo[0]['delete'] = false;
                    $executionInfo[1]['delete'] = false;
                }
            } else {
                foreach ($executionInfo as $i => $info) {
                    $this->comment(date('Y-m-d H:i:s') . " MANUAL --> {$info['id']}");
                    $executionInfo[$i]['manualCheck'] = true;
                    $executionInfo[$i]['delete'] = false;
                }
            }

            // 削除対象の実行
            foreach ($executionInfo as $info) {
                $dupFlag = $info['duplicated'] ? 1 : 0;
                if ($info['delete'] === 0) {
                    $delFlag = 0;
                } else {
                    if (isset($info['manualCheck'])) {
                        $delFlag = 3;
                    } else if (isset($info['normalizedDirectly']) && !$info['delete']) {
                        $this->comment(date('Y-m-d H:i:s') . " AUTO --> {$info['id']}");
                        $this->normalize($info['id'], $item->id, $info['normalizedValues']);
                        $delFlag = $info['delete'] ? 1 : 2;
                    } else if (isset($info['normalized'])) {
                        if ($info['delete']) {
                            $this->comment(date('Y-m-d H:i:s') . " AUTO --> {$info['id']}");
                            $this->normalize($info['id'], $item->id, $info['normalizedValues']);
                        }
                        $delFlag = 2;
                    } else {
                        $delFlag = $info['delete'] ? 1 : '';
                    }
                }
                if ($delFlag == 1) {
                    $this->comment(date('Y-m-d H:i:s') . " DELETE --> {$info['id']}");
                }
                $this->updateFlag($info['id'], $dupFlag, $item->id, $delFlag);
                // $this->copyRecord($info['data']);
            }
        }
        
        $this->comment(date('Y-m-d H:i:s') . ' Duplication check ended');
    }

    /**
     * コピー先のデータの値が空のチェックする関数
     */
    private function isNormalizedFieldNull($item, $normalizedData) {
        foreach ($normalizedData as $key => $value) {
            if (!empty($item->{$key})) {
                return false;
            }
        }
        return true;
    }

    /**
     * 違う値があるチェックする関数
     */
    private function isHaveDifferenceValues($item1, $item2) {
        foreach ($item1 as $key => $value) {
            if (!empty($item1->{$key}) && !empty($item2->{$key}) &&
                $item1->{$key} == $item2->{$key}) {
                return false;
            }
        }
        return true;
    }

    /**
     * 全部値が空であるチェックする関数
     */
    private function isNullValues($item)
    {
        foreach ($this->nullContents as $i => $field) {
            if (empty($item->{$field})) {
                return false;
            }
        }
        return true;
    }

    /**
     * レコードが全く同じのチェックする関数
     */
    private function isEqual($item1, $item2)
    {
        foreach ($this->contentCheckFields as $i => $field) {
            if ($item1->{$field} != $item2->{$field}) {
                return false;
            }
        }
        return true;
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
            tgc_check_id = {$checkId}, 
            tgc_delete_target = '{$deleteFlag}',
            tgc_checked_at = current_timestamp
            WHERE id = '{$id}' AND (tgc_delete_target IS NULL OR tgc_delete_target <> '1')";
        DB::select( $sql );
    }
    
    /**
     * 全部の値をコピーする関数
     */
    private function normalize($id, $sourceId, $data)
    {
        if ($sourceId != '') {
            $checkId = "'{$sourceId}'";
        } else {
            $checkId = 'NULL';
        }
        $normalizedValues = '';
        foreach ($data as $key => $value) {
            $value = str_replace("'", "''", $value);
            $normalizedValues .= "{$key} = '{$value}', ";
        }
        $sql = "UPDATE {$this->tableName} SET
            tgc_check_id = {$checkId}, 
            {$normalizedValues}
            tgc_delete_target = '2',
            tgc_checked_at = current_timestamp
            WHERE id = '{$id}'";
        DB::select( $sql );
    }

    /*private function copyRecord($item)
    {
        // COPY 1
        $copyKeys = '';
        $copyValues = '';
        foreach ($item as $key => $value) {
            $copyKeys .= "{$key}, ";
            $value = str_replace("'", "''", $value);
            if (!empty($value)) {
                $value = str_replace("'", "''", $value);
                $copyValues .= "'{$value}', ";
            } else {
                $copyValues .= "NULL, ";
            };
        }
        $sql = "INSERT INTO {$this->tableName}_check({$copyKeys}tgc_check_stage,
            tgc_checked_at) VALUES({$copyValues}'1', now())";
        DB::select( $sql );
    }*/
}
