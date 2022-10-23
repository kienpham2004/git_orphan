<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use DB;

class CleanRecords extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:clean-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'スキャンして確認した結果のレコードを削除するコマンド';
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '8196M');

        $this->comment(date('Y-m-d H:i:s') ." - Duplicated records deletion started");
        
        $this->cleanDuplicationCheckResult( 'tb_target_cars', 'tgc' );
        $this->cleanDuplicationCheckResult( 'tb_result', 'rst' );
        $this->cleanDuplicationCheckResult( 'tb_result_cars', 'rstc' );
        $this->cleanDuplicationCheckResult( 'tb_manage_info', 'mi' );
        
        $this->comment(date('Y-m-d H:i:s') ." - Duplicated records deletion ended");
    }

    /**
     * 手動重複データの検索結果を解消
     * @param string $tbName テーブル名
     * @param string $tbPrefix テーブルのプレフィックス
     */
    private function cleanDuplicationCheckResult($tbName, $tbPrefix) {
        $sql = "SELECT * FROM {$tbName} WHERE {$tbPrefix}_delete_target = '1'";
        $items = DB::select( $sql );
        $rowCount = count($items);
        $this->comment(date('Y-m-d H:i:s') .' - 手動解消結果の重複データを' . $rowCount . '件見つけました。' . "\n");
        
        if ($rowCount == 0) {
            return;
        }
        
        $filePath = storage_path('logs/duplicatedRecords/backup-' . $tbName . '-' . date('Ymd') . '.sql');
        
        foreach ($items as $i => $item) {
            $sqlBackup = $this->buildBackupSql( $tbName, $item );
            $this->appendFileContent($filePath, $sqlBackup);

            // 重複データを削除
            $sqlDelete = "DELETE FROM {$tbName} " .
                "WHERE {$tbPrefix}_delete_target = '1' AND id = '{$item->id}'";
            DB::statement( $sqlDelete );
        }
    }
    
    /**
     * バックアップファイルを書き込み
     * 
     * @param string $path ログファイルのパス
     * @param string $stringData 文章
     */
    private function appendFileContent( $path, $stringData ) {
        $stringData .= "\n";
        $fh = fopen($path, 'a') or die("can't open file");
        fwrite($fh, $stringData);
        fclose($fh);
    }

    /**
     * レコードのバックアップのクエリーを作成
     * 
     * @param string $tbName テーブル名
     * @param object $item レコード
     * @return string
     */
    private function buildBackupSql( $tbName, $item ) {
        $copyKeys = '';
        $copyValues = '';
        $index = 0;
        $fieldCount = count((array)$item) - 1;

        foreach ($item as $key => $value) {
            $copyKeys .= $key;
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
        
        return "INSERT INTO {$tbName}({$copyKeys}) VALUES({$copyValues});";
    }
    
}
