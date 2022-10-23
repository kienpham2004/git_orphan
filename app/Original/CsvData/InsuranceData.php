<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Models\Insurance;
use App\Models\UserAccount;
use App\Original\Util\CodeUtil;

/**
 * 保険用CSVデータ
 *
 * @author yhatsutori
 *
 */
class InsuranceData{

    use tCsvImport;
    
    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    protected $_cols = [
        1     => 'insu_inspection_target_ym', // 対象年月

        3     => 'insu_jisya_tasya', // 自社・他社
        5     => 'insu_base_code_init', // 拠点コード
        6     => 'insu_base_name_csv', // 拠点名称
        7     => 'insu_insurance_end_date', // 満期日
        8     => 'insu_insurance_start_date', // 保険始期日
        9     => 'insu_user_code_init', // 担当者コード
        10    => 'insu_user_name_csv', // 担当者名
        11    => 'insu_kikan', // 保険期間
        12    => 'insu_syumoku_code', // 保険種目コード
        13    => 'insu_syumoku', // 保険種目
        14    => 'insu_company_code', // 保険会社コード
        15    => 'insu_company_name', // 保険会社名
        16    => 'insu_uketuke_kbn_code', // 受付区分コード
        17    => 'insu_uketuke_kbn', // 受付区分
        18    => 'insu_customer_name', // 契約者名
        19    => 'insu_tel_no', // TEL NO
        20    => 'insu_syoken_number', // 証券番号
        21    => 'insu_syadai_number', // 車台番号
        22    => 'insu_car_base_number', // 登録番号
        23    => 'insu_syaryo_type', // 車両
        24    => 'insu_jisseki_hokenryo', //実績保険料
        25    => 'insu_daisya', // 代車
        26    => 'insu_tokyu', // 等級
        27    => 'insu_jinsin_syogai', // 人身傷害
        28    => 'insu_cashless_flg', // キャッシュレス
        29    => 'insu_kanyu_dairiten', // 加入代理店
        30    => 'insu_syoken_sindan_date', // 証券診断実施日
        31    => 'insu_keiyaku_kekka', // 契約結果（継続/成約日）

        //202001test用
//        31    => 'insu_syoken_extract_number', // 抽出番号
//        32    => 'insu_syoken_suishin_1', // 特別推進情報1
//        33    => 'insu_syoken_suishin_2', // 特別推進情報2
//        34    => 'insu_syoken_source' // 情報元
    ];

    /**
     * バリデーションルール
     */
    private $_validate_rules = [
        ############################
        ## 必須項目
        ############################

        'insu_syoken_number' => 'required', // 統合車両管理No

        ############################
        ## 日付チェック
        ############################

        'insu_insurance_end_date' => 'date', // 満期日
        'insu_insurance_start_date' => 'date', // 保険始期日
        'insu_syoken_sindan_date' => 'date' // 証券診断実施日
    ];

    /**
     * CSV外の値を埋め込む
     * 画面の選択値とうを埋め込む
     *
     * @param unknown $storeData
     * @param unknown $value
     * @return unknown
     */
    public function inject( $storeData, $value ){
        return $storeData;
    }

