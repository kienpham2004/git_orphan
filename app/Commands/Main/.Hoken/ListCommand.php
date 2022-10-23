<?php
//
//namespace App\Commands\Main\Hoken;
//
//use App\Models\Insurance;
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
//            'tb_insurance.id' => 'id',
//            'insu_car_manage_number' => '統合車両管理ＮＯ',
//            'base_short_name' => '拠点',
//            'user_name' => '担当者',
//            'insu_customer_code' => '顧客コード',
//            'insu_customer_name_kanji' => '顧客名',
//            'insu_customer_name_kata' => '顧客名(カナ)', // 一覧画面だけ
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
