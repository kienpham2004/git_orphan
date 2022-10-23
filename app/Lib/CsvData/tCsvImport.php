<?php

namespace App\Lib\CsvData;

use Validator;

/**
 * CSVインポート処理のトレイト
 *
 * @author yhatsutori
 *
 */
trait tCsvImport {

    private $_values;

    private $messages = [
            'required' => ':attributeは必須指定項目です'
    ];

    /**
     * 取り込み時に拠点コードの変換処理を行う
     *
     * @param unknown $value
     */
    public function convertBaseCode($value) {
        if(!empty($value)) {
            return sprintf("%'.02s", $value);
        }
        return $value;
    }

    /**
     * 取り込み時に担当者コードの変換処理を行う
     *
     * @param unknown $value
     */
    public function convertUserId($value) {
        if(!empty($value)) {
            return sprintf("%'.03s", $value);
        }
        return $value;
    }

    /**
     * 取り込み時に統合車両管理Noの変換処理を行う
     *
     * @param unknown $value
     */
    public function convertCarManageNumber($value) {
        if(!empty($value)) {
            return ltrim($value, '0');
        }
        return $value;
    }

    /**
     * 取り込み時に顧客コードの変換処理を行う
     *
     * @param unknown $value
     */
    public function convertCustomerCode($value) {
        if(!empty($value)) {
            return ltrim($value, '0');
        }
        return $value;
    }

    /**
     * スペースを除去する（全角、半角すべて）
     *
     * @param unknown $value
     */
    public function filterSpace($value) {
        if(!empty($value)) {
            $value = str_replace('　', '', $value);
            $value = str_replace(' ', '', $value);
            return $value;
        } else {
            return $value;
        }
    }

    /**
     * 車両No用のデータバリデーション
     * スペースを除去する（全角、半角すべて）
     * 半角カナを全角ひらがなに変更
     * @param unknown $value
     */
    public function filterCarNumberSpace($value) {
        if(!empty($value)) {
            $value = str_replace('　', '', $value);
            $value = str_replace(' ', '', $value);
            // 半角カタカナを全角ひらがなに変更
            $value = mb_convert_kana($value, "H");
            return $value;
        } else {
            return $value;
        }
    }

    /**
     * 電話番号(customer_tel, customer_office_tel)が「--」の場合、空文字に変換する
     *
     * @param unknown $value
     */
    public function convertTel($value) {
        if('--' == $value
            || 'ーー' == $value) {

            return '';
        } else {
            return $value;
        }
    }

    /**
     * 最終点検実施(ciao_jisshi)が「!!」「-」の場合、空文字に変換する
     *
     * @param unknown $value
     */
    public function convertCiaoJissi($value) {
        if('!!' == $value
            || '-' == $value
            || '！！' == $value
            || 'ー' == $value) {

            return '';
        } else {
            return $value;
        }
    }

    /**
     * バリデーションを実行する
     *
     * @param unknown $row
     */
    public function validate($row) {
        // 登録対象値の作成
        $this->_values = $this->genRegistValues($row);
        // バリデーション実行
        $validator = Validator::make($this->_values , $this->getValidateRules(), $this->messages);
        if($validator->fails()) {
            $messages = $validator->messages()->all();
            foreach($messages as $message) {
                $errors[] = $message;
            }
            return array($this->_values, $errors);
        } else {
            return array($this->_values, null);
        }
    }

    /**
     * 登録値を作成する
     *
     * @param unknown $row
     */
    public function genRegistValues($row) {
        foreach ($this->getColumns() as $key => $value) {
            $v = trim($row[$key - 1]);
            if(!empty($v)) {
                $result[$value] = $v;
            } else {
                $result[$value] = null;
            }
        }
        return $result;
    }
    
    /**
     * 日付データのYY/MM/DD形式をYYYY/MM/DD形式に変換する
     *
     * @param unknown $value
     */
    public function convertData($value) {
        $orgData = $value;
        
        //日付データを年月日に分割
        $data = explode("/", $orgData);
        //年が2文字(YY)の場合、4文字(YYYY)になる様に「20」を頭に追加
        if(mb_strlen($data[0]) == 2){
            $orgData = "20".$orgData;
        }
        
        return $orgData;
    }

    /**
     * 担当者コードチェック
     * @param $user_code 担当者コード
     * @param $user_id　担当者Id
     * @return string 戻りメッセージ
     */
    public function checkUserCode($user_code, $user_id) {
        $message = "";
        if(empty($user_code)) {
            $message =  "担当者コードは空欄です";
        }elseif($user_id == 0){
            $message =  "担当者コード「".$user_code."」 は未登録です";
        }
        return $message;
    }

    /**
     * CSVのカラム構成を取得する
     *
     */
    abstract public function getColumns();

    /**
     * バリデーションルールを取得する
     *
     */
    abstract public function getValidateRules();
}
