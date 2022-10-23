<?php

namespace App\Console\Commands;

use App\Commands\Csv\CsvConsoleUploadCommand;
use App\Commands\Extract\ExtractResultCarsCommand;
use App\Commands\Extract\ExtractContactSateiCommand;
use App\Commands\Extract\ExtractContactMitsumoriCommand;
use App\Commands\Extract\ExtractContactShijoCommand;
use App\Commands\Extract\ExtractContactShodanCommand;
use App\Commands\Extract\ExtractManageInfoCommand;
use App\Commands\Extract\UpdateSmaProCarManageCommand;
use App\Commands\Extract\ExtractIkouCommand;
use App\Commands\Extract\ExtractIkouLockCommand;
use App\Commands\Extract\UpdateIkouHistoryCommand;
use App\Commands\TableCleaning\ResultCarsFullCommand;
use App\Commands\TableCleaning\ResultCarsNotFullCommand;
use App\Commands\TableCleaning\ResultNotFullCommand;
use App\Commands\TableCleaning\ManageInfoFullCommand;
use App\Commands\TableCleaning\ManageInfoNotFullCommand;
use App\Models\UploadHistory;
use App\Original\Util\CodeUtil;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;

use App\Commands\Extract\ExtractSyakenJisshiCommand;
use App\Commands\Extract\UpdateFlagStatusCommand;
use App\Lib\Util\Constants;

// 独自
use OhInspection;

class CsvShortUpdate extends Command {

    use DispatchesCommands;

