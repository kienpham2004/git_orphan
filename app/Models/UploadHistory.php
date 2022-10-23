<?php

namespace App\Models;

class UploadHistory extends AbstractModel {

    // テーブル名
    protected $table = 'tb_history';

    // 変更可能なカラム
    protected $fillable = [
        'type_code',
        'this_time_count',
        'total_count',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];

    ###########################
    ## CSVの処理
    ###########################
    
    /**
     * データの登録と更新
     * @param  [type] $type_code       [description]
     * @param  [type] $this_time_count [description]
     * @param  [type] $total           [description]
     * @return [type]                  [description]
     */
    public static function merge( $type_code, $this_time_count, $total ) {
        UploadHistory::updateOrCreate(
            ['type_code' => $type_code],
            ['this_time_count' => $this_time_count, 'total_count' => $total]
        );
        
    }

    /**
     * csvアップロード履歴の全データを取得
     *
     * @return collection
     */
    public static function findAll(){
        return UploadHistory::orderBy('type_code', 'asc')
            ->whereNotIn('type_code', [88,99])
                            ->get();
    }

}
