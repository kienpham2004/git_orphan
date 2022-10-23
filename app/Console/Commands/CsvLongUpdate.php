<?php

namespace App\Console\Commands;

use App\Commands\Csv\CsvConsoleUploadCommand;

//use App\Commands\Extract\ExtractDmTenkenLastCommand;
//use App\Commands\Extract\ExtractDmTenkenCommand;
//use App\Commands\Extract\ExtractDmSyakenCommand;
use App\Commands\Extract\ExtractInsuranceUpdateCommand;
use App\Commands\Extract\ExtractTargetCarsCommand;
use App\Commands\Extract\ExtractCreditCommand;
use App\Commands\Extract\ExtractInsuranceCommand;
use App\Commands\TableCleaning\TargetCarsFullCommand;
use App\Commands\TableCleaning\TargetCarsNotFullCommand;
use App\Commands\TableCleaning\CustomerCommand;
use App\Lib\Codes\Code;
use App\Models\Dm;
// 保険
use App\Commands\Extract\ExtractInsuranceNoContactCommand;

use App\Original\Util\CodeUtil;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;
// 独自
use OhInspection;
use App\Models\UploadHistory;
use App\Commands\Extract\UpdateLockFlg6Command;

class CsvLongUpdate extends Command {

    use DispatchesCommands;

    /**
     * The console command name.
     * C京都
     * @var string
     */
    protected $name = 'batch:CsvLongUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(主)表示用データの取込';

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
            $processFile = storage_path() . '/upload/check_long_batch';
            // ダブル実行チェック
            if (CodeUtil::doubleRunCheck(true, $processFile, 'BATCH')) {
                $this->comment(date('Y-m-d H:i:s') . " - CsvLongUpdate バッチは重複実行 ");
                exit;
            }
            ############################################################
            ## 接触管理データ
            ############################################################
            $custCount  = OhInspection::getScanFileCount( storage_path() . '/upload/notyet_customer' );
            if( $custCount >= 1 ){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start console csv upload 顧客データ.");
                    $this->dispatch( new CsvConsoleUploadCommand( $this,"3" ) );
                    $this->comment(date('Y-m-d H:i:s') ." - End console csv upload 顧客データ.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start extract target cars 対象車データ.");
                    $this->dispatch( new ExtractTargetCarsCommand() );
                    $this->comment( PHP_EOL.date('Y-m-d H:i:s') ." - End extract target cars 対象車データ.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start UpdateLockFlg6 表示ロックフラグ.");
                    $this->dispatch( new UpdateLockFlg6Command(1) );
                    $this->comment(date('Y-m-d H:i:s') ." - End UpdateLockFlg6 表示ロックフラグ.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n";
                }
            }

            // 月末だけ実行
            if( date("t") == date("d")){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start UpdateLockFlg6 表示ロックフラグ（月末チェック）.");
                    $this->dispatch( new UpdateLockFlg6Command() );
                    $this->comment(date('Y-m-d H:i:s') ." - End UpdateLockFlg6 表示ロックフラグ（月末チェック）.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n";
                }
            }


