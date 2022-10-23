<?php

namespace App\Commands\Main\Tenken;

use App\Original\Util\CodeUtil;
use App\Models\TargetCars;
use App\Commands\Command;
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
    public function __construct( $sort, $requestObj, $filename="target.csv" ){
        $this->sort = $sort;
        $this->requestObj = $requestObj;
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
            //'tb_target_cars.id' => 'id',
            'tgc_inspection_ym' => '点検月',
            'tgc_inspection_id' => '点検区分',
            'tgc_car_manage_number' => '統合車両管理ＮＯ',
            'base_code' => '拠点コード',
            'base_short_name' => '拠点',
            'user_code' => '担当者コード',
            'user_name' => '担当者',
            "tb_user_account.deleted_at" => "削除",
            'tgc_customer_code' => '顧客コード',
            'tgc_customer_name_kanji' => '顧客名',
            // 2022/013/25 update field CSV
            'tgc_customer_postal_code' => '〒',
            'tgc_customer_address' =>'住所',
            'tgc_car_base_number' => '登録車両ＮＯ',
            'tgc_car_model' => '型式',
            'tgc_car_name' => '車種名',
            'tgc_syaken_times' => '車検回数',
            'tgc_syaken_next_date' => '次回車検日',
            'tgc_customer_kouryaku_flg' => '攻略対象車',
            'tgc_status' => '意向結果',

//          2022/03/25 update field
            'tgc_dm_flg' => 'ＤＭ不要区分',
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
            'tb_target_cars.tgc_abc_zone' => 'ABC',
            'tb_manage_info.mi_htc_login_flg' => 'HTC',

            'mi_rstc_reserve_status' => '状況',
            'tb_manage_info.mi_rstc_reserve_commit_date' => '予約承認日時',
            'tb_manage_info.mi_rstc_get_out_date' => '出庫日時',
            'tb_manage_info.mi_rstc_delivered_date' => '納車済日時',

//            2022/03/25 update field CSV
//            'tb_manage_info.mi_ctcsa_contact_date' => '活動実績日(査定)',
            'tb_manage_info.mi_smart_day' => '活動実績日(査定)',
            'tb_manage_info.mi_ctcmi_contact_date' => '活動実績日(商談)',
            'tb_manage_info.mi_ctcshi_contact_date' => '活動実績日(試乗)',
            
            'tb_customer_umu.umu_csv_flg' => 'csv有無'
            // 2022/03/25 delete field
//            'tb_manage_info.mi_smart_day' => '活動実績日(査定)',

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
                                //->joinCiao()
                                ->joinInfo()
                                ->joinUmu();
        
        // 検索条件を指定
        $builderObj = $builderObj->whereRequest( $this->requestObj, "tenken" );

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
            $export[$key]['tgc_inspection_ym'] = $value['tgc_inspection_ym'];
            $export[$key]['tgc_inspection_id'] = CodeUtil::getInspectDmTypeName($value['tgc_inspection_id']);
            $export[$key]['tgc_car_manage_number'] = $value['tgc_car_manage_number'];
            $export[$key]['base_code'] = '\''.sprintf('%02s', $value['base_code']);
            $export[$key]['base_short_name'] = $value['base_short_name'];
            $export[$key]['user_code'] = '\''.sprintf('%03s', $value['user_code']);
            $export[$key]['user_name'] = $value['user_name'];
            if ($value['user_name'] != "" && $value['deleted_at'] != ""){
                $export[$key]['user_name'] = $value['user_name']."(退職者)";
            }

            $export[$key]['tgc_customer_code'] = '\''.sprintf('%08d', $value['tgc_customer_code']);
            $export[$key]['tgc_customer_name_kanji'] = $value['tgc_customer_name_kanji'];

//          2022/03/25 update field
            $export[$key]['tgc_customer_postal_code'] = $value['tgc_customer_postal_code'];
            $export[$key]['tgc_customer_address'] = $value['tgc_customer_address'];

            $export[$key]['tgc_car_base_number'] = $value['tgc_car_base_number'];
            $export[$key]['tgc_car_model'] = $value['tgc_car_model'];
            $export[$key]['tgc_car_name'] = $value['tgc_car_name'];
            $export[$key]['tgc_syaken_times'] = $value['tgc_syaken_times'] . "回";
            $export[$key]['tgc_syaken_next_date'] = $value['tgc_syaken_next_date'];
            $export[$key]['tgc_customer_kouryaku_flg'] = CodeUtil::getMaruBatsuType( $value['tgc_customer_kouryaku_flg'] );
            $export[$key]['tgc_status'] = CodeUtil::getIntentSyatenkenName( $value['tgc_status'] );
            //2020/03/25 update field
            if( !empty( $value['tgc_dm_flg'] ) == True && $value['tgc_dm_flg'] == "1" ){
                $export[$key]['tgc_dm_flg'] =  "不要";
            }else{
                $export[$key]['tgc_dm_flg'] =  "";
            }

            // 保険
            $export[$key]['tgc_customer_insurance_type'] =  $value['tgc_customer_insurance_type'];
            $export[$key]['tgc_customer_insurance_company'] = $value['tgc_customer_insurance_company'];
            $export[$key]['tgc_customer_insurance_end_date'] = $value['tgc_customer_insurance_end_date'];

            // クレジット
            $export[$key]['tgc_credit_manryo_date_ym'] = $value['tgc_credit_manryo_date_ym'];
            $export[$key]['tgc_credit_hensaihouhou_name'] = CodeUtil::getCreditType( $value['tgc_credit_hensaihouhou_name'] );
            if( !empty( $value['tgc_shiharai_count'] ) == True ){
                $export[$key]['tgc_shiharai_count'] =  $value['tgc_shiharai_count'];
            }else{
                $export[$key]['tgc_shiharai_count'] =  "";
            }
            $export[$key]['tgc_first_shiharai_date_ym'] = $value['tgc_first_shiharai_date_ym'];
            $export[$key]['tgc_keisan_housiki_kbn'] = $value['tgc_keisan_housiki_kbn'];
            $export[$key]['tgc_credit_card_select_kbn'] = $value['tgc_credit_card_select_kbn'];
            $export[$key]['tgc_memo_syubetsu'] = $value['tgc_memo_syubetsu'];
            $export[$key]['tgc_sueoki_zankaritsu'] = $value['tgc_sueoki_zankaritsu'];
            $export[$key]['tgc_last_shiharaikin'] = $value['tgc_last_shiharaikin'];
            
            $export[$key]['tgc_ciao_course'] = str_replace( "チャオ", "",  $value['tgc_ciao_course'] );
            $export[$key]['tgc_abc_zone'] = $value['tgc_abc_zone'];

            // 2022/03/17 update  mi_htc_login_flg
            //$export[$key]['mi_htc_login_flg'] = $value['mi_htc_login_flg'] == '0' ? '未' : '';
            $export[$key]['mi_htc_login_flg'] = $value['mi_htc_login_flg'] == '0' ? '未' : ($value['mi_htc_login_flg'] == '1' ? '済' : '') ;

            $export[$key]['mi_rstc_reserve_status'] = $value['mi_rstc_reserve_status'];
            $export[$key]['mi_rstc_reserve_commit_date'] = $value['mi_rstc_reserve_commit_date'];
            $export[$key]['mi_rstc_get_out_date'] = $value['mi_rstc_get_out_date'];
            $export[$key]['mi_rstc_delivered_date'] = $value['mi_rstc_delivered_date'];

            // 2022/03/16 change filed
            $export[$key]['mi_smart_day'] = $value['mi_smart_day'];
            $export[$key]['mi_ctcmi_contact_date'] = $value['mi_ctcmi_contact_date'];
            $export[$key]['mi_ctcshi_contact_date'] = $value['mi_ctcshi_contact_date'];
            
            if( !empty( $value['umu_csv_flg'] ) == True && $value['umu_csv_flg'] == "1" ){
                $export[$key]['umu_csv_flg'] =  "";
            }else{
                $export[$key]['umu_csv_flg'] =  "無";
            }
        }
        
        return $export;
    }
    
}
