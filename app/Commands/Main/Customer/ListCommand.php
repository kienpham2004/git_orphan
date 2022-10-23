<?php

namespace App\Commands\Main\Customer;

use App\Models\Customer;
use App\Commands\Command;

/**
 * 取り込みデータの実績一覧を取得するコマンド
 *
 * @author yhatsutori
 */
class ListCommand extends Command{

    /**
     * コンストラクタ
     * @param array $sort 並び順
     * @param $requestObj 検索条件
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
            'base_id' => '拠点Id',
            'base_code' => '拠点コード',
            'base_name' => '拠点 名',
            'user_code' => '担当者コード',
            'user_name' => '担当者 名',
            'tb_user_account.deleted_at' => '削除',
            'customer_code' => '顧客 コード',
            'car_manage_number' => '統合車両管理No.',
            'customer_name_kanji' => '顧客 名',
            'customer_name_kata' => '顧客カナ',
            'gensen_code_name' => '源泉',
            'customer_category_code_name' => '顧客分類',
            'customer_address' => '自宅住所',
            'customer_tel' => '自宅TEL',
            'mobile_tel' => '携帯TEL',
            'customer_office_tel' => '勤務先TEL',
            'car_name' => '車名',
            'car_year_type' => '年式',
            'first_regist_date_ym' => '初度登録年月',
            'cust_reg_date' => '登録日',
            'car_base_number' => '車両登録No.',
            'car_model' => '型式',
            'car_service_code' => 'サービス通称名',
            'car_frame_number' => 'フレームNo.',
            'car_buy_type' => '自販他販区分',
            'car_new_old_kbn_name' => '新中区分',
            'syaken_times' => '車検回数',
            'syaken_next_date' => '次回車検日',
            'customer_insurance_type' => '任意保険加入区分',
            'customer_insurance_company' => '任意保険会社',
            'customer_insurance_end_date' => '任意保険終期',
            'credit_hensaihouhou' => '選択プラン_クレジット返済方法(テキスト)',
            'credit_hensaihouhou_name' => 'クレジット返済方法',
            'first_shiharai_date_ym' => '選択プラン_初回支払年月',
            'keisan_housiki_kbn' => '計算方式',
            'credit_card_select_kbn' => 'クレジット・カード選択区分',
            'memo_syubetsu' => 'メモ種別',
            'shiharai_count' => '選択プラン_支払回数',
            'sueoki_zankaritsu' => '選択プラン_据置・残価率',
            'last_shiharaikin' => '選択プラン_最終回支払額金',
            'customer_kouryaku_flg' => '攻略対象車',
            'abc_zone' => 'ABCゾーン',
            'ciao_course' => 'チャオコース',
            'ciao_end_date' => '会員証有効期間終期',
            //update field
            //'htc_login_status' => 'HTC',
            'mi_htc_login_flg' => 'HTC',

            'tb_customer.created_at' => '登録日時',
            'tb_customer.updated_at' => '更新日時',
            'tb_user_account.user_name' => '担当者名(マスター)',

            'tb_customer_umu.umu_csv_flg' => '車両無',
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
                ->joinUmu()
            // 20220318 update join htc -> info
//                ->joinHtc()
                ->JoinInfoSyaken()
                ->whereNull('tb_customer.deleted_at');
        
        // 検索条件を指定
        $builderObj = $builderObj->whereRequest( $this->requestObj );
        
        // 並び替えの処理
        // 2022/03/24 update sort
        if ( isset($this->sort['sort']['mi_htc_login_flg']) ){
            $builderObj = $builderObj->orderByRaw( "case when mi_htc_login_flg is null then '0'
                                                                          else mi_htc_login_flg
                                                          end {$this->sort['sort']['mi_htc_login_flg']}");
        }
        else {
            $builderObj = $builderObj->orderBys($this->sort['sort']);
        }

        // ペジネートの処理
        $data = $builderObj
            ->paginate( $this->requestObj->row_num, $this->columns )
            // 表示URLをpagerに指定
            ->setPath('pager');
        
        $list_cre_type = 
                [
                    'A' => '据置クレ',
                    'B_D_E_1_2_7_8' => '通クレ',
                    'C_9' => '残クレ'
                ];
        
        return array($data, $list_cre_type);
    }

}
