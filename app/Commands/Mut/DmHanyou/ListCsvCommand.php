<?php

namespace App\Commands\Mut\DmHanyou;

use App\Original\Util\CodeUtil;
use App\Models\DmHanyou\CustomerDm;
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
    public function __construct( $sort, $requestObj, $filename="target.csv" ){
        $this->sort = $sort;
        $this->requestObj = $requestObj;
        $this->filename = $filename;

        // カラムとヘッダーの値を取得
        $csvParams = $this->getCsvParams();
        // カラムを取得
        $this->columns = array_keys( $csvParams );
        // ヘッダーを取得
        //$this->headers = array_values( $csvParams );

        $this->headers = [
            "面付No", "orig no", "no", "シーケンス",
            "自宅郵便番号", "自宅住所", "車両基本登録No", "顧客コード", "顧客漢字氏名 + 敬称", "車種名", "統合車両管理No",
            //"点検月",
            "車両満了日", "拠点版", "担当者氏名", "担当者画像", "営業コメント",
            "おもて", "うら",
            "拠点コード", "担当者コード", "担当者氏名(漢字)", "拠点略称",
            //"作業種別名称",
            "チャオコース", "備考", "チャオ分け",
            "車検回数", "新車/中古車"
        ];
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
            'tb_customer_dm_1.customer_code' => '顧客コード',
            'tb_customer_dm_1.customer_name_kanji' => '顧客漢字氏名',
            'tb_customer_dm_1.customer_kojin_hojin_flg' => '個人法人コード',
            'tb_customer_dm_1.syaken_next_date' => '＊次回車検日',
            'tb_customer_dm_1.base_code' => '拠点コード',
            'tb_base.base_short_name' => '拠点略称',
            'tb_customer_dm_1.user_id' => '担当者コード',
            'tb_user_account.user_name' => '担当者',
            'tb_customer_dm_1.user_name' => '担当者氏名',
            'tb_user_account.file_name' => '担当者画像',
            'tb_user_account.comment' => 'コメント',
            //'v_ciao.ciao_course' => 'チャオコース',
            'tb_target_cars.tgc_ciao_course' => 'チャオコース',

            'tb_customer_dm_1.syaken_times' => '車検回数',
            'tb_customer_dm_1.car_new_old_kbn_name' => '新中区分名称',
            'tb_customer_dm_1.original_car_new_old_flg' => 'オリジナル 新車/中古車'
            
        ];
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 表示も問題で一度変数に格納
        $requestObj = $this->requestObj;

        // 他のテーブルとJOIN
        $builderObj = CustomerDm::joinBase()
                                ->joinSales()
                                ->joinCiao();

        // 検索条件を指定
        $builderObj = $builderObj->whereMutRequest( $this->requestObj );
        
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
        $export = [];

        $allAddress = [];

        foreach( $data as $key => $value ){
            // 同じ住所の人には送らないことにすること
            if( !isset( $allAddress[$value["customer_address"]] ) == True ){
                $allAddress[$value["customer_address"]] = 1;

                // カラムの値を格納
                $export[$key]["mentsuke"] = "";
                $export[$key]["orig_no"] = "";
                $export[$key]["no"] = "";
                $export[$key]["sequence"] = "";

                $export[$key]["customer_postal_code"] = $value["customer_postal_code"];
                $export[$key]["customer_address"] = $value["customer_address"];
                $export[$key]["car_base_number"] = $value["car_base_number"];
                
                $export[$key]["customer_code"] = $value["customer_code"];
                $export[$key]["customer_name_kanji"] = trim( $value["customer_name_kanji"] );
                $export[$key]["customer_name_kanji"] = str_replace( "　　", "", $export[$key]["customer_name_kanji"] );
                $export[$key]["customer_name_kanji"] = str_replace( "*", "", $export[$key]["customer_name_kanji"] );

                if( $value["customer_kojin_hojin_flg"] == 2 ){
                    $export[$key]["customer_name_kanji"] .= " 御中";
                }else{
                    $export[$key]["customer_name_kanji"] .= " 様";
                }

                $export[$key]["car_name"] = $value["car_name"];
                $export[$key]["car_manage_number"] = $value["car_manage_number"];
                
                /*
                if( $value["inspection_id"] == 4 ){
                    $export[$key]["syaken_next_date"] = date( "Y年m月d日", strtotime( $value["syaken_next_date"] ) );

                }else{
                    $export[$key]["syaken_next_date"] = "";
                }
                */
                $export[$key]["syaken_next_date"] = date( "Y年m月d日", strtotime( $value["syaken_next_date"] ) );

                $export[$key]["base_code_eps"] = "'" . $value["base_code"] . '.eps';
                $export[$key]["user_name_1"] = $value["user_name"];

                $export[$key]["user_image"] = str_replace( "FaceImages/", "", $value["file_name"] );
                $export[$key]["user_comment"] = $value["comment"];

                $export[$key]["omote"] = "";
                $export[$key]["ura"] = "";
                
                $export[$key]["base_code"] = "'" . $value["base_code"];
                $export[$key]["user_id"] = "'" . $value["user_id"];
                //$export[$key]["user_name_2"] = $value["user_name"];
                $export[$key]["user_name"] = $value["user_name"];
                $export[$key]["base_short_name"] = $value["base_short_name"];

                $export[$key]["ciao"] = str_replace( "チャオ", "", $value["ciao_course"] );
                $export[$key]["bikou"] = "";

                if( !empty( $value["ciao_course"] ) == True ){
                    $export[$key]["ciao_flg"] = "加入";
                }else{
                    $export[$key]["ciao_flg"] = "未加入";
                }
                
                $export[$key]["syaken_times"] = $value["syaken_times"] . "回";

                if( $value["original_car_new_old_flg"] == "1" ){
                    $export[$key]["original_car_new_old_flg"] = "新車";
                }elseif( $value["original_car_new_old_flg"] == "2" ){
                    $export[$key]["original_car_new_old_flg"] = "中古車";
                }

            }
        }
        
        // 不要なデータを除外
        foreach( $export as $key => $values ){
            foreach( $values as $column => $value ){
                $export[$key][$column] = trim( $value );     
            }
        }

        return $export;
    }

}
