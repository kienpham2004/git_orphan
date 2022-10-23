<?php

namespace app\Lib\Csv;

use App\Lib\Csv\CsvImportResult;
// 独自
use OhInspection;

/**
 * CSVのアップロードの処理の本体
 */
class CsvImport{

    // 対応フィルパス
    private $importFile = "";

    /**
     * コンストラクタ
     * @param [type]  $csvImportObj    [description]
     * @param [type]  $filePath  ファイルパス
     * @param [type]  $parameter 画面入力値及びCSVインポート時に使用する値
     * @param integer $start     読み込み開始位置
     */
    private function __construct( $csvImportObj, $filePath, $parameter=null, $start=0 ){
        $this->csvImportObj = $csvImportObj;
        $this->filePath = $filePath; // ファイルパス
        $this->parameter = $parameter; // 検索条件を取得
        $this->start = $start; // 読み飛ばす列数

        $this->importFile = $filePath; // 対応フィルパス

        // 結果を取得する配列
        $this->result = new CsvImportResult();

        // file_get_contentsでメモリーオーバーになる時アリ
        set_time_limit(18432);
        ini_set('memory_limit', '8192M');
        //CSVファイルがSJISの場合、UTF-8に変換する
        $data = file_get_contents($filePath);
        
        if(mb_detect_encoding($data, "UTF-8, SJIS-win", true) == "SJIS-win"){
            $data = mb_convert_encoding($data, 'UTF-8', 'SJIS-win');
        }
        
        //顧客データ.csvのデータの不要な「"」削除用
        $data = str_replace("\"\",", ",", $data);
        $data = str_replace("\"\",\"\"", "\",\"", $data);
        $data = str_replace("\"\"\"", "\"", $data);

        //顧客データ.csvのデータの不要な「全角スペース」を半角スペースに置き換え
        $data = str_replace("　", " ", $data);

        // 20201102 ＣSVデータ不正文字変換
        $a = array('\"');
        $b = array('"');
        $data  = str_replace($a, $b, $data);

        $temp = tmpfile();
        $meta = stream_get_meta_data($temp);
        fwrite($temp, $data);
        rewind($temp);

        // CSVファイルを扱う際の便利オブジェクト	
        $this->csvFileObj = new \SplFileObject( $meta['uri'] );	
        $this->csvFileObj->setFlags( \SplFileObject::READ_CSV );
            
        // もともとの処理はコメントアウト
        // CSVファイルを扱う際の便利オブジェクト
        // $this->csvFileObj = new \SplFileObject( $this->filePath );
        // $this->csvFileObj->setFlags( \SplFileObject::READ_CSV );
        
        // 値の追加と編集を行うカラムを格納する変数
        $this->columnList = [];
    }

    /**
     * 自分自身のオブジェクトを返す
     * @param  [type]  $csvImportObj    [description]
     * @param  [type]  $filePath  ファイルパス
     * @param  [type]  $parameter 画面入力値及びCSVインポート時に使用する値
     * @param  integer $start     読み込み開始位置
     * @return [type]             [description]
     */
    public static function getInstance( $csvImportObj, $filePath, $parameter=null, $start=0 ){
        \Log::debug('getInstance!');

        // 自分自身のインスタンスを返す
        return new CsvImport( $csvImportObj, $filePath, $parameter, $start );
    }
    
    /**
     * csvのデータをDBに登録するメソッド
     *
     * $line => csvの1行分の配列
     * $soreData => csv1行分の連想配列（キーはDBのカラム名）
     */
    public function execute(){
        \Log::debug('execute!');
        echo PHP_EOL.date('Y-m-d H:i:s') ." - CsvImport execute(）start ";

        // 総数を保持
        $totalCount = 0;
        // メール送信の配列
        $arrayError = array();

        // 値の追加と編集を行うカラムを取得
        $this->columnList = collect( $this->csvImportObj->getColumns() );
        
        foreach( $this->csvFileObj as $row_num => $line ){
            \Log::debug('row=' . $row_num . 'start=' . $this->start );
                
            if( $row_num >= $this->start ) {
                // 値が入っているかを確認する為に配列の値を文字列に変換
                $lineText = implode("", $line);
                
                // 1行目が空でなければ動作
                if( !empty( $lineText ) == True ){
                    // 総数を加算
                    $totalCount += 1;

                    // 予め設定されているCSV項目数を比較
                    if( $this->isOkColsNum( $line ) == True ){
                        // ここでデータがおかしくなっている可能性あり。
                        list( $storeData, $errors ) = $this->csvImportObj->validate( $line );

                        // エラーがあれば
                        if( !empty( $errors ) ) {
                            $this->setErrorText($row_num, $line, $errors);
                            \Log::error(" - !empty( errors ) -> ");
                            \Log::error($errors);
                        } else {
                            try {
                                // CSV外の値を加工した値を取得
                                $storeData = $this->csvImportObj->inject( $storeData, $this->parameter );
            
                                // 値の登録を行う
                                $this->csvImportObj->store( $storeData );
                                
                                // 成功データとしてバッファに追加
                                $this->result->add( $storeData );


                            }catch( \Exception $e ){
                                // なんらかのエラーが発生した場合
                                //$this->result->addError($this->genMessage($row_num, $line, $e->getMessage()));
                                $errorMessage = substr($e->getMessage(),0,strpos($e->getMessage(),'Stack trace:'));
                                array_push($arrayError, "Row : " . $row_num . " -> ". $errorMessage);
                                \Log::error(" - Data update error -> ".PHP_EOL. $e);
                                echo date('Y-m-d H:i:s') ." - Data update error -> ".PHP_EOL. $e;
                            }
                        }

                    } else {
                        $errors = ["カラムの数が合いません(想定：" . $this->csvImportObj->getItemNum() . " 実際：" . count( $line ) . ")"];
                        \Log::error($errors);
                        $this->setErrorText($row_num, $line, $errors);
                    }
                }
            }
        }

        // エラーが有る場合、メールを送信する。
        if (count($arrayError) > 0) {
            $this->sendErrorMail($arrayError);
        }

        // 総件数の設定
        $this->result->setTotalCount( $totalCount );
        echo PHP_EOL.date('Y-m-d H:i:s') ." - CsvImport execute(）end ".$totalCount;
    }
    
