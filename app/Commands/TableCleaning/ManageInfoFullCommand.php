<?php

namespace App\Commands\TableCleaning;

use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 重複データを解消するコマンド
 */
class ManageInfoFullCommand extends Command implements ShouldBeQueued {
    
    use tTableCleaner;
    
    /**
     * 実行テストモード
     */
    const EXECUTION_TEST_MODE = 0;
    
    /**
     * 実行本番モード
     */
    const EXECUTION_PRODUCTION_MODE = 1;
    
    /**
     * 実行設定
     * 
     * @var array
     */
    private $config = array(
        // バックアップ
        'backupDeletionRecords' => false,
        // 重複データを生成
        'generateFakeRecords' => false,
        // 実行するモード
        // 注意：テストモードでは、重複データを生成されるとなります。
        'executionMode' => self::EXECUTION_PRODUCTION_MODE,
    );

    /**
     * テーブル名
     */
    private $tableNames = array( 'tb_manage_info' );

    /**
     * テーブルのカラム名
     */
    private $fields = [];
    
    /**
     * カラムのタイプ
     * @var array
     */
    private $fieldsType = [];

    /**
     * 値が同じの比較
     */
    private $equalFields = [];

    /**
     * 処理率
     * @var int
     */
    private $percentage = 0;

    /**
     * 重複データのフラグリスト
     * @var array
     */
    private $duplicatedIds = array();

    /**
     * コンストラクタ
     */
    public function __construct(){
        
        ini_set('memory_limit', '8196M');
        
        $this->duplicatedIds = array();
        
        // 必要なデータの準備
        $timeStart = microtime(true);
        $this->comment( "準備中..." );
        foreach ( $this->tableNames as $tableName ) {
            
            $this->duplicatedIds[$tableName] = array();
            $this->fields[$tableName] = $this->getTableColumns( $tableName );
            $this->fieldsType[$tableName] = $this->getTableFieldsType( $tableName, $this->fields[$tableName] );
            $this->equalFields[$tableName] = $this->collectEqualFields( $tableName, $this->fields[$tableName] );
        }
        
        $timeEnd = microtime(true);
        $executionTime = round($timeEnd - $timeStart, 2);
        $this->comment( "実行時間: {$executionTime}秒" );
        $this->comment( '=========================' );
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle() {

        // 完全一致重複データを解消
        foreach ( $this->tableNames as $tableName ) {
            $this->cleanDuplicatedRecords( $tableName );
        }
    }

    /**
     * 完全一致重複データを解消
     */
    private function cleanDuplicatedRecords( $tableName ) {
        
//        if ($this->config['executionMode'] == self::EXECUTION_PRODUCTION_MODE) {
            $items = $this->getTodayUpdatedRecords( $tableName );
//        } else {
//            $items = $this->makeFakeDataAndGetRecords( $tableName );
//        }
        $rowCount = count($items);
        
        // 重複データを生成
        /*if ($this->config['executionMode'] == self::EXECUTION_TEST_MODE && $this->config['generateFakeRecords']) {
//            $halfOfCount = floor($rowCount / 2) - 1; 
            
            $this->comment('生成中...');
            $timeStart = microtime(true);
            
            $total = 0;
            foreach ($items as $index => $item) {
                $fields = $this->removeArrayItemByValue( $this->fields[$tableName], ['id'] );
                $this->duplicateRecord($tableName, $fields, $item);
                $total++;
//                if ($index == $halfOfCount) {
//                    break;
//                }
            }
            
            $this->comment( $tableName . 'で重複するデータを' . $total . '件生成しました。' );
        
            $timeEnd = microtime(true);
            $executionTime = round(($timeEnd - $timeStart) / 60, 2);
            $this->comment( "実行時間: {$executionTime}分" );
        }*/
        
        $timeStart = microtime(true);
        $this->comment( $tableName . 'データを' . $rowCount . '件絞り込みました。' );
        if ($rowCount > 0) {
            $this->comment('処理中...');
        }
        
        // バックアップして、削除するコマンド
//        if ($this->config['backupDeletionRecords']) {
//            $filePath = storage_path('logs/duplicatedRecords/backup-short-' . $tableName . '-' . date('Ymd') . '.sql');
//        }
        
        $this->comment('-------------------------');

        $cleanedCount = 0;
        foreach ($items as $index => $item) {
            // 重複フラグがあるデータ
            if (isset($this->duplicatedIds[$tableName][$item->id])) {
                continue;
            }

            // 作成日時と更新日時の期間
            $duplicatedItems = $this->getDuplicationRecord( $tableName, $this->equalFields[$tableName], $this->fieldsType[$tableName], $item );
            // 重複チェック
            if (count($duplicatedItems) == 0) {
                continue;
            }
            
            $duplicatedItem = $duplicatedItems[0];
            
            $this->duplicatedIds[$tableName][$item->id] = true;
            $this->duplicatedIds[$tableName][$duplicatedItem->id] = true;
            
            // バックアップ
//            if ($this->config['backupDeletionRecords']) {
//                $sql = $this->buildBackupSql( $tableName, $this->fields[$tableName], $item );
//                $this->appendFileContent($filePath, $sql);
//            }

            // 削除
            if ($duplicatedItem->id < $item->id) {
                $this->deleteRecord( $tableName, $duplicatedItem->id, $item->id );
            } else {
                $this->deleteRecord( $tableName, $item->id, $item->id );
            }
            $cleanedCount++;
            
            $percentage = round(($index + 1) / $rowCount * 100);
            if ($this->percentage != $percentage && $percentage % 4 == 0) {
                echo '-';
            }
            $this->percentage = $percentage;
        }
        
        if ($rowCount > 0) {
            $this->comment('');
        }

        $this->percentage = 0;
        $this->comment( $tableName . 'データの完全一致の重複データを' . $cleanedCount . '件削除しました。' );
        
        $timeEnd = microtime(true);
        $executionTime = round(($timeEnd - $timeStart) / 60, 2);
        $this->comment( "実行時間: {$executionTime}分" );
        $this->comment( '=========================' );
    }
}
