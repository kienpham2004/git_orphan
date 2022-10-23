<?php

namespace App\Commands\Main\Customer;

use App\Original\Util\CodeUtil;
use App\Models\Customer;
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
    public function __construct( $sort, $requestObj, $filename="customer.csv" ){
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
            'tb_customer_umu.umu_csv_flg' => '車両無',
            "car_manage_number" => "統合車両管理No.",
            "syaken_next_date" => "次回車検日",
            "base_code" => "拠点コード",
            "base_name" => "拠点名",
            "user_code" => "担当者コード",
            "user_name" => "担当者名",
            "tb_user_account.deleted_at" => "削除",
            "customer_code" => "顧客コード",
            "customer_name_kanji" => "顧客名",
            "customer_name_kata" => "顧客カナ",
            "customer_postal_code" => "自宅郵便番号",
            "customer_address" => "自宅住所",
            "car_base_number" => "車両登録No",
            "car_model" => "型式",
            "car_service_code" => "車種名",
            "car_year_type" => "年式",
            "first_regist_date_ym" => "初度登録年月",
            "cust_reg_date" => "登録日",
            "customer_insurance_type" => "任意保険加入区分",
            "customer_insurance_company" => "任意保険会社",
            "customer_insurance_end_date" => "任意保険終期",
            "shiharai_count" => "支払回数",
            "credit_hensaihouhou" => "契約区分",
            "sueoki_zankaritsu" => "据置・残価率",
            "keisan_housiki_kbn" => "計算方式",
            "first_shiharai_date_ym" => "初回支払年月",
            "htc_car" => "契約満了月",
            "customer_kouryaku_flg" => "攻略対象車",
            "syaken_times" => "車検回数",
            "car_new_old_kbn_name" => "新中区分",
            'abc_zone' => 'ABC',
            'ciao_course' => 'チャオコース',
            'ciao_end_date' => 'チャオ終期',
            'mi_htc_login_flg' => 'HTC',

            "tb_customer.created_at" => "登録日",
            "tb_customer.updated_at" => "更新日",
        ];
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 表示も問題で一度変数に格納
        $requestObj = $this->requestObj;

        // データを取得
        $builderObj =  Customer::joinBase()
                //->joinCiao()
                //->joinAbc()
                ->joinUmu()
            // 2022/03/21 update join
//                ->joinHtc()
                ->JoinInfoSyaken()
                ->whereNull('tb_customer.deleted_at');
        
        // 検索条件を指定
        $builderObj = $builderObj->whereRequest( $this->requestObj );
        
        // 並び替えの処理
        $builderObj = $builderObj->orderBys( $this->sort['sort'] );

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
            $export[$key]['umu_csv_flg'] = $value['umu_csv_flg'];
            $export[$key]['car_manage_number'] = $value['car_manage_number'];
            $export[$key]['syaken_next_date'] = $value['syaken_next_date'];
            $export[$key]['base_code'] = '\''.sprintf('%02s', $value['base_code']);
            $export[$key]['base_name'] = $value['base_name'];
            $export[$key]['user_code'] = '\''.sprintf('%03s', $value['user_code']);
            $export[$key]['user_name'] = $value['user_name'];
            if ($value['user_name'] != "" && $value['deleted_at'] != ""){
                $export[$key]['user_name'] = $value['user_name']."(退職者)";
            }
            $export[$key]['customer_code'] = '\''.sprintf('%08d', $value['customer_code']);
            $export[$key]['customer_name_kanji'] = $value['customer_name_kanji'];
            $export[$key]['customer_name_kata'] = $value['customer_name_kata'];
            $export[$key]['customer_postal_code'] = $value['customer_postal_code'];
            $export[$key]['customer_address'] = $value['customer_address'];
            $export[$key]['car_base_number'] = $value['car_base_number'];
            $export[$key]['car_model'] = $value['car_model'];
            $export[$key]['car_service_code'] = $value['car_service_code'];
            $export[$key]['car_year_type'] = $value['car_year_type'];
            $export[$key]['first_regist_date_ym'] = $value['first_regist_date_ym'];
            $export[$key]['cust_reg_date'] = $value['cust_reg_date'];
            $export[$key]['customer_insurance_type'] = $value['customer_insurance_type'];
            $export[$key]['customer_insurance_company'] = $value['customer_insurance_company'];
            $export[$key]['customer_insurance_end_date'] = $value['customer_insurance_end_date'];
            $export[$key]['shiharai_count'] = $value['shiharai_count'];
            $export[$key]['credit_hensaihouhou'] = $value['credit_hensaihouhou'];
            $export[$key]['sueoki_zankaritsu'] = $value['sueoki_zankaritsu'];
            if( $value['keisan_housiki_kbn'] == 1 ){
                $export[$key]['keisan_housiki_kbn'] = "実質年率";
            }elseif( $value['keisan_housiki_kbn'] == 2 ){
                $export[$key]['keisan_housiki_kbn'] = "アドオン";
            }else{
                $export[$key]['keisan_housiki_kbn'] = "";
            }
            $export[$key]['first_shiharai_date_ym'] = $value['first_shiharai_date_ym'];
            if( !empty( $value["first_shiharai_date_ym"] ) ){
                $export[$key]['htc_car'] =
                date( "Y/m", strtotime( $value["first_shiharai_date_ym"] . "01 +" . intval( $value["shiharai_count"] - 1 ) . "month" ) );
            }else{
                $export[$key]['htc_car'] = "";
            }
            $export[$key]['customer_kouryaku_flg'] = CodeUtil::getMaruBatsuType( $value['customer_kouryaku_flg'] );
            $export[$key]['syaken_times'] = $value['syaken_times'];
            $export[$key]['car_new_old_kbn_name'] = $value['car_new_old_kbn_name'];
            $export[$key]['abc_zone'] = $value['abc_zone'];
            $export[$key]['ciao_course'] = $value['ciao_course'];
            $export[$key]['ciao_end_date'] = $value['ciao_end_date'];
            $export[$key]['mi_htc_login_flg'] = $value['mi_htc_login_flg'] == '0' ? '未' : ($value['mi_htc_login_flg'] == '1' ? '済' : '');
            $export[$key]['created_at'] = date( "Y/m/d", strtotime( $value['created_at'] ) );
            $export[$key]['updated_at'] = date( "Y/m/d", strtotime( $value['updated_at'] ) );
        }

        return $export;
    }

}