            ############################################################
            ## 保険データ
            ############################################################
            $hokenCount = OhInspection::getScanFileCount( storage_path() . '/upload/notyet_hoken' );
            if( $hokenCount >= 1 ){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start console csv upload 保険データ.");
                    $this->dispatch( new CsvConsoleUploadCommand( $this, "20" ) );
                    $this->comment(date('Y-m-d H:i:s') ." - End console csv upload 保険データ.");
                    $this->comment("");
                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start console 未接触敗戦 保険データ.");
                    $this->dispatch( new ExtractInsuranceNoContactCommand() );
                    $this->comment(date('Y-m-d H:i:s') ." - End console 未接触敗戦 保険データ.");
                    $this->comment("");

                    // 保険のデータ更新を行う 20200702 add
                    $this->dispatch( new ExtractInsuranceUpdateCommand());

                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n", $e;
                }

            }

            /*
            // csvがアップロードされているときに動作
            if( $custCount >= 1 ){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start extract credit cars クレジットデータ.");
                    $this->dispatch( new ExtractCreditCommand() );
                    $this->comment(date('Y-m-d H:i:s') ." - End extract credit cars クレジットデータ.");
                    $this->comment("");

                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n";
                }

            }

            // csvがアップロードされているときに動作
            if( $custCount >= 1 ){
                try {
                    $this->comment(date('Y-m-d H:i:s') ." - Start extract insurance cars 保険データ.");
                    $this->dispatch( new ExtractInsuranceCommand() );
                    $this->comment(date('Y-m-d H:i:s') ." - End extract insurance cars 保険データ.");
                    $this->comment("");

                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n";
                }

            }
            */

            ##############################
            ## DMデータの抽出
            ##############################

            // 1日を過ぎたら（10日以前）
            /*
            if( date("d") > "01" && date("d") < "10" ){
                try {

                    ##############################
                    ## （点検）点検
                    ##############################

                    // 当月からみて、2ヶ月先を対象月とする
                    $target_ym = date( "Ym", strtotime(date("Y-m-01"). "+2 month" ) );

                    // 対象月のデータがあるかを確認
                    $targetDmCount = Dm::where( "dm_inspection_ym", "=", $target_ym )
                                       ->whereNotIn( 'dm_inspection_id', [4] ) // 点検のみ
                                       ->count();

                    // 対象月のデータが空の時指定された月のデータを取得
                    if( empty( $targetDmCount ) == True ){
                        $this->comment(date('Y-m-d H:i:s') ." - Start extract dm cars DMデータ(点検).");
                        $this->dispatch( new ExtractDmTenkenCommand( $target_ym ) );
                        $this->comment(date('Y-m-d H:i:s') ." - End extract dm cars DMデータ(点検).");
                        $this->comment("");
                    }

                    ##############################
                    ## （点検）車検6ヶ月前
                    ##############################

                    // 当月からみて、2ヶ月先を対象月とする
                    $target_ym = date( "Ym", strtotime( "+2 month" ) );

                    // 対象月のデータがあるかを確認
                    $targetDmCount = Dm::where( "dm_inspection_ym", "=", $target_ym )
                                       ->whereIn( 'dm_inspection_id', [22] ) // 車検6ヶ月のコードが22
                                       ->count();

                    // 対象月のデータが空の時指定された月のデータを取得
                    if( empty( $targetDmCount ) == True ){
                        $this->comment(date('Y-m-d H:i:s') ." - Start extract dm cars DMデータ(車検6ヶ月前).");
                        $this->dispatch( new ExtractDmTenkenLastCommand( $target_ym ) );
                        $this->comment(date('Y-m-d H:i:s') ." - End extract dm cars DMデータ(車検6ヶ月前).");
                        $this->comment("");
                    }

                    ##############################
                    ## （車検）早期入庫
                    ##############################

                    // 当月からみて、3ヶ月先を対象月とする
                    $target_ym = date( "Ym", strtotime( "+3 month" ) );

                    // 対象月のデータがあるかを確認
                    $targetDmCount = Dm::where( "dm_inspection_ym", "=", $target_ym )
                                       ->whereIn( 'dm_inspection_id', [4] ) // 車検のコードが4
                                       ->count();

                    // 対象月のデータが空の時指定された月のデータを取得
                    if( empty( $targetDmCount ) == True ){
                        $this->comment(date('Y-m-d H:i:s') ." - Start extract dm cars DMデータ(車検).");
                        $this->dispatch( new ExtractDmSyakenCommand( $target_ym ) );
                        $this->comment(date('Y-m-d H:i:s') ." - End extract dm cars DMデータ(車検).");
                        $this->comment("");
                    }

                } catch (\Exception $e) {
                    echo "例外キャッチ：", $e->getMessage(), "\n";
                }

            }*/


            ############################################################
            ## 重複データがあった時の処理
            ############################################################
            /*
            if( $custCount >= 1 ){

                // 重複データの削除対象をバックアップ
                $this->comment(date('Y-m-d H:i:s') ." - Start tb_customerの重複データ解消.");
                $this->dispatch( new CustomerCommand() );
                $this->comment(date('Y-m-d H:i:s') ." - End tb_customerの重複データ解消.");

                // 重複データの削除対象をバックアップ
                $this->comment(date('Y-m-d H:i:s') ." - Start tb_target_carsの重複データ解消.");
                // 完全一致であるターゲットカーズの重複チェック
                $this->dispatch( new TargetCarsFullCommand() );
                // 完全一致でないターゲットカーズの重複チェック
                $this->dispatch( new TargetCarsNotFullCommand(
                    TargetCarsNotFullCommand::CHECKING_TODAY
                ));
                $this->comment(date('Y-m-d H:i:s') ." - End tb_target_carsの重複データ解消.\n");

            }
            */

            ############################################################
            ## メール・ログ処理
            ############################################################
            if( $custCount >= 1 || $hokenCount >= 1){

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

                if ($custCount >= 1){
                    // 処理の履歴を登録、更新する
                    UploadHistory::merge('99', '0', '0');
                }
            }
        } finally {
            CodeUtil::doubleRunCheck(false, $processFile);
        }
    }
}
