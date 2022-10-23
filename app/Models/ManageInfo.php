<?php

namespace App\Models;

use App\Original\Util\CodeUtil;

class ManageInfo extends AbstractModel {
    // テーブル名
    protected $table = 'tb_manage_info';

    // 変更可能なカラム
    protected $fillable = [
        'mi_inspection_id',             // 車点検区分
        'mi_inspection_ym',             // 対象年月
        'mi_car_manage_number',         // 統合車両管理ＮＯ
        'mi_customer_code',             // 顧客コード
        'mi_base_code_init',            // 初期拠点コード
        'mi_user_id',                    // 担当者Id

        'mi_tmr_last_process_status',   // 最終処理状況
        'mi_tmr_last_call_result',      // 最終架電結果
        'mi_tmr_call_times',            // 架電回数
        'mi_tmr_last_call_date',        // 最終架電日
        'mi_tmr_to_base_comment',       // 拠点への申送事項
        'mi_tmr_in_sub_intention',      // 入庫/代替意向
        'mi_tmr_in_sub_detail',         // 入庫/代替詳細

        'mi_tmr_call_1_status',         // 1コール目処理状況
        'mi_tmr_call_1_result',         // 1コール目処理状況
        'mi_tmr_call_1_date',           // 1コール目架電日
        'mi_tmr_call_2_status',         // 2コール目処理状況
        'mi_tmr_call_2_result',         // 2コール目処理状況
        'mi_tmr_call_2_date',           // 2コール目架電日
        'mi_tmr_call_3_status',         // 3コール目処理状況
        'mi_tmr_call_3_result',         // 3コール目処理状況
        'mi_tmr_call_3_date',           // 3コール目架電日
        'mi_tmr_call_4_status',         // 4コール目処理状況
        'mi_tmr_call_4_result',         // 4コール目処理状況
        'mi_tmr_call_4_date',           // 4コール目架電日
        'mi_tmr_call_5_status',         // 5コール目処理状況
        'mi_tmr_call_5_result',         // 5コール目処理状況
        'mi_tmr_call_5_date',           // 5コール目架電日
        'mi_tmr_call_6_status',         // 6コール目処理状況
        'mi_tmr_call_6_result',         // 6コール目処理状況
        'mi_tmr_call_6_date',           // 6コール目架電日

        'mi_rstc_start_date',           // 作業開始日時
        'mi_rstc_end_date',             // 作業終了日時

        'mi_rstc_detail',               // 作業内容
        'mi_rstc_hosyo_kbn',            // 保証区分
        'mi_rstc_youmei',               // 用命事項

        'mi_rstc_put_in_date',          // 入庫日時
        'mi_rstc_get_out_date',         // 出庫日時
        'mi_rstc_reserve_commit_date',  // 予約承認日時
        'mi_rstc_delivered_date',       // 作業進捗：納車済日時
        'mi_rstc_reserve_status',       // 状況
		        
        'mi_ctcsa_contact_date',        // 活動実績日(査定)
        'mi_ctcsa_result_code',         // 接触成果コード(査定)
        'mi_ctcsa_result_name',         // 接触成果名称(査定)

        'mi_ctcmi_contact_date',        // 活動実績日(見積)
        'mi_ctcmi_result_code',         // 接触成果コード(見積)
        'mi_ctcmi_result_name',         // 接触成果名称(見積)

        'mi_ctcshi_contact_date',       // 活動実績日(試乗)
        'mi_ctcshi_result_code',        // 接触成果コード(試乗)
        'mi_ctcshi_result_name',        // 接触成果名称(試乗)
        
        'mi_ctcsho_contact_date',       // 活動実績日(商談)
        'mi_ctcsho_result_code',        // 接触成果コード(商談)
        'mi_ctcsho_result_name',        // 接触成果名称(商談)
        
        'mi_smart_day',                 // 査定日
        'mi_smart_nego_kbn',            // 商談区分
        'mi_ik_final_achievement',      // 最終実績
        'mi_rstc_web_reserv_flg',       // web予約
        
        'mi_ctc_seiyaku_flg',           // 成約フラグ(直近〇ヶ月)
        'mi_ctcview_seiyaku_flg',       // 成約フラグ
        'mi_date_max1',                 // 最新日時：電話(直近〇ヶ月)
        'mi_ctcview_date_tel',          // 最新日時：電話
        'mi_date_max2',                 // 最新日時：訪問(直近〇ヶ月)
        'mi_ctcview_date_home',         // 最新日時：訪問
        'mi_date_max3',                 // 最新日時：来店(直近〇ヶ月)
        'mi_ctcview_date_shop',         // 最新日時：来店
        
        'mi_rcl_recall_flg',            // リコール有無フラグ 

        // 2022/02/21
        'mi_dsya_keiyaku_car',    // 契約確定車名
        'mi_dsya_syaken_jisshi_date',    // 車検実施日
        'mi_dsya_syaken_reserve_date',   // 車検予約日
        'mi_dsya_status_katsudo_code',   // 意向区分コード（活動日報）
        'mi_dsya_status_katsudo_date',   // 意向確認日（活動日報）
        'mi_dsya_status_katsudo_csv',   // 意向区分（活動日報
        'mi_contact_6m',  // 6M接触日
        'mi_satei_6m',    // 6M査定
        'mi_shijo_6m',    // 6M試乗
        'mi_syodan_6m',   // 6M商談
        'mi_contact_5m',  // 5M接触日
        'mi_satei_5m',    // 5M査定
        'mi_shijo_5m',    // 5M試乗
        'mi_syodan_5m',   // 5M商談
        'mi_contact_4m',  // 4M接触日
        'mi_satei_4m',    // 4M査定
        'mi_shijo_4m',    // 4M試乗
        'mi_syodan_4m',   // 4M商談
        'mi_contact_3m',  // 3M接触日
        'mi_satei_3m',    // 3M査定
        'mi_shijo_3m',    // 3M試乗
        'mi_syodan_3m',   // 3M商談
        'mi_contact_2m',  // 2M接触日
        'mi_satei_2m',    // 2M査定
        'mi_shijo_2m',    // 2M試乗
        'mi_syodan_2m',   // 2M商談
        'mi_contact_1m',  // 1M接触日
        'mi_satei_1m',    // 1M査定
        'mi_shijo_1m',    // 1M試乗
        'mi_syodan_1m',   // 1M商談
        'mi_contact_0m',   // 車検当月接触日
        'mi_dsya_first_regist_date_ym',   // 初度登録
        'mi_dsya_car_name',   // 車名
        'mi_satei_max',    // 最新査定
        'mi_shijo_max',    // 最新試乗
        'mi_syodan_max',   // 最新商談
    ];
    