    /**
     * DBへの登録更新処理を行う
     *
     * @param unknown $storeData
     */
    public function store( $storeData ){
        /**
         * 登録する値の加工
         */
        // 拠点
        $storeData['insu_base_code_init'] = $this->convertBaseCode( $storeData['insu_base_code_init'] );

        $storeData['insu_car_base_number'] = $this->filterCarNumberSpace( $storeData['insu_car_base_number'] );
        
        // 担当者名を整理
        $storeData['insu_user_name_csv'] = str_replace( "　", " ", $storeData['insu_user_name_csv'] );
        $storeData['insu_user_name_csv'] = str_replace( "  ", " ", $storeData['insu_user_name_csv'] );
        $storeData['insu_user_name_csv'] = str_replace( ".", "", $storeData['insu_user_name_csv'] );

        // 担当者コードがある
        if($storeData['insu_user_code_init'] != null && trim($storeData['insu_user_code_init']) != "#N/A"){
            // 担当者
            $storeData['insu_user_code_init'] = $this->convertUserId( $storeData['insu_user_code_init'] );
            $storeData['insu_user_id'] = CodeUtil::getUserIdByCode($storeData['insu_user_code_init']);

        }else{
        	// 氏名からコードを取得する
            $userName = str_replace( ' ', '',$storeData['insu_user_name_csv']);
            $userInfo = UserAccount::getUserIdByName($storeData['insu_base_code_init'], $userName);
            if (!$userInfo->isEmpty()){
                echo (PHP_EOL.date('Y-m-d H:i:s') ." - UserIDがある→".$storeData['insu_user_name_csv']);
                $storeData['insu_user_id'] = $userInfo[0];
            }else{
                echo (PHP_EOL.date('Y-m-d H:i:s') ." - UserIDがなし→".$storeData['insu_user_name_csv']);
                $storeData['insu_user_id'] = null;
            }
            $storeData['insu_user_code_init']  = null;
        }

        // 対象月を格納
        $storeData["insu_inspection_ym"] = date( "Ym", strtotime( $storeData["insu_insurance_end_date"] ) );
        
        // 車両の値を取得
        $storeData["insu_syaryo_type"] = trim( $storeData["insu_syaryo_type"] );

        // 実績保険料の金額のカンマを除外
        $storeData["insu_jisseki_hokenryo"] = str_replace( ",", "", $storeData["insu_jisseki_hokenryo"] );

        // 実績保険料の値を取得
        if( empty( $storeData["insu_jisseki_hokenryo"] ) == True ){
            $storeData["insu_jisseki_hokenryo"] = 0;
        }

        // 代車の値を数値で保持
        if( $storeData["insu_daisya"] == "有" ){
            $storeData["insu_daisya"] = 1;
        }else if( $storeData["insu_daisya"] == "無" ){
            $storeData["insu_daisya"] = 0;
        }else{
            $storeData["insu_daisya"] = 0;
        }

        // キャッシュレスの値を数値で保持
        if( $storeData["insu_cashless_flg"] == "〇" ){
            $storeData["insu_cashless_flg"] = 1;
        }else{
            $storeData["insu_cashless_flg"] = 0;
        }

        // 契約結果（継続/成約日）のおかしなデータを初期化
        if( $storeData["insu_keiyaku_kekka"] == "** 未成約 **" ){
            $storeData["insu_keiyaku_kekka"] = NULL;
        }else if( $storeData["insu_keiyaku_kekka"] == "** 未継続 **" ){
            $storeData["insu_keiyaku_kekka"] = NULL;
        }
        
        // データを取り込める最大月を現在の月から三ヶ月後する
        //$maxtDate = date( "Ym", strtotime( date("Y-m-d") . "+3 month" ) );

        // 2018-01-29 hatsutori 変更
        // データを取り込める最大月を現在の月から四ヶ月後する
        $maxtDate = date( "Ym", strtotime( date("Y-m-d") . "+4 month" ) );
        
        // データを取り込める最大月(対象月)を現在の月から三ヶ月後よりも前の日付かを確認する
        if( $storeData["insu_inspection_target_ym"] <= $maxtDate ) {
            try {
                Insurance::merge($storeData);
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        }
    }

    /**
     * CSVカラムの定義を返送する
     *
     * @return string[]
     */
    public function getColumns() {
        return $this->_cols;
    }

    /**
     * バリデーションルールを返送する
     *
     * @return string[]
     */
    public function getValidateRules() {
        return $this->_validate_rules;
    }

    /**
     * CSV項目数を返送する
     *
     * @return number
     */
    public function getItemNum() {
//        return 34;202001test用
        return 31;
    }
    
    /*
     * エラーログ用にクラス名を返送する
     * 
     * @return string
     */
    public function getClassName() {
        $check_target = __CLASS__;
        $check_place  = strrpos($check_target, '\\') + 1;
        $class_name = substr($check_target, $check_place);
        
        return $class_name;
    }
}
