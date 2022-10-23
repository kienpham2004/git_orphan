<?php

namespace App\Models;

use App\Lib\Codes\CheckCodes;
use App\Models\Role;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

/**
 * 担当者モデル
 *
 * @author yhatsutori
 *
 */
class UserAccount extends AbstractModel implements AuthenticatableContract, CanResetPasswordContract {

    use Authenticatable, CanResetPassword, SoftDeletes;

    // テーブル名
    protected $table = 'tb_user_account';

    // 変更可能なカラム
    protected $fillable = [
        'id',
        'user_code',
        'password',
        'user_password',
        'user_name',
        'user_login_id',
        'account_level',
        'base_id',
        'base_name',
        'last_logined',
        'remember_token',
        'bikou',
        'file_name',
        'comment',
        'img_uploaded_flg',
        'img_uploaded_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    
    ######################
    ## other
    ######################
    
    /**
     * 権限を動的にバインドする
     *
     */
    public function role() {
        return $this->hasOne( 'App\Models\Role', 'id', 'account_level' );
    }
    
    /**
     * 拠点を動的にバインドする
     *
     */
    public function base() {
        return $this->hasOne( 'App\Models\Base', 'id', 'base_id' );
    }

    /**
     * 担当者選択用のOptionをDBから取得する
     *
     */
    public static function options() {
        // 拠点長は表示しない
        //return UserAccount::whereNotIn( 'account_level', [6] )
        return UserAccount::orderBys( ['id' => 'asc'] )
            ->lists( 'user_name', 'user_code' );
    }

    /**
     * すべてユーザーの権限のDBから取得する
     */
    public static function allStaffPermision() {
        return UserAccount::orderBys( ['id' => 'asc'] )
            ->withTrashed()
            ->lists( 'account_level', 'id' );
    }

    /**
     * 担当者用のOptionをDBから取得する
     */
    public static function getStaffbyId($id) {
        return UserAccount::withTrashed()
            ->where('id', $id)
            ->firstOrFail();
    }


    /**
     * ユーザー名でDBから取得する
     */
    public static function getUserIdByName($baseCode, $userName) {
        return UserAccount::JoinBase()
            ->whereMatch( 'tb_base.base_code', $baseCode )
            ->withTrashed()
            ->whereRaw("REPLACE(REPLACE(user_name,'　',''),' ','') = '$userName'" )
            ->lists('tb_user_account.id' );
    }

    /**
     * 本店担当者コード用のOptionをDBから取得する
     */
    public static function headStaffOptions() {
        return UserAccount::JoinBase()
            ->whereMatch( 'tb_base.base_code', config('original.head_office_code') )
            ->orderBys( ['id' => 'asc'] )
            ->lists( 'user_name', 'user_code' );
    }

    /**
     * 本店担当者Id用のOptionをDBから取得する
     */
    public static function headStaffOptionsId() {
        return UserAccount::JoinBase()
//            ->whereMatch( 'tb_base.base_code', config('original.head_office_code') )
            ->whereIn('account_level', [1,2,3])
            ->orderBys( ['id' => 'asc'] )
            ->lists( 'user_name', 'tb_user_account.id' );
    }

    /**
     * 指定された拠点の担当者用のOptionをDBから取得する
     */
    public static function staffOptions( $base_code='' ) {
        return UserAccount::JoinBase()
                ->whereMatch( 'tb_base.base_code', $base_code )
                ->whereNotIn('user_code', ['mut']) // システムを除外
                ->orderBy( 'user_code' )
                ->lists( 'user_name', 'user_code' );
    }

    /**
     * 指定された拠点の担当者用のOptionをDBから取得する
     */
    public static function staffOptionsId( $base_code='' ) {
        return UserAccount::JoinBase()
            ->whereMatch( 'tb_base.base_code', $base_code )
            ->whereNotIn('user_code', ['mut']) // システムを除外
            ->orderBy( 'user_code' )
            ->lists( 'user_name', 'tb_user_account.id' );
    }

    /**
     * 指定された拠点の退職者用のOptionをDBから取得する
     */
    public static function staffOptionsDeleted( $base_code='' ) {
        return UserAccount::JoinBase()
            ->whereMatch( 'tb_base.base_code', $base_code )
            ->whereNotIn('user_code', ['mut']) // システムを除外
            ->includeDeleted( true ) // 削除データを含めるか
            ->orderBy( 'user_code' )
            ->lists( 'user_name', 'tb_user_account.id' );
    }

    /**
     * ユーザーIDの重複をチェックする
     * カスタムバリデーション用メソッド
     * @param unknown $value
     * @return boolean
     */
    public function unique( $value ) {
        $count = UserAccount::where( 'user_code', $value )
                            ->whereNull( $this->getTableName().'.'.$this->getDeletedAtColumn() )
                            ->count();

        return $count == 0;
    }

    /**
     * ユーザーログインIDの重複チェックをする
     * カスタムバリデーション用メソッド
     * @param unknown $value
     * @return boolean
     */
    public function unique_login_id( $value ) {
        $count = UserAccount::where( 'user_login_id', $value )
                            ->whereNull( $this->getTableName().'.'.$this->getDeletedAtColumn() )
                            ->count();

        return $count == 0;
    }
    
     /**
     * 半角チェックをする
     * カスタムバリデーション用メソッド
     * @param unknown $value
     * @return boolean
     */
    public function is_alnum( $value ) {
        if (preg_match("/^[a-zA-Z0-9]+$/",$value)) {
            return TRUE;
    	} else {
            return FALSE;
    	}
    }

    ###########################
    ## スコープメソッド(Join文)
    ###########################

    /**
     * 拠点とJoinするスコープメソッド
     *
     */
    public function scopeJoinBase( $query ) {
        $query->leftJoin(
            'tb_base',
            function( $join ) {
                $join->on( 'tb_user_account.base_id', '=', 'tb_base.id' )
                    ->whereNull( 'tb_base.deleted_at' );
            }
        );

        return $query;
    }
    
    /**
    * tb_user_accountをジョイン
    * ジョイン条件に検索条件を入れないとすべてたの担当者が出せない。
    */
    public function scopeJoinDmConfirm( $query, $shipping_ym='' ) {
//    public function scopeJoinDmConfirm( $query ) {
        $query->leftJoin(
            'tb_dm_confirm',
            function( $join ) use ( $shipping_ym ) {
//            function( $join ) {
                $join->on( 'tb_dm_confirm.user_code', '=', 'tb_user_account.user_code' )
                    ->where( 'tb_dm_confirm.inspection_ym', '=', $shipping_ym )
                    ->whereNull( 'tb_dm_confirm.deleted_at' );
            }
        );
        
        return $query;
    }

    /**
    * tb_user_accountをジョイン
    * ジョイン条件に検索条件を入れないとすべてたの担当者が出せない。
    */
    public function scopeJoinDmHanyouConfirm( $query ) {
        $query->leftJoin(
            'tb_customer_dm_confirm',
            function( $join ) {
                $join->on( 'tb_customer_dm_confirm.user_code', '=', 'tb_user_account.user_code' )
                    ->whereNull( 'tb_customer_dm_confirm.deleted_at' );
            }
        );
        
        return $query;
    }

    /**
    * tb_user_accountをジョイン
    * ジョイン条件に検索条件を入れないとすべてたの担当者が出せない。
    */
    public function scopeJoinDmEventConfirm( $query ) {
        $query->leftJoin(
            'tb_customer_event_dm_confirm',
            function( $join ) {
                $join->on( 'tb_customer_event_dm_confirm.user_code', '=', 'tb_user_account.user_code' )
                    ->whereNull( 'tb_customer_event_dm_confirm.deleted_at' );
            }
        );
        
        return $query;
    }

    ###########################
    ## スコープメソッド(条件式)
    ###########################
    
    /**
     * DMチェック確認の検索
     */
    public function scopeWhereDmCheck( $query, $value ) {
        if( !is_null( $value ) ) {
            if( CheckCodes::isNashi( $value ) ) {
                $query->whereNull( 'dm_confirm_flg' );

            } else if( CheckCodes::isAri( $value ) ) {
                $query->whereNotNull( 'dm_confirm_flg' );

            }
        }

        return $query;
    }
    
    ###########################
    ## User List Commands
    ###########################
    
    /**
     * 検索条件を指定するメソッド
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereRequest( $query, $requestObj, $user )
    {

        $login_base_id = null;
        $login_user_code = null;

        // 店長/工場長権限の場合
        if (in_array($user->getRolePriority(), [4, 5])) {
            $login_base_id = $user->getBaseId();
        } // 営業担当の時
        else if (in_array($user->getRolePriority(), [6])) {
            $login_base_id = $user->getBaseId();
            $login_user_code = $user->defaultSelectedUserCode();
        }

        // 検索条件を指定
        $query = $query
            ->whereLike('user_code', $requestObj->user_code)
            ->whereLike('user_code', $login_user_code)
            ->whereLike('user_name', $requestObj->user_name)
            ->whereMatch('tb_user_account.base_id', $requestObj->base_id)
            ->whereMatch('tb_user_account.base_id', $login_base_id)
            ->whereMatch('account_level', $requestObj->account_level)
            ->where('account_level', '>=', $user->getRolePriority())
            ->whereLike('bikou', $requestObj->bikou);
            //->whereUmuNull('tb_user_account.deleted_at', $requestObj->deleted_flg );
        // 削除済みの場合
        if ($requestObj->deleted_flg == '1') {
            $query = $query ->withTrashed()
                            ->whereUmuNull('tb_user_account.deleted_at', '1' );
        }
        return $query;
    }

    ###########################
    ## DmConfirm List Commands
    ###########################

    /**
     * 検索条件を指定するメソッド
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereDmConfirmRequest( $query, $requestObj ){
        // 検索条件を指定
        $query = $query
            ->whereMatch( 'tb_base.id', $requestObj->base_code )
            ->whereLike( 'tb_user_account.user_code', $requestObj->user_code )
//            ->where( 'tb_dm_confirm.inspection_ym',$requestObj->tgc_shipping_ym )
            ->whereDmCheck( $requestObj->dm_confirm_flg );

        return $query;
    }
    
}