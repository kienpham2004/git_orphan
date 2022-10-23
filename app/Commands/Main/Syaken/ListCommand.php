<?php

namespace App\Commands\Main\Syaken;

use App\Models\TargetCars;
use App\Commands\Command;
use App\Models\ManageInfo;

/**
 * 取り込みデータの実績一覧を取得するコマンド
 *
 * @author yhatsutori
 */
class ListCommand extends Command{

    /**
     * コンストラクタ
     * @param array $sort 並び順
     * @param $requestObj 検索条件
     */
    public function __construct( $sort, $requestObj ){
        $this->sort = $sort;
        $this->requestObj = $requestObj;

        // カラムとヘッダーの値を取得
        $csvParams = $this->getCsvParams();
        // カラムを取得
        $this->columns = array_keys( $csvParams );
        // ヘッダーを取得
        $this->headers = array_values( $csvParams );
    }

    /**
     * カラムとヘッダーの値を取得
     * @return array
     */
    private function getCsvParams(){
        return [
            'tb_target_cars.id' => 'id',
            'tgc_user_id' => '担当者Id',
            'tgc_inspection_ym' => '車検月',
            'tgc_car_manage_number' => '統合車両管理ＮＯ',
            'base_id' => '拠点Id',
            'base_code' => '拠点コード',
            'base_short_name' => '拠点',
            'user_code' => '担当者コード',
            'user_name' => '担当者',
            'tb_user_account.deleted_at' => '削除',
            'tgc_customer_code' => '顧客コード',
            'tgc_customer_name_kanji' => '顧客名',
            'tgc_customer_name_kata' => '顧客名(カナ)', // 一覧画面だけ
            'tgc_car_base_number' => '登録車両ＮＯ',
            'tgc_car_model' => '型式',
            'tgc_car_name' => '車種名',
            'tgc_syaken_times' => '車検回数',
            'tgc_syaken_next_date' => '次回車検日',
            'tgc_customer_kouryaku_flg' => '攻略対象車',
            'tgc_status' => '意向結果',
            'tgc_status_update' => '意向結果日',

            // アラート
            'tgc_alert_memo' => 'アラートメモ',

            // 保険
            'tgc_customer_insurance_type' => '保険区分',
            'tgc_customer_insurance_company' => '保険会社名称',
            'tgc_customer_insurance_end_date' => '保険終期',
            
            // クレジット
            'tgc_credit_manryo_date_ym' => '契約満了月',
            'tgc_credit_hensaihouhou' => '選択プラン_クレジット返済方法(テキスト)',
            'tgc_credit_hensaihouhou_name' => 'クレジット返済方法名称',
            'tgc_shiharai_count' => '支払回数',
            'tgc_first_shiharai_date_ym' => '初回支払年月',
            'tgc_keisan_housiki_kbn' => '計算方式区分',
            'tgc_credit_card_select_kbn' => 'クレジット・カード選択区分',
            'tgc_memo_syubetsu' => 'メモ種別',
            'tgc_sueoki_zankaritsu' => '据置・残価率',
            'tgc_last_shiharaikin' => '最終回支払金',
            
            'tb_target_cars.tgc_ciao_course' => 'チャオコース',
            'tb_target_cars.tgc_abc_zone' => 'ABC',
            'tb_manage_info.mi_htc_login_flg' => 'HTC',

            'tb_manage_info.mi_rstc_reserve_commit_date' => '予約承認日時',
            'tb_manage_info.mi_rstc_get_out_date' => '出庫日時',
            'tb_manage_info.mi_rstc_put_in_date' => '入庫日時',

            'tb_manage_info.mi_satei_max' => '活動実績日(査定)',
            'tb_manage_info.mi_syodan_max' => '活動実績日(見積)',
            'tb_manage_info.mi_shijo_max' => '活動実績日(試乗)',

            'tb_manage_info.mi_ctcsa_contact_date' => '活動実績日(査定)',
            'tb_manage_info.mi_ctcmi_contact_date' => '活動実績日(見積)',
            'tb_manage_info.mi_ctcshi_contact_date' => '活動実績日(試乗)',
            
            'tb_manage_info.mi_tmr_in_sub_intention' => '入庫/代替意向',
            'tb_manage_info.mi_ik_final_achievement' => '最終実績',
            'tb_manage_info.mi_dsya_syaken_reserve_date' => '車検予約日',
            'tb_manage_info.mi_dsya_syaken_jisshi_date' => '車検実施日',

            'tb_customer_umu.umu_csv_flg' => 'csv有無',
            'tb_manage_info.mi_rcl_recall_flg' => 'リコール有無フラグ',
            // 2022/03/16 add field
            'tb_manage_info.mi_smart_day' => '査定',
            'tb_manage_info.mi_dsya_keiyaku_car' => '契約確定車名'
        ];
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 表示も問題で一度変数に格納
        $requestObj = $this->requestObj;
        
        
        // 他のテーブルとJOIN
        $builderObj = TargetCars::joinBase()
                                //->joinSales()
                                //->joinSalesAll()
                                //->joinCiao()
                                ->joinInfo()
                                ->joinUmu();        
        
        // 検索条件を指定
        $builderObj = $builderObj->whereRequest( $this->requestObj, "syaken" );
        
        // 並び替えの処理
        // 2022/03/24 update order
        if (isset($this->sort['sort']['mi_dsya_syaken_reserve_date'])) {
            $builderObj = $builderObj->orderByRaw("case 
                    when to_char(tgc_syaken_next_date + '-12 month'::interval, 'YYYYMM'::text) < to_char(mi_dsya_syaken_reserve_date, 'YYYYMM')
                        then mi_dsya_syaken_reserve_date
                    else null end {$this->sort['sort']['mi_dsya_syaken_reserve_date']}");
        }

        else {
            $builderObj = $builderObj->orderBys($this->sort['sort']);
        }

        // ペジネートの処理
        $data = $builderObj
            ->paginate( $this->requestObj->row_num, $this->columns )
            // 表示URLをpagerに指定
            ->setPath('pager');
        
        // TMR意向検索用
        //$list_tmr_name = ManageInfo::getTmrName();    全取得する場合
        $list_tmr_name = 
                [
                    '入庫意向有' => '入庫意向有',
                    '自社予約済' => '自社予約済',
                    '代替意向有' => '代替意向有',
                    '他社予約済' => '他社予約済',
                    '代替意向無' => '代替意向無',
                    '入庫意向無' => '入庫意向無'
                ];
        
        // 活動意向検索用
        $list_tgc_action_code =
                [
                    'n'     => '未確定',
                    '11'    => '自社車検',
                    '12'    => '他社車検',
                    '13'    => '自社代替',
                    '14'    => '他社代替',
                    '15'    => '廃車・転売',
                    '16'    => '転居予定',
                    '17'    => '拠点移管'
                ];
//        $list_tgc_action_code =
//                [
//                    'n'     => '未確認',
//                    '1_2_11'=> '自社車検',
//                    '5_6_12'=> '他社車検',
//                    '3_4_13'=> '自社代替',
//                    '7_14'  => '他社代替',
//                    '15'    => '廃車・転売',
//                    '8_16'  => '転居予定',
//                    '17'    => '拠点移管'
//                ];
        
        $list_cre_type = 
                [
                    'A' => '据置クレ',
                    'B_D_E_1_2_7_8' => '通クレ',
                    'C_9' => '残クレ'
                ];

        // クレジット検索用
//        $list_ciao_type = 
//                [
//                    'タモツＡＳ_チャオ１Ｓ_チャオ２Ｓ_チャオ３Ｓ_チャオ４Ｓ_チャオ５Ｓ_チャオＬＳ_チャオＳＳ' => 'Sｺｰｽ',
//                    'タモツＡＴ_チャオ２Ｔ_チャオ３Ｔ_チャオ４Ｔ_チャオ５Ｔ_チャオＬＴ_リース６Ｔ' => 'Tｺｰｽ'
//                ];
        
        return array($data, $list_tmr_name, $list_tgc_action_code, $list_cre_type );
    }

}
