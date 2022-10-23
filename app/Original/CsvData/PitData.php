<?php

namespace App\Original\CsvData;

use App\Lib\CsvData\tCsvImport;
use App\Lib\Util\DateUtil;
use App\Models\Append\Pit;
use App\Original\Util\CodeUtil;

/**
 * PIT CSV用データ
 * @author yhatsutori
 *
 */
class PitData{

    use tCsvImport;

    /**
     * カラムのリスト
     * CSVのカラム順と以下の順が一致している必要がある
     */
    private $_cols = [
        3   => 'rst_base_code_init',        // 拠点CD
        5   => 'rst_accept_date',           // 受付日時
        16  => 'rst_customer_code',         // 統合顧客CD
        17  => 'rst_customer_name',         // 氏名
        24  => 'rst_user_code_init',        // 担当営業CD
        25  => 'rst_user_name_csv',             // 担当営業氏名
        26  => 'rst_user_base_code',        // 担当営業所属拠点CD
        28  => 'rst_car_name',              // 車名
        29  => 'rst_manage_number',         // 統合車輌管理番号
        35  => 'rst_start_date',            // 作業開始日時
        36  => 'rst_end_date',              // 作業終了日時
        37  => 'rst_detail',                // 作業内容
        38  => 'rst_hosyo_kbn',             // 保証区分
        39  => 'rst_youmei',                // 用命事項1
        40  => 'rst_hikitori_value',        // 引取有無
        41  => 'rst_put_in_date',           // 入庫日時
        43  => 'rst_get_out_date',          // 出庫日時
        46  => 'rst_daisya_value',          // 代車有無
        49  => 'rst_reserve_commit_date',   // 予約承認日時
        50  => 'rst_reserve_status',        // 状況
        51  => 'rst_work_put_date',         // 作業進捗：入庫日時
        58  => 'rst_delivered_date',        // 作業進捗：納車済日時
        85  => 'rst_syaken_next_date',      // 次回車検日
        86  => 'rst_matagi_group_number',   // 日またぎ作業グループ番号
        87  => 'rst_machi_seibi_value',     // お待ち整備
        88  => 'rst_web_reserv_flg'         // Web予約
    ];

    private $_validate_rules = [
        'rst_manage_number' => 'required',      // 統合車両管理No
        'rst_detail' => 'required',             // 作業内容
        'rst_syaken_next_date' => 'required'	// 次回車検
    ];



    /**
     * CSV外の値を埋め込む
     *
     * @param unknown $storeData
     * @param unknown $value
     * @return unknown
     */
    public function inject($storeData, $value) {
        return $storeData;
    }
    /**
     * DBへの登録更新処理を行う
     *
     * @param unknown $storeData
     */
    public function store( $storeData ) {
        // 拠点
        $storeData['rst_base_code_init'] = $this->convertBaseCode( $storeData['rst_base_code_init'] );
        // 担当者
        $storeData['rst_user_code_init'] = $this->convertUserId( $storeData['rst_user_code_init'] );
        $userId = CodeUtil::getUserIdByCode($storeData['rst_user_code_init']);
        if ($userId == "" || $userId == NULL) {
            $userId = 0;
        }
        $storeData['rst_user_id'] = $userId ;


        $storeData['rst_manage_number'] = $this->convertCarManageNumber( $storeData['rst_manage_number'] );
        $storeData['rst_customer_code'] = $this->convertCustomerCode( $storeData['rst_customer_code'] );

        $storeData['rst_accept_date'] = DateUtil::toTimestamp( $storeData['rst_accept_date'] );
        $storeData['rst_start_date'] = DateUtil::toTimestamp( $storeData['rst_start_date'] );
        $storeData['rst_end_date'] = DateUtil::toTimestamp( $storeData['rst_end_date'] );
        $storeData['rst_put_in_date'] = DateUtil::toTimestamp( $storeData['rst_put_in_date'] );
        $storeData['rst_get_out_date'] = DateUtil::toTimestamp( $storeData['rst_get_out_date'] );
        $storeData['rst_reserve_commit_date'] = DateUtil::toTimestamp( $storeData['rst_reserve_commit_date'] );
        $storeData['rst_work_put_date'] = DateUtil::toTimestamp( $storeData['rst_work_put_date'] );
        $storeData['rst_delivered_date'] = DateUtil::toTimestamp( $storeData['rst_delivered_date'] );

        // 統合車両管理Noの値があるときにmerge
        if( isset( $storeData['rst_manage_number'] ) == True && !empty( $storeData['rst_manage_number'] ) == True ){
            /*
            // ※サービス来場予約リストがあるため、コメントアウト
            //  指定された作業コードでない時に処理
            if( !in_array( $storeData['rst_detail'], ['新車整備','板金塗装','一般・他','中一般・他'] ) == True ){
                Pit::merge( $storeData );
            }
            */
            //　データ登録
            try{
                Pit::merge( $storeData );
            
            }catch( \Exception $e ){
                throw new \Exception($e);
//                $e->getMessage();
//
//                info('---------------- '.'Pit::merge');
//                info('発生場所 ------- '.__METHOD__);
//                info('クラス名 ------- '.__CLASS__);
//                info('エラー内容 ----- '."\n".$e->getMessage()."\n");
            }
            
        }
    }
    
    /**
     * CSVカラム定義を返送する
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
        //update from 180 ->181 on 20220601
        return 181;
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
