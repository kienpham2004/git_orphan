<?php
/**
 * Define constant
 *
 * @copyright © Mutsumi Group.
 * @link https://www.mut.co.jp/
 */
namespace App\Lib\Util;
/**
 * This class define constant for agent.
 *
 */
class Constants
{
    // 権限定義 -------------------------------------------
    const P01 = 1; //管理者
    const P02 = 2; //部長
    const P03 = 3; //本社
    const P04 = 4; //店長
    const P05 = 5; //工場長、サービス
    const P06 = 6; //営業担当
    const P07 = 7; //CS

    //画面一覧 --------------------------------------------
    const T00 = "T00"; //トップ

    //活動進捗グラフ
    const R00 = "R00"; // トレジャーボード(車検意向)
    const R01 = "R01"; // 〃　詳細
    const R10 = "R10"; // トレジャーボード(点検意向)
    const R11 = "R11"; // 〃　詳細

    // 実施リスト
    const J00 = "J00"; // 車検リスト
    const J01 = "J01"; //　〃詳細
    const J10 = "J10"; // 点検リスト
    const J11 = "J11"; //　〃詳細
    const J20 = "J20"; // 顧客マスターリスト

    // 実績分析
    const B00 = "B00"; // 拠点・担当者別実施率(車検)
    const B10 = "B10"; // 拠点・担当者別実施率(点検)
    const B20 = "B20"; // 車種別防衛率
    const B30 = "B30"; // 拠点・担当者別防衛率
    const B40 = "B40"; // 意向結果

    //　保険
    const H00 = "H00"; // 自社継続推進リスト
    const H01 = "H01"; // 〃　詳細
    const H10 = "H10"; // 他社・新規管理トレジャーボード
    const H11 = "H11"; // 〃　詳細
    const H20 = "H20"; // 拠点・担当者別 保険集計表
    const H21 = "H21"; // 〃　詳細
    const H30 = "H30"; // 担当者数登録(拠点毎)

    //　本社管理
    const M00 = "M00"; // アップロード
    const M10 = "M10"; // 拠点
    const M20 = "M20"; // 担当者
    const M30 = "M30"; // お知らせ

    //セッションキー一覧--------------------------------------
    const  SEC_ACCOUT_PERMISION = 'ACCOUT_PERMISION'; // ユーザー権限リスト
//    const  SEC_VALIDATION_ERR = 'VALIDATION_ERR'; // チェックエラー
//    const  SEC_ERROR = 'ERROR'; // チェックエラー
    const  SEC_PROCESS_SCREEN_ID = 'PROCESS_SCREEN_ID'; // 画面ID処理

    const  SEC_TARGET_DATA_MIN = 'TARGET_DATA_MIN'; // tb_target_carsテーブルのMinデータ
    const  SEC_TARGET_DATA_MAX = 'ARGET_DATA_MAX'; // tb_target_carsテーブルのMaxデータ

    const  SEC_CUSTOMER_DATA_MIN = 'CUSTOMER_DATA_MIN'; // tb_customerテーブルのMinデータ
    const  SEC_CUSTOMER_DATA_MAX = 'CUSTOMER_DATA_MAX'; // tb_customerテーブルのMaxデータ

    const  SEC_INSURANCE_DATA_MIN = 'INSURANCE_DATA_MIN'; // tb_insuranceテーブルのMinデータ
    const  SEC_INSURANCE_DATA_MAX = 'INSURANCE_DATA_MAX'; // tb_insuranceテーブルのMaxデータ
    const  SEC_INSURANCE_JISHA_DATA_MIN = 'INSURANCE_JISHA_DATA_MIN'; // tb_insuranceテーブルの自社のMinデータ
    const  SEC_INSURANCE_JISHA_DATA_MAX = 'INSURANCE_JISHA_DATA_MAX'; // tb_insuranceテーブルの自社のMaxデータ

    const  SEC_INSURANCE_TASHA_DATA_MIN = 'INSURANCE_TASHA_DATA_MIN'; // tb_insuranceテーブルの他社のMinデータ
    const  SEC_INSURANCE_TASHA_DATA_MAX = 'INSURANCE_TASHA_DATA_MAX'; // tb_insuranceテーブルの他社のMaxデータ

    const  SEC_INSURANCE_RESULT_DATA_MIN = 'INSURANCE_RESULT_DATA_MIN'; // tb_insuranceテーブルのMinデータ
    const  SEC_INSURANCE_RESULT_DATA_MAX = 'INSURANCE_RESULT_DATA_MAX'; // tb_insuranceテーブルのMaxデータ

    const  SEC_INSURANCE_END_DATA_MIN = 'INSURANCE_END_DATA_MIN'; // tb_target_carsテーブルのtgc_customer_insurance_end_dateのMinデータ
    const  SEC_INSURANCE_END_DATA_MAX = 'INSURANCE_END_DATA_MAX'; // tb_target_carsテーブルのtgc_customer_insurance_end_dateのMaxデータ

    const  SEC_MAINTEN_FLAG = 'MAINTEN_FLAG'; // メンテナンス前のメッセージが出るフラグ

    const CONS_JISYA = '自社分'; //　自社分
    const CONS_TASYA = '他社分'; //　他社分
    const CONS_SHINKI = '純新規'; //　純新規
    const CONS_OK = 'OK';
    const CONS_NG = 'NG';
    const CONS_ERROR = 'ERROR';

    const CONS_TAISHOKUSHA_CODE = '***'; // 退職者コード

    //テーブル一覧
    const  TB_TARGET_CARS = 'tb_target_cars';
    const  TB_CUSTOMER = 'tb_customer';
    const  TB_INSURANCE = 'tb_insurance';

    //項目の一覧
    const  TGC_INSPECTION_YM = 'tgc_inspection_ym';
    const  SYAKEN_NEXT_DATE = 'syaken_next_date';
    const  INSU_INSPECTION_TARGET_YM = 'insu_inspection_target_ym';
    const  INSU_INSPECTION_YM = 'insu_inspection_ym';
    const  TGC_CUSTOMER_INSURANCE_END_DATE = 'tgc_customer_insurance_end_date';

    // Batch type
    const LONG_BATCH = 1;
    const SHORT_BATCH = 2;
}
