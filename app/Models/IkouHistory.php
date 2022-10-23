<?php

namespace App\Models;

use App\Original\Util\CodeUtil;

class IkouHistory extends AbstractModel {
    // テーブル名
    protected $table = 'tb_ikou_history';

    // 変更可能なカラム
    protected $fillable = [
        'ikh_inspection_ym',             // 対象年月
        'ikh_customer_code',             // 顧客コード
        'ikh_car_manage_number',        // 統合車両管理ＮＯ
        'ikh_ikou_time',                 // 意向時期0~6
        'ikh_katsudo_code',              // 意向区分コード
        'ikh_katsudo_date',              // 意向確認日
        'ikh_syaken_next_date',         // 意向確認日（活動日報）
        'ikh_status_katsudo_csv',       // 意向区分（活動日報）
        'ikh_status_katsudo_code',      // 意向区分コード（活動日報）
        'ikh_status_csv',                // 意向区分
        'ikh_status_code'                // 意向区分コード
    ];
    
    ###########################
    ## 抽出の処理
    ###########################
    
    /**
     * データの登録と更新
     * @param  [type] $values [description]
     * @return [type]         [description]
     * @todo   車点検種別がある場合は基本的に必須項目とする
     */
    public static function merge( $values ) {
        $fieldData = static::convertFromViewTargetCars( $values );

        // 現在日時
        if( empty( $fieldData["created_at"] ) == True ){
            $fieldData["created_at"] = date("Y-m-d");
        }
        // 現在日時
        $fieldData["updated_at"] = date("Y-m-d");

        // 管理者で指定
        if( empty( $fieldData["created_by"] ) == True ){
            $fieldData["created_by"] = "1";
        }
        // 管理者で指定
        $fieldData["updated_by"] = "1";

        IkouHistory::updateOrCreate(
            [
                'ikh_inspection_ym' => $fieldData['ikh_inspection_ym'],
                'ikh_customer_code' => $fieldData['ikh_customer_code'],
                'ikh_car_manage_number' => $fieldData['ikh_car_manage_number'],
                'ikh_ikou_time' => $fieldData['ikh_ikou_time']
            ],
            $fieldData
        );
    }

    /**
     * データを登録用に変換する
     *
     * @param unknown $values
     */
    public static function convertFromViewTargetCars( $values ) {
        $filter = collect([
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'created_by',
            'updated_by',
            'deleted_by'
        ]);

        foreach ( $values as $key => $value ) {
            $ex = $filter->contains( $key );
            // \Log::debug( $ex );
            // $filter->contains()の戻り値はboolianなので、
            // emptyではちょっと違和感を覚えました。
            // if (! $ex) {} でもいいように思います
            if( empty( $ex ) ) {
                // DM用のカラム名を取得
                $setKey = str_replace( "dsya_", "ikh_", $key );
                // 値の指定
                $result[$setKey] = $value;
            }
        }

        return $result;
    }

}
