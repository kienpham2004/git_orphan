<?php

namespace App\Commands\Mut\DmHanyou;

use App\Models\DmHanyou\CustomerDm;
use App\Commands\Command;

/**
 * 六三管理のDM送付リスト一覧を取得するコマンド
 *
 * @author yhatsutori
 */
class ListCommand extends Command{

    /**
     * コンストラクタ
     * @param [type] $sort       [description]
     * @param [type] $requestObj [description]
     */
    public function __construct( $sort, $requestObj ){
        $this->sort = $sort;
        $this->requestObj = $requestObj;

        // カラムとヘッダーの値を取得
        $csvParams = $this->getCsvParams();
        // カラムを取得
        $this->columns = array_keys( $csvParams );
        // ヘッダーを取得
        $this->headers = array_values( $csvParams );
    }

    /**
     * カラムとヘッダーの値を取得
     * @return array
     */
    private function getCsvParams(){
        return [
            'tb_customer_dm_1.car_manage_number' => '統合車両管理ＮＯ',
            'tb_customer_dm_1.car_name' => '車種',

            'tb_customer_dm_1.customer_postal_code' => '自宅郵便番号',
            'tb_customer_dm_1.customer_address' => '＊自宅住所',
            'tb_customer_dm_1.car_base_number' => '＊車両基本登録No',
            'tb_customer_dm_1.customer_name_kanji' => '顧客漢字氏名',
            'tb_customer_dm_1.customer_kojin_hojin_flg' => '個人法人コード',
            'tb_customer_dm_1.syaken_next_date' => '＊次回車検日',

            'tb_customer_dm_1.syaken_times' => '車検回数',
            'tb_customer_dm_1.car_new_old_kbn_name' => '新中区分名称',
            'tb_customer_dm_1.original_car_new_old_flg' => 'オリジナル 新車/中古車',

            'tb_customer_dm_1.base_code' => '拠点コード',
            'tb_base.base_short_name' => '拠点略称',
            'tb_customer_dm_1.user_id' => '担当者コード',
            'tb_user_account.user_name' => '担当者',
            'tb_customer_dm_1.user_name' => '担当者氏名',
            'tb_user_account.file_name' => '担当者画像',
            'tb_user_account.comment' => 'コメント',
            //'v_ciao.ciao_course' => 'チャオコース',
            'tb_target_cars.tgc_ciao_course' => 'チャオコース',
        ];
    }
    
    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 他のテーブルとJOIN
        $builderObj = CustomerDm::joinBase()
                                ->joinSales()
                                ->joinCiao();
                        
        // 検索条件を指定
        $builderObj = $builderObj->whereMutRequest( $this->requestObj );
        
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
