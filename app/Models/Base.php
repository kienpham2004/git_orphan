<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 拠点モデル
 *
 * @author yhatsutori
 *
 */
class Base extends AbstractModel {
    
    use SoftDeletes;

    // 本店を表す拠点コード
    // const HEAD_OFFICE = '90';

    // テーブル名
    protected $table = 'tb_base';

    // 変更可能なカラム
    protected $fillable = [
        'base_id',
        'base_code',
        'base_name',
        'base_short_name',
        'work_level',
        'block_base_code'
    ];

    ######################
    ## other
    ######################

    /**
     * Topのグラフで使用
     * @return [type] [description]
     */
    public static function options() {
        return Base::orderBys( ['base_code' => 'asc'] )
                   ->lists( 'base_short_name', 'base_code' );
    }

    /**
     * Topのグラフで使用
     * @return [type] [description]
     */
    public static function optionsId() {
        return Base::orderBys( ['base_code' => 'asc'] )
            ->lists( 'base_short_name', 'id' );
    }

    public static function getBaseNameByCode( $base_code='' ) {
        return Base::whereMatch( 'base_code', $base_code )
            ->lists( 'base_name');
    }

    /**
     * 拠点コードの重複を確認する
     * ・呼出元はカスタムバリデーション
     *
     * @param unknown $query
     * @param unknown $value
     */
    public function unique( $value ) {
    	//0埋め処理
		$work = trim($value);
		if(mb_strlen($work) == 1){
		$work = "0".$work;
		$value = $work;
		}
		
        $count = Base::where( 'base_code', $value )
                     ->whereNull( $this->getTableName().'.'.$this->getDeletedAtColumn() )
                     ->count();
                     
        return $count == 0;
    }

    ###########################
    ## Base List Commands
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
            ->whereMatch( 'base_code', $requestObj->base_code )
            ->whereLike( 'base_name', $requestObj->base_name )
            ->whereMatch( 'work_level', $requestObj->work_level )
            ->whereMatch( 'block_base_code', $requestObj->block_base_code )
            ->whereMatch( 'id', $requestObj->base_id )
            ->includeDeleted( "1" ); // 削除データを含めるか
            //->includeDeleted( $requestObj->is_delete ); // 削除データを含めるか

        return $query;
    }

}
