<?php

namespace App\Commands\Csv;
asdasdasdasdas
use App\Original\Util\CodeUtil;
use App\Commands\Command;
use App\Commands\Csv\tCsvUpload;
use App\Events\UploadedEvent;
use App\Models\UserAccount;
// 独自
use OhInspection;

/**
 * csvアップロードのコマンド
 * @author yhatsutori
 */
class CsvConsoleUploadCommand extends Command{
    
    use tCsvUpload;

    /**
     * コンストラクタ
     * @param [type] $consoleCmdObj [description]
     * @param string $file_type     [description]
     */
    public function __construct( $consoleCmdObj, $file_type="0" ){
        $this->consoleCmdObj = $consoleCmdObj;
        $this->file_type = $file_type;
    }

    /**
     * メインの処理
     */
    public function handle(){
        // CSVアップロードの時間を上げる
        set_time_limit(18432);
        // メモリの上限を変更
        ini_set('memory_limit', '2048M');
        
        // ファイルを格納するディレクトリ名を取得
        $csvDirName = CodeUtil::getCsvDirCode( $this->file_type );

        // 検索対象ディレクトリのパス
        $csvDir = storage_path() . '/upload/notyet_' . $csvDirName;
        // コピー先のディレクトリのパス
        $copyDir = storage_path() . '/upload/' . $csvDirName;


        // 対象ディレクトリを取得
        $files = OhInspection::getScanFileList( $csvDir );
       
        if( !empty( $files ) == True ){
            foreach ( $files as $key => $value ) {

                try {
                    $processFile = $copyDir . '/check_' . $value;
                    // ダブル実行チェック
                    if (CodeUtil::doubleRunCheck(true, $processFile, 'CSV')) {
                        $this->consoleCmdObj->comment("ダブルのファイル " . $csvDir . "/" . $value);
                        continue;
                    }

                    // CSVの取り込みを行うオブジェクトを取得
                    $csvImportObj = $this->getInstance($this->file_type, $csvDir . "/" . $value);
                    $this->consoleCmdObj->comment(PHP_EOL . date('Y-m-d H:i:s') . " - ファイル : " . $csvDir . "/" . $value);

                    // CSVファイルの取り込み処理
                    // CsvImportのメソッドを実行
                    $csvImportObj->execute();

                    if ($this->file_type === '8') {
                        // CSVの取り込みを行うオブジェクトを取得
                        $csvImportObj = $this->getInstance($this->file_type, $csvDir . "/" . $value, true);

                        // CSVファイルの取り込み処理
                        // CsvImportのメソッドを実行
                        $csvImportObj->execute();
                    }

                    // アップロード履歴を登録、更新する
                    \Event::fire(
                        new UploadedEvent(
                            new UserAccount(),
                            $csvImportObj->result->totalCount(),
                            $this->file_type
                        )
                    );

                    // 顧客データの時に顧客データの有無のデータを取得
                    if ($this->file_type == "3") {
                        // CSVの取り込みを行うオブジェクトを取得
                        $csvImportObj = $this->getInstanceUmu($this->file_type, $csvDir . "/" . $value);

                        // CSVファイルの取り込み処理
                        // CsvImportのメソッドを実行
                        $csvImportObj->execute();

                    }

                    // ファイル移動のコメント
                    $this->consoleCmdObj->comment(PHP_EOL . date('Y-m-d H:i:s') . " - コピー元のファイル " . $csvDir . "/" . $value);
                    $this->consoleCmdObj->comment(date('Y-m-d H:i:s') . " - コピー先のファイル " . $copyDir . "/" . $value);

                    // ファイルの書き込み権限を変更
                    @chmod($csvDir . "/" . $value, 0755);
                    // アップロード済みディレクトリにファイルを格納
                    @rename($csvDir . "/" . $value, $copyDir . "/" . $value);

                    CodeUtil::zipFile($copyDir . "/" . $value); // 圧縮ファイル

                    // 元々残ファイル一回を全て圧縮を行う、毎回実施が不要
                    CodeUtil::zipAllFileInFolder($copyDir);

                } catch( \Exception $ex ) {
                    $this->consoleCmdObj->comment(date('Y-m-d H:i:s') ."Error : ".PHP_EOL. $ex);
                }
                finally {
                    //仮ファイル削除
                    CodeUtil::doubleRunCheck(false, $processFile);
                }
            }
        }
    }
}
