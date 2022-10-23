<?php

namespace App\Commands\Dm\Dm;

use App\Models\TargetCars;
use App\Commands\Command;

/**
 * DM送付リスト画面で一覧を取得
 *
 * @author yhatsutori
 *
 */
class ListTenkenLastCommand extends Command{
//
//    /**
//     * コンストラクタ
//     * @param [type] $sort       [description]
//     * @param [type] $requestObj [description]
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
//            // CSV部と共通カラム
//            'tb_target_cars.id' => 'ID',
//            'tb_target_cars.tgc_customer_postal_code' => '自宅郵便番号',
//            'tb_target_cars.tgc_customer_address' => '＊自宅住所',
//            'tb_target_cars.tgc_customer_code' => '顧客コード',
//            'tb_target_cars.tgc_customer_name_kanji' => '顧客漢字氏名',
//            'tb_target_cars.tgc_customer_kojin_hojin_flg' => '個人法人コード',
//            'tb_target_cars.tgc_car_manage_number' => '統合車両管理No',
//            'tb_target_cars.tgc_car_name' => '車種名',
//            'tb_target_cars.tgc_inspection_ym' => '対象年月',
//            'tb_target_cars.tgc_inspection_id' => '車点検区分',
//            'tb_target_cars.tgc_syaken_next_date' => '＊次回車検日',
//            'tb_target_cars.tgc_first_regist_date_ym' => '初度登録年月',
//            'tb_base.base_short_name' => '拠点略称',
//            'tb_customer.base_name' => '拠点名',
//            'tb_base.base_code' => '拠点コード',
//            'tb_user_account.user_id' => '(現)担当者コード',
//            'tb_user_account.user_name' => '(現)担当者名',
//            'v_ciao.ciao_course' => 'チャオコース',
//            // 以下単独カラム
//            'tb_target_cars.tgc_customer_kouryaku_flg' => '',
//            'tb_target_cars.tgc_status' => '意向結果',
//            'tb_target_cars.tgc_dm_flg' => '',
//            'tb_target_cars.tgc_dm_unnecessary_reason' => '',
//
//            'tb_manage_info.mi_rstc_reserve_commit_date' => '予約承認日時',
//            'tb_manage_info.mi_rstc_get_out_date' => '出庫日時',
//            'tb_manage_info.mi_rstc_reserve_status' => '状況',
//
//            'tb_target_cars.tgc_customer_name_kata' => '顧客名(カナ)', // 一覧画面だけ
//        ];
//    }
//
//    /**
//     * 一覧データの取得
//     * @return collection
//     */
//    public function handle(){
//        // 他のテーブルとJOIN
//        $builderObj = TargetCars::joinBase()
//                                ->joinSales()
//                                ->joinCiao()
//                                ->joinInfo()
//                                ->joinCustomer();
//
//        // 検索条件を指定
//        $builderObj = $builderObj->whereDmTenkenLastRequest( $this->requestObj );
////        $builderObj = $builderObj->whereDmRequest( $this->requestObj, '4' );
//
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
}
