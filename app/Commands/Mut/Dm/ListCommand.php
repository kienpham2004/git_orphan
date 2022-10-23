<?php

namespace App\Commands\Mut\Dm;

use App\Models\TargetCars;
use App\Models\Dm;
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
            'tb_dm.dm_car_manage_number' => '統合車両管理ＮＯ',
            'tb_dm.dm_car_name' => '車種',

            'tb_dm.dm_customer_postal_code' => '自宅郵便番号',
            'tb_dm.dm_customer_address' => '＊自宅住所',
            'tb_dm.dm_car_base_number' => '＊車両基本登録No',
            'tb_dm.dm_customer_name_kanji' => '顧客漢字氏名',
            'tb_dm.dm_customer_kojin_hojin_flg' => '個人法人コード',
            'tb_dm.dm_inspection_ym' => '対象年月',
            'tb_dm.dm_inspection_id' => '車点検区分',
            'tb_dm.dm_syaken_next_date' => '＊次回車検日',
            'tb_dm.dm_base_code' => '拠点コード',
            'tb_base.base_short_name' => '拠点略称(マスター)',
            'tb_base.base_name' => '拠点名(マスター)',
            
            'tb_dm.dm_user_id' => '担当者コード',
            'tb_dm.dm_user_name' => '担当者氏名',
            
            'tb_user_account.user_name' => '担当者(マスター)',
            'tb_user_account.file_name' => '担当者画像',
            'tb_user_account.comment' => 'コメント',
            
            //'v_ciao.ciao_course' => 'チャオコース',
            'tb_target_cars.tgc_ciao_course' => 'チャオコース',
            
            'tb_manage_info.mi_rstc_reserve_status' => '状況',
            'tb_dm.dm_status' => '最新意向',
        ];
    }
    
    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 他のテーブルとJOIN
        $builderObj = Dm::joinBase()
                        ->joinSales()
                        ->joinCiao()
                        ->joinInfo()
                        ->joinCustomer();
                        
        // 検索条件を指定
        $builderObj = $builderObj->whereDmRequest( $this->requestObj );
        
        // 並び替えの処理
        $builderObj = $builderObj->orderBys( $this->sort['sort'] );
        
        // ペジネートの処理
        $data = $builderObj
            ->paginate( $this->requestObj->row_num, $this->columns )
            // 表示URLをpagerに指定
            ->setPath('pager');
        
        $list_dm_car_name =
                [
                    'NBOX_NVAN_NWGN_Nｽﾗ_Nﾌﾟﾗ_Nﾜﾝ'=> 'Nシリーズ'
                ];
            
        return array( $data, $list_dm_car_name );
    }
    
}
