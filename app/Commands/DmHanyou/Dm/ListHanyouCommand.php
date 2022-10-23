<?php

namespace App\Commands\DmHanyou\Dm;

use App\Models\DmHanyou\CustomerDm;
use App\Commands\Command;

/**
 * DM送付リスト画面で一覧を取得
 *
 * @author yhatsutori
 *
 */
class ListHanyouCommand extends Command{
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
//            'tb_customer_dm_1.id' => 'ID',
//            'car_manage_number' => '',
//            'tb_base.base_code' => '',
//            'tb_base.base_short_name' => '',
//            'tb_user_account.user_id' => '',
//            'tb_user_account.user_name' => '',
//            'customer_code' => '',
//            'customer_name_kanji' => '',
//            'customer_name_kata' => '顧客名(カナ)', // 一覧画面だけ
//            'customer_postal_code' => '',
//            'customer_address' => '',
//            'car_name' => '',
//            'syaken_times' => '',
//            'syaken_next_date' => '',
//            'customer_kouryaku_flg' => '',
//            'car_new_old_kbn_name' => '',
//            'original_dm_flg' => '',
//            'original_dm_unnecessary_reason' => '',
//            'original_car_new_old_flg' => '',
//
//            'ciao_course' => ''
//        ];
//    }
//
//    /**
//     * 一覧データの取得
//     * @return collection
//     */
//    public function handle(){
//        // 他のテーブルとJOIN
//        $builderObj = CustomerDm::joinBase()
//                                ->joinSales()
//                                ->joinCiao();
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
}
