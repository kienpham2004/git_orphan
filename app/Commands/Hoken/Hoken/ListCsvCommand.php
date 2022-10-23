<?php

namespace App\Commands\Hoken\Hoken;

use App\Models\Insurance;
use App\Commands\Command;
use App\Original\Util\CodeUtil;
// 独自
use OhInspection;

/**
 * 取り込みデータの実績CSVダウンロード
 *
 * @author yhatsutori
 */
class ListCsvCommand extends Command{

    /**
     * コンストラクタ
     * @param array $sort 並び順
     * @param $requestObj 検索条件
     * @param [type] $filename 出力ファイル名
     */
    public function __construct( $sort, $requestObj, $insu_jisya_tasya, $filename="target.csv" ){
        $this->sort = $sort;
        $this->requestObj = $requestObj;
        $this->insu_jisya_tasya = $insu_jisya_tasya;
        $this->filename = $filename;

        // カラムとヘッダーの値を取得
        $csvParams = $this->getCsvParams();
        // カラムを取得
        $this->columns = array_keys( $csvParams );
        // ヘッダーを取得
        unset($csvParams['tb_user_account.deleted_at']); // 削除フラグ除く
        $this->headers = array_values( $csvParams );
    }

    /**
     * カラムとヘッダーの値を取得
     * @return array
     */
    private function getCsvParams(){
        return [
            //'tb_insurance.id' => 'id',
            'insu_inspection_target_ym' => '対象年月',
            'insu_inspection_ym' => '保険満期月',
            'base_code' => '拠点コード',
            'base_name' => '拠点名称',
            'user_code' => '担当者コード',
            'user_name' => '担当者名',
            'tb_user_account.deleted_at' => '削除',

            'insu_customer_name' => '契約者名',
            //'insu_tel_no' => 'TEL',
            'insu_insurance_start_date' => '保険始期日',
            'insu_insurance_end_date' => '満期日',
            'insu_kikan' => '保険期間',
            'insu_jisya_tasya' => '自社・他社',
            'insu_syumoku_code' => '保険種目コード',
            'insu_syumoku' => '保険種目',
            'insu_company_code' => '保険会社コード',
            'insu_company_name' => '保険会社名',
            'insu_uketuke_kbn_code' => '受付区分コード',
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

            //202001test用
//            'insu_syoken_extract_number' => '抽出番号',
//            'insu_syoken_suishin_1' => '特別推進情報1',
//            'insu_syoken_suishin_2' => '特別推進情報2',
//            'insu_syoken_source' => '情報元',

            'insu_status_gensen' => '獲得状況',
            'insu_status_gensen_detail' => '獲得状況詳細',

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

            'insu_staff_info_toss' => 'スタッフからの情報トス',
            'insu_toss_staff_name' => '情報トススタッフ名',
            'insu_pair_fleet' => 'ペアフリート',
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
        
        // 検索条件を指定
        $builderObj = $builderObj->whereRequest( $this->requestObj, $this->insu_jisya_tasya )
                                 //->where('insu_inspection_target_ym','>', $targetdate) 不要な条件削除 20210120
                                 ->whereRaw(' coalesce( insu_status, 0 ) <> 100 ');     // 対象外ステータスを削除
        
        // 並び替えの処理
        $builderObj = $builderObj
            ->orderBys( $this->sort['sort'] );

        // 配列で値を取得
        $data = $builderObj
            ->get( $this->columns )
            ->toArray();
        
        if ( empty( $data ) ) {
            throw new \Exception('データが見つかりません');
        }

        // 検索結果をCSV出力ように変換
        $export = $this->convert( $data );
        
        
        return OhInspection::download( $export, $this->headers, $this->filename );
    }

    /**
     * 出力形式に変換
     * @param $data
     * @return
     */
    private function convert( $data ){
        $export = null;

        foreach( $data as $key => $value ){
        	//$export[$key]['id'] = $value['id'];
            $export[$key]['insu_inspection_target_ym'] = $value['insu_inspection_target_ym'];
            $export[$key]['insu_inspection_ym'] = $value['insu_inspection_ym'];
            $export[$key]['base_code'] = ( isset($value['base_code'])) ? '\''.sprintf('%02s', $value['base_code']) : "";
            $export[$key]['base_name'] = $value['base_name'];
            $export[$key]['user_code'] = (isset($value['user_code'] )) ?  '\''.sprintf('%03s', $value['user_code'] ) : "";

            $export[$key]['user_name'] = $value['user_name'];
            if ($value['user_name'] != "" && $value['deleted_at'] != ""){
                $export[$key]['user_name'] = $value['user_name']."(退職者)";
            }

            $export[$key]['insu_customer_name'] = $value['insu_customer_name'];
            //$export[$key]['insu_tel_no'] = $value['insu_tel_no'];
            $export[$key]['insu_insurance_start_date'] = $value['insu_insurance_start_date'];
            $export[$key]['insu_insurance_end_date'] = $value['insu_insurance_end_date'];
            $export[$key]['insu_kikan'] = $value['insu_kikan'];
            
            if( $value['insu_jisya_tasya'] == "純新規" ){
                $export[$key]['insu_jisya_tasya'] = "新規";
            }else{
                $export[$key]['insu_jisya_tasya'] = $value['insu_jisya_tasya'];
            }

            $export[$key]['insu_syumoku_code'] = $value['insu_syumoku_code'];
            $export[$key]['insu_syumoku'] = $value['insu_syumoku'];
            $export[$key]['insu_company_code'] = $value['insu_company_code'];
            $export[$key]['insu_company_name'] = $value['insu_company_name'];
            $export[$key]['insu_uketuke_kbn_code'] = $value['insu_uketuke_kbn_code'];
            $export[$key]['insu_uketuke_kbn'] = $value['insu_uketuke_kbn'];
            $export[$key]['insu_syoken_number'] = $value['insu_syoken_number'];
            $export[$key]['insu_car_base_number'] = $value['insu_car_base_number'];
            $export[$key]['insu_syadai_number'] = $value['insu_syadai_number'];
            $export[$key]['insu_syaryo_type'] = CodeUtil::getInsuSyaryoType( $value['insu_syaryo_type'] );
            $export[$key]['insu_jisseki_hokenryo'] = $value['insu_jisseki_hokenryo'];
            $export[$key]['insu_daisya'] = CodeUtil::getCheckType( $value['insu_daisya'] );
            $export[$key]['insu_tokyu'] = $value['insu_tokyu'];
            $export[$key]['insu_jinsin_syogai'] = $value['insu_jinsin_syogai'];
            $export[$key]['insu_cashless_flg'] = $value['insu_cashless_flg'];
            $export[$key]['insu_kanyu_dairiten'] = $value['insu_kanyu_dairiten'];
            $export[$key]['insu_syoken_sindan_date'] = $value['insu_syoken_sindan_date'];
            $export[$key]['insu_keiyaku_kekka'] = $value['insu_keiyaku_kekka'];

            //202001test用
//            $export[$key]['insu_syoken_extract_number'] = $value['insu_syoken_extract_number'];
//            $export[$key]['insu_syoken_suishin_1'] = $value['insu_syoken_suishin_1'];
//            $export[$key]['insu_syoken_suishin_2'] = $value['insu_syoken_suishin_2'];
//            $export[$key]['insu_syoken_source'] = $value['insu_syoken_source'];

            if( $value['insu_jisya_tasya'] != "自社分" ){
                $export[$key]['insu_status_gensen'] = CodeUtil::getInsuStatusGensen( $value['insu_status_gensen'] );
                $export[$key]['insu_status_gensen_detail'] = $value['insu_status_gensen_detail'];
            }else{
                $export[$key]['insu_status_gensen'] = "";
                $export[$key]['insu_status_gensen_detail'] = "";
            }

            $export[$key]['insu_status'] = CodeUtil::getInsuStatusName( $value['insu_status'] );
            $export[$key]['insu_contact_plan_date'] = $value['insu_contact_plan_date'];
            $export[$key]['insu_contact_date'] = $value['insu_contact_date'];
            $export[$key]['insu_action'] = CodeUtil::getInsuActionType( $value['insu_action'] );
            $export[$key]['insu_contact_jigu'] = CodeUtil::getInsuJiguType( $value['insu_contact_jigu'] );
            
            $export[$key]['insu_contact_taisyo'] = CodeUtil::getInsuContactTaisyoType( $value['insu_contact_taisyo']);
            $export[$key]['insu_contact_taisyo_name'] = $value['insu_contact_taisyo_name'];
            $export[$key]['insu_contact_syaryo_type'] = CodeUtil::getInsuSyaryoType( $value['insu_contact_syaryo_type'] );
            $export[$key]['insu_contact_daisya'] = CodeUtil::getCheckType( $value['insu_contact_daisya'] );
            $export[$key]['insu_contact_period'] = $value['insu_contact_period'];
            $export[$key]['insu_kakutoku_company_name'] = $value['insu_kakutoku_company_name'];
            $export[$key]['insu_contact_keijyo_ym'] = $value['insu_contact_keijyo_ym'];
            $export[$key]['insu_memo'] = $value['insu_memo'];
            $export[$key]['insu_contact_daigae_car_name'] = $value['insu_contact_daigae_car_name'];
            $export[$key]['insu_updated_history'] = $value['insu_updated_history'];

            $export[$key]['insu_add_tetsuduki_date'] = $value['insu_add_tetsuduki_date'];
            $export[$key]['insu_add_tetsuduki_detail'] = $value['insu_add_tetsuduki_detail'];
            $export[$key]['insu_add_keijyo_date'] = $value['insu_add_keijyo_date'];
            $export[$key]['insu_add_keijyo_ym'] = $value['insu_add_keijyo_ym'];

            if( $value['insu_jisya_tasya'] != "自社分" ){
                $export[$key]['insu_staff_info_toss'] = CodeUtil::getCheckType( $value['insu_staff_info_toss'] );
                $export[$key]['insu_toss_staff_name'] = $value['insu_toss_staff_name'];
                $export[$key]['insu_pair_fleet'] = CodeUtil::getCheckType( $value['insu_pair_fleet'] );
            }else{
                $export[$key]['insu_staff_info_toss'] = "";
                $export[$key]['insu_toss_staff_name'] = "";
                $export[$key]['insu_pair_fleet'] = "";
            }

        }
        
        return $export;
    }

}
