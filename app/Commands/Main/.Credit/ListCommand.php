<?php
//
//namespace App\Commands\Main\Credit;
//
//use App\Models\Credit;
//use App\Commands\Command;
//
///**
// * 取り込みデータの実績一覧を取得するコマンド
// *
// * @author yhatsutori
// */
//class ListCommand extends Command{
//
//    /**
//     * コンストラクタ
//     * @param array $sort 並び順
//     * @param $requestObj 検索条件
//     */
//    public function __construct( $sort, $requestObj ){
//        $this->sort = $sort;
//        $this->requestObj = $requestObj;
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
//            'tb_credit.id' => 'id',
//            'cre_car_manage_number' => '統合車両管理ＮＯ',
//            'base_short_name' => '拠点',
//            'user_name' => '担当者',
//            'cre_customer_code' => '顧客コード',
//            'cre_customer_name_kanji' => '顧客名',
//            'cre_customer_name_kata' => '顧客名(カナ)', // 一覧画面だけ
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
//            //'cre_first_shiharai_date_ym' => '初回支払年月',
//            //'cre_keisan_housiki_kbn' => '計算方式区分',
//            //'cre_credit_card_select_kbn' => 'クレジット・カード選択区分',
//            //'cre_memo_syubetsu' => 'メモ種別',
//            //'cre_sueoki_zankaritsu' => '据置・残価率',
//            //'cre_last_shiharaikin' => '最終回支払金',
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
//        // ペジネートの処理
//        $data = $builderObj
//            ->paginate( $this->requestObj->row_num, $this->columns )
//            // 表示URLをpagerに指定
//            ->setPath('pager');
//
//        return $data;
//    }
//
//}