    ###########################
    ## 抽出の処理
    ###########################
    
    /**
     * データの登録と更新
     * @param  [type] $values [description]
     * @return [type]         [description]
     */
    public static function merge( $values ) {
        
        // 値が空でない時だけ更新（tb_contactデータ保持を基本的に〇ヶ月とした際、空白の上書きを防止する為）
        if( !empty( $values["mi_date_max1"] ) == True ){
            $values["mi_ctcview_date_tel"]  = $values["mi_date_max1"];
        }
        if( !empty( $values["mi_date_max2"] ) == True ){
            $values["mi_ctcview_date_home"] = $values["mi_date_max2"];
        }
        if( !empty( $values["mi_date_max3"] ) == True ){
            $values["mi_ctcview_date_shop"] = $values["mi_date_max3"];
        }
        if( !empty( $values["mi_ctc_seiyaku_flg"] ) == True ){
            $values["mi_ctcview_seiyaku_flg"] = $values["mi_ctc_seiyaku_flg"];
        }

        // 最新値設定
        $values["mi_shijo_max"] = CodeUtil::getMaxDate($values["mi_shijo_6m"], $values["mi_shijo_5m"], $values["mi_shijo_4m"], $values["mi_shijo_3m"], $values["mi_shijo_2m"], $values["mi_shijo_1m"] );
        $values["mi_satei_max"] = CodeUtil::getMaxDate($values["mi_satei_6m"], $values["mi_satei_5m"], $values["mi_satei_4m"], $values["mi_satei_3m"], $values["mi_satei_2m"], $values["mi_satei_1m"] );
        $values["mi_syodan_max"] = CodeUtil::getMaxDate($values["mi_syodan_6m"], $values["mi_syodan_5m"], $values["mi_syodan_4m"], $values["mi_syodan_3m"], $values["mi_syodan_2m"], $values["mi_syodan_1m"] );
        ManageInfo::updateOrCreate(
            [
                'mi_inspection_id' => $values['mi_inspection_id'],
                'mi_inspection_ym' => $values['mi_inspection_ym'],
                'mi_car_manage_number' => $values['mi_car_manage_number']
            ],
            $values
        );
    }
    
    /**
     * @return array    $list_tmr_name    TMR意向検索用 ※全取得する場合
     */
    public static function getTmrName() {
        $list_tmr_name = ManageInfo::groupBy('mi_tmr_in_sub_intention')
                ->lists('mi_tmr_in_sub_intention','mi_tmr_in_sub_intention')
                ->toArray();
        
        return $list_tmr_name;
        
    }
    
}
