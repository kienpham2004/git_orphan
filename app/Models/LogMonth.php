<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

/**
 * ログ月モデル
 *
 * @author hung_hkn
 *
 */
class LogMonth extends AbstractModel {
    
    use SoftDeletes;

    // テーブル名
    protected $table = 'tb_log_month';

    // 変更可能なカラム
    protected $fillable = [
        'id',
        'lm_file_name',
        'lm_ym',
        'lm_last_downloaded',
        'lm_last_downloaded_by',
        'deleted_at'
    ];

     // 日付のカラム
     protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * ログ一覧の取得
     */
    public static function scopeGetAll() {
        return self::orderBy( 'lm_ym', 'desc' )->get();
    }

    /**
     * 最終ダウンロードの担当者を動的にバインド
     */
    public function user() {
        return $this->hasOne( 'App\Models\UserAccount', 'id', 'lm_last_downloaded_by' );
    }

    /**
     * 3ヶ月前のすべてログ情報を取得
     */
    public static function getInfoNotDownloadOfThreeMonthBefore() {
        $year = date( "Y", strtotime( "-3 months" ) );
        $month = date( "m", strtotime( "-3 months" ) );
        $ym = (string)($year .$month);

        return self::where('lm_ym', '<=', $ym)
            ->whereRaw(DB::raw( '(lm_last_downloaded is null and lm_last_downloaded_by is null)' ) )
            ->get();
    }
}
