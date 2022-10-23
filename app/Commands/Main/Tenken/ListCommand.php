<?php

namespace App\Commands\Main\Tenken;

use App\Models\TargetCars;
use App\Commands\Command;

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
            'tgc_inspection_ym' => '点検月',
            'tgc_inspection_id' => '点検区分',
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
            'tgc_status_update' => '意向結果更新日時',
            
            // アラート
            'tgc_alert_memo' => 'アラートメモ',

            // 保険
            'tgc_customer_insurance_type' => '保険区分',
            'tgc_customer_insurance_company' => '保険会社名称',
            'tgc_customer_insurance_end_date' => '保険終期',
            
            // クレジット
            'tgc_credit_manryo_date_ym' => '契約満了月',
            'tgc_credit_hensaihouhou_name' => 'クレジット返済方法名称',
            'tgc_shiharai_count' => '支払回数',
            'tgc_first_shiharai_date_ym' => '初回支払年月',
            'tgc_keisan_housiki_kbn' => '計算方式区分',
            'tgc_credit_card_select_kbn' => 'クレジット・カード選択区分',
            'tgc_memo_syubetsu' => 'メモ種別',
            'tgc_sueoki_zankaritsu' => '据置・残価率',
            'tgc_last_shiharaikin' => '最終回支払金',
            
            //'v_ciao.ciao_course' => 'チャオ',
            'tb_target_cars.tgc_ciao_course' => 'チャオ',

            //'tb_manage_info.mi_abc_abc' => 'ABC',
            "tb_target_cars.tgc_abc_zone" => 'ABC',
            'tb_manage_info.mi_htc_login_flg' => 'HTC',

            'tb_manage_info.mi_rstc_reserve_commit_date' => '予約承認日時',
            'tb_manage_info.mi_rstc_get_out_date' => '出庫日時',
            
            'tb_manage_info.mi_ctcsa_contact_date' => '活動実績日(査定)',
            'tb_manage_info.mi_ctcsho_contact_date' => '活動実績日(商談)',
            'tb_manage_info.mi_ctcshi_contact_date' => '活動実績日(試乗)',

            // 20180821 追加
            'tb_manage_info.mi_tmr_in_sub_intention' => '入庫/代替意向',
            'tb_manage_info.mi_rstc_start_date' => '作業開始日時',
            
            // 20180903 追加
            'tb_manage_info.mi_ik_final_achievement' => '最終実績',
            
            'tb_manage_info.mi_rstc_reserve_status' => '状況',
            'tb_manage_info.mi_rcl_recall_flg' => 'リコール有無フラグ',
            
            'tb_customer_umu.umu_csv_flg' => 'csv有無',
            // 2022/03/16 add field
            'tb_manage_info.mi_smart_day' => '査定',

            /*
            'tb_manage_info.mi_ctcsa_result_code' => '',
            'tb_manage_info.mi_ctcsa_result_name' => '',
            'tb_manage_info.mi_ctcmi_result_code' => '',
            'tb_manage_info.mi_ctcmi_result_name' => '',
            'tb_manage_info.mi_ctcshi_result_code' => '',
            'tb_manage_info.mi_ctcshi_result_name' => ''
            */
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
        $builderObj = $builderObj->whereRequest( $this->requestObj, "tenken" );
        
        // 並び替えの処理
        $builderObj = $builderObj->orderBys( $this->sort['sort'] );

        // ペジネートの処理
        $data = $builderObj
            ->paginate( $this->requestObj->row_num, $this->columns )
            // 表示URLをpagerに指定
            ->setPath('pager');
        
        // 検索用（TMR意向）
        $list_tmr_name = 
                [
                    '入庫意向有' => '入庫意向有',
                    '自社予約済' => '自社予約済',
                    '代替意向有' => '代替意向有',
                    '他社予約済' => '他社予約済',
                    '代替意向無' => '代替意向無',
                    '入庫意向無' => '入庫意向無'
                ];
        // 検索用（クレ区分）
        $list_cre_type = 
                [
                    'A' => '据置クレ',
                    'B_D_E_1_2_7_8' => '通クレ',
                    'C_9' => '残クレ'
                ];
//        $list_ciao_type = 
//                [
//                    'タモツＡＳ_チャオ１Ｓ_チャオ２Ｓ_チャオ３Ｓ_チャオ４Ｓ_チャオ５Ｓ_チャオＬＳ_チャオＳＳ' => 'Sｺｰｽ',
//                    'タモツＡＴ_チャオ２Ｔ_チャオ３Ｔ_チャオ４Ｔ_チャオ５Ｔ_チャオＬＴ_リース６Ｔ' => 'Tｺｰｽ'
//                ];

        return array( $data, $list_tmr_name, $list_cre_type );
    }

}
