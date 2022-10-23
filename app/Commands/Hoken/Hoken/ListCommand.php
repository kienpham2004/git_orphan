<?php

namespace App\Commands\Hoken\Hoken;

use App\Models\Insurance;
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
    public function __construct( $sort, $requestObj, $insu_jisya_tasya ){
        $this->sort = $sort;
        $this->requestObj = $requestObj;
        $this->insu_jisya_tasya = $insu_jisya_tasya;

        // カラムとヘッダーの値を取得
        $csvParams = $this->getParams();
        // カラムを取得
        $this->columns = array_keys( $csvParams );
        // ヘッダーを取得
        $this->headers = array_values( $csvParams );
    }

    /**
     * カラムとヘッダーの値を取得
     * @return array
     */
    private function getParams(){
        return [
            'tb_insurance.id' => 'id',
            //'tb_insurance.id' => 'id',
            'insu_inspection_target_ym' => '対象年月',
            'insu_inspection_ym' => '保険満期月',
            //'insu_base_code' => '拠点コード',
            'insu_base_name_csv' => '拠点名称',
            'insu_user_id' => '担当者Id',
            'insu_user_name_csv' => '担当者名',
            'base_id' => '拠点Id',
            'base_code' => '拠点コード(マスタ)',
            'base_short_name' => '拠点略称(マスタ)',
            'user_code' => '担当者コード(マスタ)',
            'user_name' => '担当者名(マスタ)',
            'tb_user_account.deleted_at' => '削除(マスタ)',
            
            'insu_customer_name' => '契約者名',
            //'insu_tel_no' => 'TEL',
            'insu_insurance_start_date' => '保険始期日',
            'insu_insurance_end_date' => '満期日',
            'insu_kikan' => '保険期間',
            'insu_jisya_tasya' => '自社・他社',
            //'insu_syumoku_code' => '保険種目コード',
            'insu_syumoku' => '保険種目',
            //'insu_company_code' => '保険会社コード',
            'insu_company_name' => '保険会社名',
            //'insu_uketuke_kbn_code' => '受付区分コード',
            'insu_uketuke_kbn' => '受付区分',
            'insu_syoken_number' => '証券番号',
            'insu_car_base_number' => '登録番号',
            'insu_syadai_number' => '車台番号',
            'insu_syaryo_type' => '車両',
            'insu_jisseki_hokenryo' => '実績保険料',
            'insu_daisya' => '代車',
            'insu_tokyu' => '等級',
            'insu_jinsin_syogai' => '人身傷害',
            'insu_cashless_flg' => 'キャッシュレス',
            'insu_kanyu_dairiten' => '加入代理店',
            'insu_syoken_sindan_date' => '証券診断実施日',
            'insu_keiyaku_kekka' => '契約結果（継続/成約日）',
            
            'insu_syoken_extract_number' => '抽出番号',
            'insu_syoken_suishin_1' => '特別推進情報1',
            'insu_syoken_suishin_2' => '特別推進情報2',
            'insu_syoken_source' => '情報元',

            'insu_status' => '更新状況/獲得状況',
            'insu_contact_plan_date' => '接触予定日(次回)',
            'insu_contact_date' => '接触日',
            'insu_action' => '活動内容',
            'insu_contact_jigu' => '治具',
            'insu_contact_taisyo' => '接触対象',
            'insu_contact_taisyo_name' => '接触対象者名',
            'insu_contact_syaryo_type' => '車両保険付帯',
            'insu_contact_daisya' => '代車特約付帯',
            'insu_contact_period' => '保険期間（長期の推進）',
            'insu_kakutoku_company_name' => '獲得保険会社名',
            'insu_contact_keijyo_ym' => '計上予定月',
            'insu_memo' => 'メモ',
            'insu_contact_daigae_car_name' => '車両の代替提案',
            'insu_updated_history' => '更新履歴',

            'insu_add_tetsuduki_date' => '手続き完了日',
            'insu_add_tetsuduki_detail' => '手続き内容',
            'insu_add_keijyo_date' => '本社集中処理担当日',
            'insu_add_keijyo_ym' => '計上反映月',
            'insu_alert_memo' => '伝達事項'
        ];
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // お客様から希望で、2018年6月までのデータは非表示
//        $targetdate = date("Ym",strtotime("2018-06-01"));
//         $targetdate = date("Ym",strtotime("-6 month"));

        // 表示も問題で一度変数に格納
        $requestObj = $this->requestObj;
                
        // 他のテーブルとJOIN
        $builderObj = Insurance::joinBase();
                                //->JoinSalesAll();
        
        // 検索条件を指定
        $builderObj = $builderObj->whereRequest( $this->requestObj, $this->insu_jisya_tasya )
                                 //->where('insu_inspection_target_ym','>', $targetdate) 不要な条件削除 20210120
                                 ->whereRaw(' coalesce( insu_status, 0 ) <> 100 ');     // 対象外ステータスを削除

        // 並び替えの処理
        $builderObj = $builderObj->orderBys( $this->sort['sort'] );

        // ペジネートの処理
        $data = $builderObj
            ->paginate( $this->requestObj->row_num, $this->columns )
            // 表示URLをpagerに指定
            ->setPath('pager');

        return $data;
    }

}

?>