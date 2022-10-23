<?php

namespace App\Commands\Mut\Dm;

use App\Original\Util\CodeUtil;
//use App\Models\Dm;
use App\Models\Dm;
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
            "自宅郵便番号", "自宅住所", "車両基本登録No", "顧客コード", "顧客漢字氏名 + 敬称", "車種名","Ｎシリーズ", "統合車両管理No",
            "車点検月", "作業種別名称", "次回車検日", "意向結果", "初度登録年月",
            "拠点版", "(現)担当者名", "(現)担当者の有無", "担当者画像", "営業コメント",
            "おもて", "うら",
            "拠点コード", "担当者コード", "担当者名", "拠点略称",
            "チャオコース", "備考", "チャオ分け", "車両タイプ"
        ];
    }

    /**
     * カラムとヘッダーの値を取得
     * @return array
     */
    private function getCsvParams(){
        return [
            // 表示部と共通カラム
            'tb_dm.dm_customer_postal_code' => '自宅郵便番号',
            'tb_dm.dm_customer_address' => '＊自宅住所',
            'tb_dm.dm_customer_code' => '顧客コード',
            'tb_dm.dm_customer_name_kanji' => '顧客漢字氏名',
            'tb_dm.dm_customer_kojin_hojin_flg' => '個人法人コード',
            'tb_dm.dm_car_manage_number' => '統合車両管理ＮＯ',
            'tb_dm.dm_car_name' => '車種',
            'tb_dm.dm_inspection_ym' => '対象年月',
            'tb_dm.dm_inspection_id' => '車点検区分',
            'tb_dm.dm_syaken_next_date' => '＊次回車検日',
            'tb_dm.dm_first_regist_date_ym' => '初度登録年月',
            'tb_base.base_short_name' => '拠点略称',
            'tb_customer.base_name' => '拠点名',
            'tb_base.base_code' => '拠点コード',
            'tb_user_account.user_id' => '(現)担当者コード',
            'tb_user_account.user_name' => '(現)担当者名',
            //'v_ciao.ciao_course' => 'チャオコース',
            'tb_target_cars.tgc_ciao_course' => 'チャオコース',
            // 以下単独カラム
            'tb_dm.dm_user_id' => '担当者コード',
            'tb_dm.dm_user_name' => '担当者名',
            'tb_user_account.file_name' => '担当者画像',
            'tb_user_account.comment' => 'コメント',
            'tb_dm.dm_car_base_number' => '＊車両基本登録No',
            // 201901顧客データ.csvに追加された項目
            'tb_dm.dm_car_type' => '車両タイプ',
            // 201908 Nシリーズ車検対応
            'tb_dm.dm_status' => '意向結果',
            
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

        foreach( $data as $key => $value ){

            // カラムの値を格納
            // 2019年1月DMに変更→tb_dmから変更
            $export[$key]["mentsuke"] = "";
            $export[$key]["orig_no"] = "";
            $export[$key]["no"] = "";
            $export[$key]["sequence"] = "";

            $export[$key]["dm_customer_postal_code"] = $value["dm_customer_postal_code"];
            $export[$key]["dm_customer_address"] = $value["dm_customer_address"];
            $export[$key]["dm_car_base_number"] = $value["dm_car_base_number"];
            
            $export[$key]["dm_customer_code"] = $value["dm_customer_code"];
            $export[$key]["dm_customer_name_kanji"] = trim( $value["dm_customer_name_kanji"] );
            $export[$key]["dm_customer_name_kanji"] = str_replace( "　　", "", $export[$key]["dm_customer_name_kanji"] );
            $export[$key]["dm_customer_name_kanji"] = str_replace( "*", "", $export[$key]["dm_customer_name_kanji"] );

            if( $value["dm_customer_kojin_hojin_flg"] == 2 ){
                $export[$key]["dm_customer_name_kanji"] .= " 御中";
            }else{
                $export[$key]["dm_customer_name_kanji"] .= " 様";
            }

            $export[$key]["dm_car_name"] = $value["dm_car_name"];
            
            // Ｎシリーズ判定
            $car_n = ["NBOX","NVAN","NWGN","Nｽﾗ","Nﾌﾟﾗ","Nﾜﾝ"];
            if( !empty($export[$key]["dm_car_name"]) && in_array($export[$key]["dm_car_name"], $car_n) ){
                $export[$key]["dm_car_n"] = "〇";
            }else{
                $export[$key]["dm_car_n"] = "";
            }
            $export[$key]["dm_car_manage_number"] = $value["dm_car_manage_number"];
            $export[$key]["dm_inspection_ym"] = date( "Y年m月", strtotime( $value["dm_inspection_ym"] . "01" ) );
            $export[$key]["dm_inspection_id"] = CodeUtil::getInspectDmTypeName( $value["dm_inspection_id"] );
            $export[$key]["dm_syaken_next_date"] = date( "Y年m月d日", strtotime( $value["dm_syaken_next_date"] ) );
            $export[$key]["dm_status"] = CodeUtil::getIntentSyatenkenName( $value["dm_status"] );
            $export[$key]["dm_first_regist_date_ym"] = date( "Y年m月", strtotime( $value["dm_first_regist_date_ym"] . "01" ) );
            $export[$key]["dm_base_code_eps"] = "'" . $value["base_code"] . '.eps';
            
            // (現)担当者名 user_name
            // 拠点コードがある場合
            if( $value["base_code"] != NULL ){
                // 担当者IDが無い場合
                if( empty($value["user_id"]) ){
                    // 店名にATが入っている場合
                    if( strpos($value["base_short_name"], "AT") !== FALSE ){
                        $export[$key]["user_name"] = mb_convert_kana( str_replace('　', ' ', $value["base_short_name"] ), 'R')."  サービス";
                    }
                    // 拠点コードが31未満の場合
                    elseif( $value["base_code"] < '31' ){
                        $export[$key]["user_name"] = str_replace('　', ' ', $value["base_short_name"])."店 サービス";
                    }else{
                        $export[$key]["user_name"] = str_replace('　', ' ', $value["base_name"]);
                    }
                }else{
                    $export[$key]["user_name"] = str_replace('　', ' ', $value["user_name"]);
                }
            }else{
                $export[$key]["user_name"] = str_replace('　', ' ', $value["user_name"]);
            }
            
            // (現)担当者の有無：tb_user_account のデータ有無（無：本社管理にて削除の可能性等）
            if( empty($value["user_id"]) ){
                $export[$key]["user_name_umu"] = "無";
            }else{
                $export[$key]["user_name_umu"] = "";
            }

            $export[$key]["dm_user_image"] = str_replace( "FaceImages/", "", $value["file_name"] );
            $export[$key]["dm_user_comment"] = $value["comment"];

            $export[$key]["omote"] = "";
            $export[$key]["ura"] = "";
            
            $export[$key]["base_code"] = "'" . $value["base_code"];
            $export[$key]["dm_user_id"] = "'" . $value["dm_user_id"];
            $export[$key]["dm_user_name"] = $value["dm_user_name"];
            $export[$key]["base_short_name"] = $value["base_short_name"];
            
            $export[$key]["ciao"] = str_replace( "チャオ", "", $value["tgc_ciao_course"] );
            $export[$key]["bikou"] = "";

            if( !empty( $value["tgc_iao_course"] ) == True ){
                $export[$key]["ciao_flg"] = "加入";
            }else{
                $export[$key]["ciao_flg"] = "未加入";
            }
            $export[$key]["dm_car_type"] = $value["dm_car_type"];
           
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
