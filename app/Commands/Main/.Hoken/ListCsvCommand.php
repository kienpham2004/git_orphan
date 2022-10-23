<?php
//
//namespace App\Commands\Main\Hoken;
//
//use App\Models\Insurance;
//use App\Commands\Command;
//use App\Original\Codes\Intent\IntentInsuranceCodes;
//// 独自
//use OhInspection;
//
///**
// * 取り込みデータの実績CSVダウンロード
// *
// * @author yhatsutori
// */
//class ListCsvCommand extends Command{
//
//    /**
//     * コンストラクタ
//     * @param array $sort 並び順
//     * @param $requestObj 検索条件
//     * @param [type] $filename 出力ファイル名
//     */
//    public function __construct( $sort, $requestObj, $filename="target.csv" ){
//        $this->sort = $sort;
//        $this->requestObj = $requestObj;
//        $this->filename = $filename;
//
//        // カラムとヘッダーの値を取得
//        $csvParams = $this->getCsvParams();
//        // カラムを取得
//        $this->columns = array_keys( $csvParams );
//        // ヘッダーを取得
//        $this->headers = array_values( $csvParams );
//    }
//
//    /**
//     * カラムとヘッダーの値を取得
//     * @return array
//     */
//    private function getCsvParams(){
//        return [
//            //'tb_insurance.id' => 'id',
//            'insu_car_manage_number' => '統合車両管理ＮＯ',
//            'base_short_name' => '拠点',
//            'user_name' => '担当者',
//            'insu_customer_code' => '顧客コード',
//            'insu_customer_name_kanji' => '顧客名',
//            'insu_customer_postal_code' => '郵便番号',
//            'insu_customer_address' => '住所',
//            'insu_car_name' => '車種名',
//            'insu_car_maker_code' => 'メーカー',
//            'insu_syaken_next_date' => '次回車検日',
//            'insu_customer_kouryaku_flg' => '攻略対象',
//            'insu_car_base_number' => '登録車両ＮＯ',
//            'insu_car_model' => '型式',
//            'insu_inspection_ym' => '保険終期月',
//            'insu_customer_insurance_type' => '保険区分',
//            'insu_customer_insurance_company' => '保険会社名称',
//            'insu_customer_kouryaku_flg' => '攻略対象車',
//            'insu_status' => '意向結果',
//
//            //'v_ciao.ciao_course' => 'チャオ'
//            'tb_target_cars.tgc_ciao_course' => 'チャオ'
//        ];
//    }
//
//    /**
//     * メインの処理
//     * @return [type] [description]
//     */
//    public function handle(){
//        // 表示も問題で一度変数に格納
//        $requestObj = $this->requestObj;
//
//        // 他のテーブルとJOIN
//        $builderObj = Insurance::joinBase()
//                               ->joinSales()
//                               ->joinCiao()
//                               ->joinInfo();
//
//        // 検索条件を指定
//        $builderObj = $builderObj->whereRequest( $this->requestObj );
//
//        // 並び替えの処理
//        $builderObj = $builderObj
//            ->orderBys( $this->sort['sort'] );
//
//        // 配列で値を取得
//        $data = $builderObj
//            ->get( $this->columns )
//            ->toArray();
//
//        if ( empty( $data ) ) {
//            throw new \Exception('データが見つかりません');
//        }
//
//        // 検索結果をCSV出力ように変換
//        $export = $this->convert( $data );
//
//
//        return OhInspection::download( $export, $this->headers, $this->filename );
//    }
//
//    /**
//     * 出力形式に変換
//     * @param $data
//     * @return
//     */
//    private function convert( $data ){
//        $export = null;
//
//        foreach( $data as $key => $value ){
//        	//$export[$key]['id'] = $value['id'];
//            $export[$key]['insu_car_manage_number'] = $value['insu_car_manage_number'];
//            $export[$key]['base_short_name'] = $value['base_short_name'];
//            $export[$key]['user_name'] = $value['user_name'];
//            $export[$key]['insu_customer_code'] = '\''.sprintf('%08d', $value['insu_customer_code']);
//            $export[$key]['insu_customer_name_kanji'] = $value['insu_customer_name_kanji'];
//
//            $export[$key]['insu_customer_postal_code'] = $value['insu_customer_postal_code'];
//            $export[$key]['insu_customer_address'] = $value['insu_customer_address'];
//            $export[$key]['insu_car_name'] = $value['insu_car_name'];
//            $export[$key]['insu_car_maker_code'] = $value['insu_car_maker_code'];
//
//            $export[$key]['insu_syaken_next_date'] = $value['insu_syaken_next_date'];
//            $export[$key]['insu_customer_kouryaku_flg'] = $value['insu_customer_kouryaku_flg'];
//
//            $export[$key]['insu_car_base_number'] = $value['insu_car_base_number'];
//            $export[$key]['insu_car_model'] = $value['insu_car_model'];
//
//            $export[$key]['insu_inspection_ym'] = $value['insu_inspection_ym'];
//            $export[$key]['insu_customer_insurance_type'] =  $value['insu_customer_insurance_type'];
//            $export[$key]['insu_customer_insurance_company'] = $value['insu_customer_insurance_company'];
//
//            $intentCodes = new IntentInsuranceCodes();
//            $export[$key]['insu_status'] = $intentCodes->getValue( $value['insu_status'] );
//
//            $export[$key]['tgc_ciao_course'] = str_replace( "チャオ", "",  $value['tgc_ciao_course'] );
//        }
//
//        return $export;
//    }
//
//}
