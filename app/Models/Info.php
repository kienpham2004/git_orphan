<?php

namespace App\Models;

use App\Lib\Util\DateUtil;
use App\Lib\Codes\DispCodes;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

/**
 * TOPのお知らせのモデル
 */
class Info extends AbstractModel {
    
    use SoftDeletes;
    
    // テーブル名
    protected $table = 'tb_info';

    // 変更可能なカラム
    protected $fillable = [
        'info_target_date',
        //'info_base_code',
        'info_user_id',
        'info_view_flg',
        'info_view_date_min',
        'info_view_date_max',
        'info_title',
        'info_body'
    ];

    ######################
    ## other
    ######################
    
    /**
     * スタッフを動的にバインド
     */
    public function staff() {
        return $this->hasOne( 'App\Models\UserAccount', 'id', 'info_user_id' );
    }
    
    ###########################
    ## スコープメソッド(Join文)
    ###########################

    /**
     * 拠点とJoinするスコープメソッド
     *
     */
    public function scopeJoinUser( $query ) {
        $query->leftJoin(
            'tb_user_account',
            function( $join ) {
                $join->on( 'tb_info.info_user_id', '=', 'tb_user_account.id' )
                    ->whereNull( 'tb_user_account.deleted_at' );
            }
        );

        return $query;
    }
    
    ###########################
    ## スコープメソッド(条件式)
    ###########################
    
    /**
     * お知らせの表示/非表示で検索
     *
     * @return $query
     */
    public function scopeWhereViewFlg( $query, $value ) {
        if( !empty( $value ) ) {
            // 表示かどうかを判定する
            if( DispCodes::isDisp( $value ) ) {
                $query->where( 'info_view_flg', DispCodes::DISP );
            } else {
                $query->where( 'info_view_flg', 0 );
            }
        }
        return $query;
    }

    /**
     * 掲載状態で検索
     *
     * @param  integer $value 掲載状態（期間中/終了）
     * @return $query
     */
    public function scopeWhereViewStatus( $query, $value ) {
        \Log::debug( $value );
        if( !empty( $value ) ) {
            $now = DateUtil::toYmd( DateUtil::now(), '-' );
            if( $value == '1' ) {
                $query->where( 'info_view_date_min', '<=', $now )
                    ->where( 'info_view_date_max', '>=', $now );
            } else if( $value == '2' ) {
                $query->where( 'info_view_date_max', '<', $now );
            }
        }
        return $query;
    }
    
    ###########################
    ## Find Info Command
    ###########################
    
    /**
     * 表示設定されたお知らせを最大10件取得
     * @return collection
     */
    public static function showInfoList() {
        return self::where( 'info_view_flg', DispCodes::DISP )
            ->whereRaw(DB::raw( "to_char(now(),'yyyymmdd') between to_char(info_view_date_min,'yyyymmdd') and to_char(info_view_date_max,'yyyymmdd')" ) )
            ->orderBy( 'info_target_date', 'desc' )
            ->take( 10 )
            ->get();
    }

    ###########################
    ## Info List Commands
    ###########################
    
    /**
     * 検索条件を指定するメソッド
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereRequest( $query, $requestObj ){
        // 検索条件を指定
        $query = $query
            ->whereMatch( 'info_user_id', $requestObj->info_user_id )
            ->whereViewFlg( $requestObj->info_view_flg )
            ->whereViewStatus( $requestObj->info_view_status )
            ->whereMatch( 'info_target_date', $requestObj->info_target_date )
            ->whereLike( 'info_title', $requestObj->info_title )
            ->whereLike( 'info_body', $requestObj->info_body )
            ->includeDeleted( "1" ); // 削除データを含めるか
            //->includeDeleted( $requestObj->is_delete ); // 削除データを含めるか

        return $query;
    }

}
