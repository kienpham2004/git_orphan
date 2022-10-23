<?php

namespace App\Console\Commands;

use App\Commands\Honsya\Log\CreateCommand;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ExportLogToZipFileOfMonth extends Command
{
    use DispatchesCommands;
     /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:ExportLogToZipFileOfMonth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '毎月に全てログのファイルをZIPファイル';


    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if( date("d") == "1") { 
            //月のすべてログファイルをZIPする
            $year = date( "Y", strtotime( "-1 day" ) );
            $month = date( "m", strtotime( "-1 day" ) );
            $this->zipFileOfMonth ($year, $month);

            //5月前のすべてログファイルを削除
            $this->deleteAllFileOfFiveMonthBefore();
        }

        //ログファイルを削除
        if( date("d") == "10") { 
            $year = date( "Y", strtotime( "-10 days" ) );
            $month = date( "m", strtotime( "-10 days" ) );

            //月のすべてログファイルを削除
            $this->deleteAllFileLogOfMonthBefore($year, $month);
        }
    }

    /**
     * 月のすべてログファイルをZIPする
     * @param $year [description]
     * @param $month [description]
     */
    private function zipFileOfMonth ($year, $month) {
        try {
            $this->comment(date('Y-m-d H:i:s') ."- 月のZIPファイルのバッチ実行が開始する。");
            $zip = new ZipArchive();

            //月のZIPファイルを作成
            $path_zip_month =  storage_path() . '/logzip_months/';
            $zip_file_name = $year .$month .'.zip';
            $zip_file_path = $path_zip_month . $zip_file_name;
            if(file_exists($zip_file_path)) {
                $result = $zip->open($zip_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            }else {
                $result = $zip->open($zip_file_path, ZipArchive::CREATE);
            }

            $count_file = 0;
            if ($result === true) {
                $path = storage_path() . '/logzips/';
                $files = File::files($path);
                $ym =  $year . $month;
                $count_file = 0;

                foreach ($files as $value) {
                    if(!file_exists($value)) continue;

                    $file_names = explode('-', File::name($value));
                    if (count($file_names) != 4) continue;

                    $file_ym = $file_names[1] . $file_names[2];
                    if((int)$file_ym == (int)$ym) {
                        $count_file += 1;
                        $zip->addFile($value, pathinfo($value, PATHINFO_BASENAME));
                    }
                }   

                $this->comment(date('Y-m-d H:i:s') ."- ファイルの合計： = ".  $count_file);

                if($count_file > 0) {
                    // ファイルの書き込み権限を変更
                    @chmod($zip_file_path, 0755);

                    //データベースに履歴データを保存
                    $this->dispatch(new CreateCommand($year . $month));

                    $this->comment(date('Y-m-d H:i:s') ."- ログファイルをZIPの終了です。");
                }
            }
        } catch (\Exception $ex) {
            \Log::error($ex);
            $this->comment(date('Y-m-d H:i:s') .'- [月のファイルのZIP]Error: ' . $ex->getMessage());
        }
    }

    /**
     * 月のすべてログファイルを削除
     * @param $year [description]
     * @param $month [description]
     */
    private function deleteAllFileLogOfMonthBefore($year, $month) {
        try {
            $this->comment(date('Y-m-d H:i:s') ."- 月のすべてログファイルののバッチ実行が開始する。");

            $path = storage_path() . '/logzips/';
            $files = File::files($path);
            $ym =  $year . $month;
            $count_file = 0;

            foreach ($files as $value) {
                if(!file_exists($value)) continue;

                $file_names = explode('-',File::name($value));
                if (count($file_names) != 4) continue;

                $file_ym = $file_names[1] . $file_names[2];
                if((int)$file_ym <= (int)$ym) {
                    $count_file += 1;
                    unlink($value);
                }
            }

            $this->comment(date('Y-m-d H:i:s') .'-' .$count_file .'の ログファイルを削除の終了です。');

        } catch (\Exception $ex) {
            \Log::error($ex);
            $this->comment(date('Y-m-d H:i:s') .'- [月のファイルの削除]Error: ' . $ex->getMessage());

            $stage_title = config('original.title');
            mutSendMail(
                config('original.mail_to'),                     // 宛先メールアドレス
                $stage_title."か毎月ログのファイルのZIPのお知らせ",       // タイトル
                "【エラー】".$this->description."がエラー発生しました\n"
                ."　ログの年月：".$year .'年'. $month . "月\n",
                config('original.mail_from'),                   // 送信者メールアドレス
                $stage_title                                    // 送信者名
            );
        }
    }

    /**
     * 5月前のすべてログファイルを削除
     */
    private function deleteAllFileOfFiveMonthBefore() {
        try {
            $this->comment(date('Y-m-d H:i:s') . '- 5月前のログファイルの削除を開始します。');
            $year = date( "Y", strtotime( "-5 months" ) );
            $month = date( "m", strtotime( "-5 months" ) );

            $path = storage_path() . '/logzip_months/';
            $files = File::files($path);
            $ym = $year . $month;

            $count_file = 0;
            foreach ($files as $value) {
                if(!file_exists($value)) continue;

                $file_ym =File::name($value);
                if((int)$file_ym <= (int)$ym) {
                    $count_file += 1;
                    unlink($value);
                }
            }
            
            $this->comment(date('Y-m-d H:i:s') ."- ファイル削除の合計： = ".  $count_file);
            
            if ($count_file > 0) {
                $sql = "delete from tb_log_month where lm_ym <= '" . $year .$month . "'";
                \DB::statement($sql);

                $this->comment(date('Y-m-d H:i:s') . '-  5月前のログファイルの削除の完了です。');
            }else {
                $this->comment(date('Y-m-d H:i:s') . '-  5月前のログファイルが存在しません。');
            }
        } catch (\Exception $ex) {
            \Log::error($ex);
            $this->comment(date('Y-m-d H:i:s') .'- [ 5月前のログファイルの削除] Error: ' . $ex->getMessage());
        }
    }

    /**
     * 1~9月の場合、'0'プラスする
     * @param $day 日
     */
    private function convertDay($day) {
        if ($day < 10) {
            return '0' . $day;
        }
        return $day;
    }
}