    /**
     * The console command name.
     * C沖縄
     * @var string
     */
    protected $name = 'batch:CsvShortUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(副)表示用データの取込';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){

        // 開始時間
        $start_time = date('Y/m/d H:i:s');

        // 処理開始
        info($this->description.'処理を開始しました');

        try {
            $processFile = storage_path() . '/upload/check_short_batch_manager';
            // ダブル実行チェック
            if (CodeUtil::doubleRunCheck(true, $processFile, 'BATCH')) {
                $this->comment(date('Y-m-d H:i:s') . " - CsvShortUpdate バッチは重複実行 ");
                exit;
            }

            // CSV更新の処理チェックファイル
            $csvFileUpdate = storage_path() . '/upload/check_short_batch_csv';
            CodeUtil::doubleRunCheck(true, $csvFileUpdate);

            ########################################
            ## PIT管理csv
            ########################################
            // pit管理のリストの中ファイル数を返す
            $pitCount = OhInspection::getScanFileCount( storage_path() . '/upload/notyet_pit' );
            if( $pitCount >= 1 ){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start console csv upload type PIT管理作業明細.");
                    $this->dispatch( new CsvConsoleUploadCommand( $this, "5" ) );
                    $this->comment(date('Y-m-d H:i:s') ." - End console csv upload type PIT管理作業明細.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start extract result cars 実績データ.");
                    $this->dispatch( new ExtractResultCarsCommand() );
                    $this->comment(PHP_EOL.date('Y-m-d H:i:s') ." - End extract result cars 実績データ.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }
            }


            ########################################
            ## TMR意向csv
            ########################################
            // TMRのリストの中ファイル数を返す
            $tmrCount = OhInspection::getScanFileCount( storage_path() . '/upload/notyet_tmr' );
            if( $tmrCount >= 1 ){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start console csv upload type TMRデータ.");
                    $this->dispatch( new CsvConsoleUploadCommand( $this, '4' ) );
                    $this->comment(date('Y-m-d H:i:s') ." - End console csv upload type TMRデータ.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }
            }


            ########################################
            ## スマートプロ査定csv
            ########################################
            // スマプロ査定のリストの中ファイル数を返す
            $smaProCount = OhInspection::getScanFileCount( storage_path() . '/upload/notyet_smart_pro' );
            if( $smaProCount >= 1 ){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start console csv upload type スマートプロ査定データ.");
                    $this->dispatch( new CsvConsoleUploadCommand( $this, '9' ) );
                    $this->comment(date('Y-m-d H:i:s') ." - End console csv upload type スマートプロ査定データ.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start update car manage number スマートプロ査定データ.");
                    $this->dispatch( new UpdateSmaProCarManageCommand() );
                    $this->comment(date('Y-m-d H:i:s') ." - End update car manage number スマートプロ査定データ.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }
            }

            ########################################
            ## 活動日報csv
            ########################################
            // 活動日報のリストの中ファイル数を返す
            $contactCount = OhInspection::getScanFileCount( storage_path() . '/upload/notyet_contact' );
            if( $contactCount >= 1 ){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start console csv upload type 活動日報実績.");
                    $this->dispatch( new CsvConsoleUploadCommand( $this, "8" ) );
                    $this->comment(date('Y-m-d H:i:s') ." - End console csv upload type 活動日報実績.");
                    $this->comment("");

                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try {
                    // 対象月分を実行
                    for( $month = -1; $month <= 2; $month++ ){
                        // 演算子を取得
                        $operate = "+";
                        if( $month == -1 ){
                            $operate = "";
                        }

                        $target_ym = date( "Ym", strtotime( date("Y-m-01") . " " . $operate . $month . " month" ) );

                        $this->comment(date('Y-m-d H:i:s') ." - Start Extract data type 活動日報実績(見積もり). {$target_ym}月");
                        $this->dispatch( new ExtractContactMitsumoriCommand( $target_ym ) );
                        $this->comment(PHP_EOL.date('Y-m-d H:i:s') ." - End Extract data type 活動日報実績(見積もり). {$target_ym}月");
                        $this->comment("");

                        $this->comment(date('Y-m-d H:i:s') ." - Start Extract data type 活動日報実績(査定). {$target_ym}月");
                        $this->dispatch( new ExtractContactSateiCommand( $target_ym ) );
                        $this->comment(PHP_EOL.date('Y-m-d H:i:s') ." - End Extract data type 活動日報実績(査定). {$target_ym}月");
                        $this->comment("");

                        $this->comment(date('Y-m-d H:i:s') ." - Start Extract data type 活動日報実績(試乗). {$target_ym}月");
                        $this->dispatch( new ExtractContactShijoCommand( $target_ym ) );
                        $this->comment(PHP_EOL.date('Y-m-d H:i:s') ." - End Extract data type 活動日報実績(試乗). {$target_ym}月");
                        $this->comment("");

                        $this->comment(date('Y-m-d H:i:s') ." - Start Extract data type 活動日報実績(商談). {$target_ym}月");
                        $this->dispatch( new ExtractContactShodanCommand( $target_ym ) );
                        $this->comment(PHP_EOL.date('Y-m-d H:i:s') ." - End Extract data type 活動日報実績(商談). {$target_ym}月");
                        $this->comment("");

                    }

                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }
            }

            ######################################
            ## Extract(代替車検推進表（顧客毎）)
            ######################################
            // 意向確認のリストの中ファイル数を返す
            $ikouCount = OhInspection::getScanFileCount( storage_path() . '/upload/notyet_ikou' );
            if( $ikouCount >= 1 ){

                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start console csv upload type 意向確認.");
                    $this->dispatch( new CsvConsoleUploadCommand( $this, "10" ) );
                    $this->comment(date('Y-m-d H:i:s') ." - End console csv upload type 意向確認.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try{
                    // tb_target_carsのtgc_statusを更新（最新意向）
                    $this->comment(date('Y-m-d H:i:s') ." - Start extract ikou to target cars latest ikou.");
                    $this->dispatch( new ExtractIkouCommand() );
                    $this->comment(date('Y-m-d H:i:s') ." - End extract ikou to target cars latest ikou.");
                    $this->comment("");
                } catch (Exception $ex) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }
            }


            ######################################
            ## Extract(市場措置未実施リスト)
            ######################################
            // 市場措置未実施リストのリストの中ファイル数を返す
            $recallCount = OhInspection::getScanFileCount( storage_path() . '/upload/notyet_recall' );
            if( $recallCount >= 1 ){

                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start console csv upload type 市場措置未実施リスト.");
                    $this->dispatch( new CsvConsoleUploadCommand( $this, "12" ) );
                    $this->comment(date('Y-m-d H:i:s') ." - End console csv upload type 市場措置未実施リスト.");
                    $this->comment("");

                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }
            }

            ######################################
            ## HTCログイン
            ######################################
            // HTCログインのリストの中ファイル数を返す
            $htcCount = OhInspection::getScanFileCount(storage_path() . '/upload/notyet_htc');
            if ($htcCount >= 1) {
                try {
                    $this->comment(date('Y-m-d H:i:s') . " - Start console csv upload type HTCログイン.");
                    $this->dispatch(new CsvConsoleUploadCommand($this, "13"));

                    // 意向履歴更新
                    // $this->dispatch(new UpdateIkouHistoryCommand(1));

                    $this->comment(date('Y-m-d H:i:s') . " - End console csv upload type HTCログイン.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start update flag フラグ更新.");
                    $this->dispatch( new UpdateFlagStatusCommand(Constants::SHORT_BATCH));
                    $this->comment(date('Y-m-d H:i:s') ." - End update flag フラグ更新.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }
            }

            ######################################
            ## Extract(車検実施リスト)
            ######################################
            // 車検実施リストの中ファイル数を返す
            $syakenJisshiCount = OhInspection::getScanFileCount( storage_path() . '/upload/notyet_syaken_jisshi' );
            if( $syakenJisshiCount >= 1 ){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start console csv upload type 車検実施リスト.");
                    $this->dispatch( new CsvConsoleUploadCommand( $this, "14" ) );
                    $this->comment(date('Y-m-d H:i:s') ." - End console csv upload type 車検実施リスト.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start 出荷報告日のデータを更新する.");
                    $this->dispatch( new ExtractSyakenJisshiCommand());
                    $this->comment(date('Y-m-d H:i:s') ." - End 出荷報告日のデータを更新する（tb_customer, tb_target_cars）.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start update flag フラグ更新.");
                    $this->dispatch( new UpdateFlagStatusCommand(Constants::SHORT_BATCH));
                    $this->comment(date('Y-m-d H:i:s') ." - End update flag フラグ更新.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }
            }

            ######################################
            ## 代替車検推進管理
            ######################################
            // 代替車検推進管理のリストの中ファイル数を返す
            $daigaeSyakenCount = OhInspection::getScanFileCount(storage_path() . '/upload/notyet_daigae_syaken');
            if ($daigaeSyakenCount >= 1) {
                try {
                    $this->comment(date('Y-m-d H:i:s') . " - Start console csv upload type 代替車検推進管理リスト.");
                    $this->dispatch(new CsvConsoleUploadCommand($this, "15"));

                    // 意向履歴更新
                    $this->dispatch(new UpdateIkouHistoryCommand(1));

                    $this->comment(date('Y-m-d H:i:s') . " - End console csv upload type 代替車検推進管理リスト.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try {
                    // tb_target_carsのtgc_statusを更新（最新意向）
                    $this->comment(date('Y-m-d H:i:s') . " - Start extract ikou to target cars latest ikou.");
                    $this->dispatch(new ExtractIkouCommand());
                    $this->comment(date('Y-m-d H:i:s') . " - End extract ikou to target cars latest ikou.");
                    $this->comment("");
                } catch (Exception $ex) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }
            }

            // 仮ファイル削除
            CodeUtil::doubleRunCheck(false, $csvFileUpdate);

            #######################################################################################
            ## Extract(マネージインフォデータ)  何かアップロードのある時に処理を行う
            #######################################################################################

            // 何かアップロードのある時に処理を行う
            if( $pitCount >= 1 || $contactCount >= 1 || $tmrCount >= 1 || $smaProCount >= 1 || $ikouCount >= 1 || $recallCount >= 1 || $daigaeSyakenCount >= 1 ){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start extract manage info マネージインフォデータ.");
                    $this->dispatch( new ExtractManageInfoCommand() );
                    $this->comment(PHP_EOL.date('Y-m-d H:i:s') ." - End extract manage info マネージインフォデータ.");
                    $this->comment("");

                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

            }

            // 意向ロック更新
            if( $ikouCount >= 1 ){
                try{
                    //意向ロックフラグ更新用
                    $this->comment(date('Y-m-d H:i:s') ." - Start update to ikou_lock_flg.");
                    $this->dispatch( new ExtractIkouLockCommand() );
                    $this->comment(date('Y-m-d H:i:s') ." - End update to ikou_lock_flg.");
                    $this->comment("");

                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }
            }

            ############################################################
            ## 重複データがあった時の処理
            ############################################################
            // tb_result, tb_result_cars
            if ($pitCount > 0) {
                $this->comment(date('Y-m-d H:i:s') ." - Start 重複データ解消.");
                $this->dispatch( new ResultCarsFullCommand() );
                // 完全一致でないターゲットカーズの重複チェック
                $this->dispatch( new ResultCarsNotFullCommand(
                    ResultCarsNotFullCommand::CHECKING_TODAY
                ));
                $this->dispatch( new ResultNotFullCommand(
                    ResultNotFullCommand::CHECKING_TODAY
                ));
                $this->comment(date('Y-m-d H:i:s') ." - End 重複データ解消（tb_result, tb_result_cars）.");
            }
            // tb_manage_info
            $uploadedManageInfoCsvFileCount = $contactCount + $tmrCount + $smaProCount + $ikouCount + $recallCount + $syakenJisshiCount + $daigaeSyakenCount;
            if ($uploadedManageInfoCsvFileCount > 0) {
                $this->comment(date('Y-m-d H:i:s') ." - Start 重複データ解消.");
                $this->dispatch( new ManageInfoFullCommand() );
                $this->dispatch( new ManageInfoNotFullCommand(
                    ManageInfoNotFullCommand::CHECKING_TODAY
                ));
                $this->comment(date('Y-m-d H:i:s') ." - End 重複データ解消（tb_manage_info）.");

            }


            #######################################################################################
            ## 終了処理（メール・ログ）
            #######################################################################################

            if( $pitCount >= 1 || $contactCount >= 1 || $tmrCount >= 1 || $smaProCount >= 1 || $ikouCount >= 1
                 || $recallCount >= 1 || $syakenJisshiCount >= 1 || $daigaeSyakenCount >= 1 ){

                // 終了時間を記録
                $end_time  = date('Y/m/d H:i:s');
                // 処理時間を記録
                $diff_time = CodeUtil::dateDifference($end_time, $start_time, '%h 時間 %i 分 %s 秒');

                // 呼び出しスクリプトは /home/dev/bash_CsvLongUpdate.bh

                // 完了メール送信
                $stage_title = config('original.title');
                mutSendMail(
                    config('original.mail_to'),                     // 宛先メールアドレス
                    $stage_title."からcsv取り込みのお知らせ",       // タイトル
                    "【完了】".$this->description."が完了しました\n"
                    ."　開始時間：".$start_time."\n"
                    ."　終了時間：".$end_time,                  // 内容
                    config('original.mail_from'),                   // 送信者メールアドレス
                    $stage_title                                    // 送信者名
                );

                // 処理終了
                info($this->description.'処理が終了しました');
                info('開始日時：' . $start_time);
                info('終了日時：' . $end_time);
                info('処理時間：' . $diff_time);

                // 処理の履歴を登録、更新する
                UploadHistory::merge('88', '0', '0');
            }
        } finally {
            // CSV実行中の仮ファイル
            CodeUtil::doubleRunCheck(false, $csvFileUpdate);
            // 仮ファイル削除
            CodeUtil::doubleRunCheck(false, $processFile);
        }
    }

}
