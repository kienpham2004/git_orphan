<?php

namespace App\Models\Append;

use App\Models\AbstractModel;
use DB;
/**
 * リコールデータに関するモデル
 */
class Recall extends AbstractModel {
    
    protected $table = "tb_recall";
    
    protected $fillable = [
        'recall_car_manage_number',// 統合車両管理No.
        'recall_no',                 // 管理番号
        'recall_division',          // 区分
        'recall_detail',            // 内容
        'recall_customer_code',    // 顧客 コード
        'recall_jisshibi'           // 実施日
    ];
    
    /**
     * 指定された期間の値を取得
     * @param  [type] $from [description]
     * @param  [type] $to   [description]
     * @return [type]       [description]
     */
    public static function findTarget( $code ) {
        // 検索条件を指定
        $builderObj = Recall::where( 'recall_car_manage_number',$code );

        // 値を取得
        $data = $builderObj->get();

        return $data;
    }
    
    ###########################
    ## CSVの処理
    ###########################
    
    /**
     * データの登録と更新
     * @param  [type] $values [description]
     * @return [type]         [description]
     */
    public static function merge( $values ) {
        \Log::debug( $values );
        
        Recall::updateOrCreate(
            [
                'recall_car_manage_number' => $values['recall_car_manage_number'],
                'recall_no' => $values['recall_no']
            ],
            $values
        );
        
    }
}
