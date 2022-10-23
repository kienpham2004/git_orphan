<?php

namespace App\Commands\Csv;

use App\Original\Util\CodeUtil;
use App\Commands\Command;
use App\Commands\Csv\tCsvUpload;
use App\Events\UploadedEvent;
use App\Models\UserAccount;
use App\Http\Requests\CsvUploadRequest;
// 独自
use OhInspection;
use Log;

/**
 * csvアップロード
 *
 * @author yhatsutori
 */
class CsvUploadCommand extends Command{

    use tCsvUpload;
    
    /**
     * コンストラクタ
     * @param CsvUploadRequest $requestObj 検索オブジェクト
     * @param boolean          $webExeFlg  web画面上で更新するかどうかのフラグ
     */
    public function __construct( CsvUploadRequest $requestObj, $webExeFlg=False ){
        $this->requestObj = $requestObj;
        $this->webExeFlg = $webExeFlg;
        $this->file_type = $requestObj->file_type;
    }

    /**
     * メインの処理
     */
    public function handle(){
        // ファイルを格納するディレクトリ名を取得
        $csvDirName = CodeUtil::getCsvDirCode( $this->file_type );

        // 活動リストと見込みリストの時に動作
        if ( in_array( $this->file_type, ["1","2"] ) == True ){
            // アップロードのディレクトリの名を取得
            $csvDir = storage_path() . '/upload/' . $csvDirName;
        }else{
            // アップロードのディレクトリの名を取得
            $csvDir = storage_path() . '/upload/notyet_' . $csvDirName;
        }
        
        // CSVアップロードしたファイルのパスを取得
        $csvFilePath = OhInspection::saveCsv( $csvDir );
        
        // CSVの取り込みを行うオブジェクトを取得
        $csvImportObj = $this->getInstance( $this->file_type, $csvFilePath );
        
        // CSVファイルの取り込み処理
        // CsvImportのメソッドを実行
        $csvImportObj->checkError();
        
        // 基本・個別・見込みファイルの時はWebからアップロード
        if( in_array( $this->file_type, ["1","2"] ) == True || $this->webExeFlg == True ){

            // CSVファイルの取り込み処理
            // CsvImportのメソッドを実行
            $csvImportObj->execute();

            // アップロード履歴を登録、更新する
            \Event::fire(
                new UploadedEvent(
                    new UserAccount(),
                    $csvImportObj->result->totalCount(),
                    $this->file_type
                )
            );

            // 顧客データの時に顧客データの有無のデータを取得
            if( $this->file_type == "3" ){
                // CSVの取り込みを行うオブジェクトを取得
                $csvImportObj = $this->getInstanceUmu( $this->file_type, $csvDir . "/" . $value );

                // CSVファイルの取り込み処理
                // CsvImportのメソッドを実行
                $csvImportObj->execute();

            }

        }
        
        return $csvImportObj;
    }
    
    /**
     * CSVアップロードのエラー時のメール送信・ログ出力
     * @param array     $csverrors      調査対象（エラー配列）
     * @param string    $check_target   ファイルパス
     */
    public static function csvErrorCheck( $csverrors, $check_target ) {
        
        // 対象ファイル名を抽出
        $check_place = strrpos($check_target, 'jp/') + 3;
        $address_name = substr($check_target, $check_place);
        
        // エラー判定
        if( !empty($csverrors) ){                
            $cnt = 0;
            foreach($csverrors as $key => $val){
                foreach($val as $err_key => $err_txt){
                    // どのエラーか調査（必須項目・カラム数・正しくない）
                    if( strpos($err_txt, "必須") !== FALSE ){
                        // カラム名を取得
                        $str_pos = mb_strpos($err_txt, "は");
                        $err_item= mb_substr($err_txt, 0, $str_pos);
                        // エラー内容の文言
                        $err_num = "必須項目不足（".$err_item."）";
                        // エラー件数の取得
                        $cnt++;
                    }if( strpos($err_txt,'カラムの数が合いません') !== FALSE ){
                        $err_col = "CSVカラム数が違います";
                    }if( strpos($err_txt,'正しくありません') !== FALSE ){
                        $err_col = "CSVファイルが正しくありません";
                    }
                }
            }
            
            // エラー内容テキストの作成
            if( isset($err_num) ){
                $err_detail = $err_num.'= '.$cnt.'件  ※複数項目の可能性有';
            }elseif( isset($err_col) ){
                $err_detail = $err_col;
                $csverrors  = $csverrors[0];
            }else{
                $err_detail = 'other error';
            }

            // ログ出力
            Log::error('発生場所 ------- '.__METHOD__);
            Log::error('アドレス ------- '.$address_name);
            Log::error('エラー内容 ----- '.$err_detail);
            Log::error('エラー詳細 ----- ');
            Log::error(print_r($csverrors, TRUE));
            
            // 屋号
            $stage_title= "【".config('original.title')."】";
            
            // デバイス名
            $sapiName = php_sapi_name();
            if ($sapiName == 'apache2handler') {
                $sapiName = 'web';
            } else if ($sapiName == 'cli') {
                $sapiName = 'console';
            }
            
            // メール送信
            $mail_to      = config('original.mail_to');
            $mail_from    = config('original.mail_from');
            $mail_title   = 'Csv Upload Error'. $stage_title;
            $mail_message =
                    $stage_title. "\n"
                    . "アドレス　　：　". $address_name. "\n"
                    . "エラー内容　：　". $err_detail. "\n"
                    . "エラー詳細　：　storage/logs/debug-{$sapiName}-". date('Y-m-d'). ".log\n";

            mutSendMail($mail_to, $mail_title, $mail_message, $mail_from, '');
            
        }
        
    }

}