    /**
     * csvのデータを取り込めるかチェック
     *
     * $line => csvの1行分の配列
     * $soreData => csv1行分の連想配列（キーはDBのカラム名）
     */
    public function checkError(){
        \Log::debug('checkError!');
            
        // 総数を保持
        $totalCount = 0;

        // 値の追加と編集を行うカラムを取得
        $this->columnList = collect( $this->csvImportObj->getColumns() );
        
        foreach( $this->csvFileObj as $row_num => $line ){
            \Log::debug('row=' . $row_num . 'start=' . $this->start );

            // CSVの1行目で基本判定
            if( $row_num == ($this->start - 1) ){
                // 値が入っているかを確認する為に配列の値を文字列に変換
                $lineText = implode("", $line);
                // 空の場合
                if( empty( $lineText ) == True ){
                    $errors = ["CSVファイルが正しくありません(". $this->csvImportObj->getClassName(). ")"];
                    $this->setErrorText($row_num, $line, $errors);
                    return;
                }
                // カラム数が違う場合
                elseif( $this->isOkColsNum( $line ) == FALSE ){
                    $errors = ["カラムの数が合いません【想定：". $this->csvImportObj->getItemNum(). "(". $this->csvImportObj->getClassName().") 実際：". count( $line ). "】" ];
                    $this->setErrorText($row_num, $line, $errors);
                    return;
                }
            }
            

            // 値の処理（CSV毎に違う読み飛ばし）
            if( $row_num >= $this->start ) {
                // 値が入っているかを確認する為に配列の値を文字列に変換
                $lineText = implode("", $line);
                
                // 1行目が空でなければ動作
                if( !empty( $lineText ) == True ){
                    // 総数を加算
                    $totalCount += 1;

                    // 予め設定されているCSV項目数を比較
                    if( $this->isOkColsNum( $line ) == True ){
                        // ここでデータがおかしくなっている可能性あり。
                        list( $storeData, $errors ) = $this->csvImportObj->validate( $line );

                        // エラーがあれば
                        if( !empty( $errors ) ) {
                            $this->setErrorText($row_num, $line, $errors);
                        }

                    } else {
                        $errors = ["カラムの数が合いません【想定：". $this->csvImportObj->getItemNum(). "(". $this->csvImportObj->getClassName().") 実際：". count( $line ). "】" ];
                        $this->setErrorText($row_num, $line, $errors);
                        return;

                    }
                }
            }
        }

        // 総件数の設定
        $this->result->setTotalCount( $totalCount );
    }

    /**
     * [isWrite description]
     * @param  [type]  $no [description]
     * @return boolean     [description]
     */
    protected function isWrite( $no ){
        return $this->columnList->has( $no );
    }

    /**
     * アップロードされたcsvのヘッダの数と
     * 予め設定されているCSV項目数を比較
     * @param  [type]  $line [description]
     * @return boolean       [description]
     */
    protected function isOkColsNum( &$line ){
        \Log::debug('item=' . $this->csvImportObj->getItemNum() );
        \Log::debug('line cols=' . count($line));
        
        // 不要なスペースが入る可能性がある為処理を変更
        if( ( $this->csvImportObj->getItemNum() >= count( $line ) ) == False ){
            for( $num = 0; $num < $this->csvImportObj->getItemNum(); $num++ ){
                if( !isset( $line[$num] ) == True ){
                    $line[$num] = "";
                }
            }
        }
        return $this->csvImportObj->getItemNum() == count( $line );
    }

    /**
     * エラーメッセーを生成するメソッド
     * @param  int $row
     * @param   $line    エラー行
     * @param   $message エラーメッセージ
     * @return  array
     */
    protected function genMessage( $row, $line, $message ){
        return array(
            'row' => $row + 1,
            'line' => implode( ',', $line ),
            'message' => implode( ',', $message )
        );
    }
    
    /**
     * エラーをセット
     * @param   string  $row_num    Description
     * @param   array   $line       Description
     * @param   mixed   $errors     Description
     */
    protected function setErrorText( $row_num, $line, $errors ) {
        $this->result->addError($this->genMessage($row_num, $line, $errors));
    }

    /**
     * エラーメールを送信する。
     * @param $arrayError
     */
    private function sendErrorMail($arrayError){
        // エラーの一覧
        $errorList = "";
        foreach ($arrayError as $i => $value) {
            $errorList = $errorList . $value;
        }

        // 完了メール送信
        $stage_title = config('original.title');
        // メール送信
        $mail_to      = config('original.mail_to');
        $mail_from    = config('original.mail_from');
        $mail_title   = 'Error update from CSV '. $stage_title;
        $mail_message = $stage_title. "\n" . "エラー内容　：\nFile path : ".$this->importFile."\n". $errorList;
        mutSendMail($mail_to, $mail_title, $mail_message, $mail_from, '');
    }
}
