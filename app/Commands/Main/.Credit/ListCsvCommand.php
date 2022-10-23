<?php
//
//namespace App\Commands\Main\Credit;
//
//use App\Original\Util\CodeUtil;
//use App\Models\Credit;
//use App\Commands\Command;
//use App\Original\Codes\Intent\IntentCreditCodes;
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
//            //'tb_credit.id' => 'id',
//            'cre_car_manage_number' => '統合車両管理ＮＯ',
//            'base_short_name' => '拠点',
//            'user_name' => '担当者',
//            'cre_customer_code' => '顧客コード',
//            'cre_customer_name_kanji' => '顧客名',
//            'cre_customer_postal_code' => '郵便番号',
//            'cre_customer_address' => '住所',
//            'cre_car_name' => '車種名',
//            'cre_car_maker_code' => 'メーカー',
//            'cre_syaken_next_date' => '次回車検日',
//            'cre_customer_kouryaku_flg' => '攻略対象',
//            'cre_car_base_number' => '登録車両ＮＯ',
//            'cre_car_model' => '型式',
//            'cre_customer_kouryaku_flg' => '攻略対象車',
//            'cre_status' => '意向結果',
//            'cre_inspection_ym' => '契約満了年月',
//            'cre_credit_manryo_date_ym' => '契約満了年月',
//            'cre_credit_hensaihouhou_name' => 'クレジット返済方法名称',
//            'cre_shiharai_count' => '支払回数',
//            'cre_first_shiharai_date_ym' => '初回支払年月',
//            'cre_keisan_housiki_kbn' => '計算方式区分',
//            'cre_credit_card_select_kbn' => 'クレジット・カード選択区分',
//            'cre_memo_syubetsu' => 'メモ種別',
//            'cre_sueoki_zankaritsu' => '据置・残価率',
//            'cre_last_shiharaikin' => '最終回支払金',
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
//        $builderObj = Credit::joinBase()
//                            ->joinSales()
//                            ->joinCiao()
//                            ->joinInfo();
//
//        // 検索条件を指定
//        $builderObj = $builderObj->whereRequest( $this->requestObj );
//
//        // 並び替えの処理
//        $builderObj = $builderObj->orderBys( $this->sort['sort'] );
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
//            //$export[$key]['id'] = $value['id'];
//            $export[$key]['cre_car_manage_number'] = $value['cre_car_manage_number'];
//            $export[$key]['base_short_name'] = $value['base_short_name'];
//            $export[$key]['user_name'] = $value['user_name'];
//            $export[$key]['cre_customer_code'] = '\''.sprintf('%08d', $value['cre_customer_code']);
//            $export[$key]['cre_customer_name_kanji'] = $value['cre_customer_name_kanji'];
//
//            $export[$key]['cre_customer_postal_code'] = $value['cre_customer_postal_code'];
//            $export[$key]['cre_customer_address'] = $value['cre_customer_address'];
//            $export[$key]['cre_car_name'] = $value['cre_car_name'];
//            $export[$key]['cre_car_maker_code'] = $value['cre_car_maker_code'];
//
//            $export[$key]['cre_syaken_next_date'] = $value['cre_syaken_next_date'];
//            $export[$key]['cre_customer_kouryaku_flg'] = $value['cre_customer_kouryaku_flg'];
//
//            $export[$key]['cre_car_base_number'] = $value['cre_car_base_number'];
//            $export[$key]['cre_car_model'] = $value['cre_car_model'];
//
//            $intentCodes = new IntentCreditCodes();
//            $export[$key]['cre_status'] = $intentCodes->getValue( $value['cre_status'] );
//
//            $export[$key]['cre_inspection_ym'] = $value['cre_inspection_ym'];
//            $export[$key]['cre_credit_manryo_date_ym'] = $value['cre_credit_manryo_date_ym'];
//
//            $export[$key]['cre_credit_hensaihouhou'] = CodeUtil::getCreditType( $value['cre_credit_hensaihouhou_name'] );
//            $export[$key]['cre_shiharai_count'] =  $value['cre_shiharai_count'];
//
//            $export[$key]['cre_first_shiharai_date_ym'] = $value['cre_first_shiharai_date_ym'];
//            $export[$key]['cre_keisan_housiki_kbn'] = $value['cre_keisan_housiki_kbn'];
//            $export[$key]['cre_credit_card_select_kbn'] = $value['cre_credit_card_select_kbn'];
//            $export[$key]['cre_memo_syubetsu'] = $value['cre_memo_syubetsu'];
//            $export[$key]['cre_sueoki_zankaritsu'] = $value['cre_sueoki_zankaritsu'];
//            $export[$key]['cre_last_shiharaikin'] = $value['cre_last_shiharaikin'];
//
//            $export[$key]['tgc_ciao_course'] = str_replace( "チャオ", "",  $value['tgc_ciao_course'] );
//        }
//
//        return $export;
//    }
//
//}
