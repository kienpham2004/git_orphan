<?php

namespace app\Lib\Csv;

/**
 * エラーがあったかどうかの処理を表示する
 */
class CsvImportResult
{
    private $success = array();
    private $errors = array();
    private $totalCount = 0;

    #############
    ## 成功
    #############
    
    public function add($row){
        $this->success[] = $row;
    }

    public function success(){
        return $this->success;
    }

    public function successCount(){
        return count( $this->success );
    }

    #############
    ## エラー
    #############
    
    public function addError( $row ){
        $this->errors[] = $row;
    }

    public function errors(){
        return $this->errors;
    }

    public function errorCount(){
        return count($this->errors);
    }

    public function hasError(){
        return $this->errorCount() > 0;
    }

    #############
    ## 総数
    #############

    public function setTotalCount( $count ){
        $this->totalCount = $count;
    }

    public function totalCount(){
        return $this->totalCount;
    }

    // debug用
    public function debug() {
        \Log::debug('インポート処理の結果-------------------------------------');
        \Log::debug('処理対象件数：' . $this->totalCount() . ' エラー件数：' . count($this->errors()) . ' 正常件数：' . count($this->success()));
        \Log::debug($this->errors());
        \Log::debug('-------------------------------------------------------');
    }

}
