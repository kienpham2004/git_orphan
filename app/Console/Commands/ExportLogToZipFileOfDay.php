<?php

namespace App\Console\Commands;

use App\Original\Util\CodeUtil;
use Exception;
use Illuminate\Console\Command;

class ExportLogToZipFileOfDay extends Command
{
     /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:ExportLogToZipFileOfDay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '毎日に全てログのファイルをZIPファイル';


    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        //毎日のすべてログをZIPする
        $date = date('Y-m-d');
        $this->exportLogToZipFileByDay($date);

        //前日のログファイルをZIPできるかどうかを確認
        $this->checkLogFileOfDayBefore();
    }

    /**
     * 毎日のすべてログをZIPする
     * @param $date
     */
    public function exportLogToZipFileByDay($date) {
        try{
            $debug_file_name = 'debug-' .$date .'.log';
            $error_file_name = 'error-' .$date .'.log';
            $access_file_name = 'access-' .$date .'.log';
    
            $debug_log_dir = storage_path() . '/logs/' . $debug_file_name;
            $error_log_dir = storage_path() . '/logs/' . $error_file_name;
            $access_log_dir = storage_path() . '/logs/' . $access_file_name;
    
            if (file_exists($debug_log_dir)) {
                $this->comment(date('Y-m-d H:i:s') ." - DebugのログファイルをZIPの開始です。");
                $this->copyAndZipFile($debug_log_dir, $debug_file_name ,storage_path() . '/logzips');
            }
    
            if (file_exists($error_log_dir)) {
                $this->comment(date('Y-m-d H:i:s') ." - エラーのログファイルをZIPの開始です。");
                $this->copyAndZipFile($error_log_dir, $error_file_name ,storage_path() . '/logzips');
            }
    
            if (file_exists($access_log_dir)) {
                $this->comment(date('Y-m-d H:i:s') ." - アクセスのログファイルをZIPの開始です。");
                $this->copyAndZipFile($access_log_dir, $access_file_name ,storage_path() . '/logzips');
            }
        }catch (\Exception $ex) {
            \Log::error($ex);
            $this->comment(date('Y-m-d H:i:s') .'- [月のファイルのZIP]Error: ' . $ex->getMessage());
            
            // エラーメール送信
            $stage_title = config('original.title');
            mutSendMail(
                config('original.mail_to'),                     // 宛先メールアドレス
                $stage_title."か毎日ログのファイルのZIPのお知らせ",       // タイトル
                "【エラー】".$this->description."がエラー発生しました\n"
                ."　ログの日付：".$date."\n",
                config('original.mail_from'),                   // 送信者メールアドレス
                $stage_title                                    // 送信者名
            );
        }
    }

    /**
     * ログファイルが存在するかどうか確認
     */
    public function checkLogFileOfDayBefore() {
        $day_before = date( "Y-m-d", strtotime( "-1 day" ) );

        $debug_logzip_dir = storage_path() . '/logzips/' . 'debug-' .$day_before .'.log.zip';
        $error_logzip_dir = storage_path() . '/logzips/' . 'error-' .$day_before .'.log.zip';
        $access_logzip_dir = storage_path() . '/logzips/' . 'access-' .$day_before .'.log.zip';
        
        $debug_log_dir = storage_path() . '/logs/' . 'debug-' .$day_before .'.log';
        $error_log_dir = storage_path() . '/logs/' . 'error-' .$day_before .'.log';
        $access_log_dir = storage_path() . '/logs/' . 'access-' .$day_before .'.log';

        if((!file_exists($debug_logzip_dir) && file_exists($debug_log_dir)) ||
            (!file_exists($error_logzip_dir) && file_exists($error_log_dir)) ||
            (!file_exists($access_logzip_dir) && file_exists($access_log_dir)))
        {
            $this->exportLogToZipFileByDay($day_before);
        }
    }

    /**
     * ログファイルがコピーとZIPをする
     * @param $compress_file ログファイルのパス
     * @param $file_name ログファイル名
     * @param $copy_dir_to 移動したZIPのフォルダ
     */
    private function copyAndZipFile($compress_file, $file_name, $copy_dir_to) {
        // 圧縮ファイル
        CodeUtil::zipFileLog($compress_file); 

        // ファイルの書き込み権限を変更
        @chmod($compress_file . ".zip", 0755);
        // アップロード済みディレクトリにファイルを格納
        @rename($compress_file . ".zip", $copy_dir_to . "/" . $file_name .'.zip');
        $this->comment(date('Y-m-d H:i:s') . $file_name ."のログファイルをZIPの終了です。");
    }
}
