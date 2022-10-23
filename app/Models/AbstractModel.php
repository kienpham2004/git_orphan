<?php

namespace App\Models;

use App\Lib\Util\QueryUtil;
use App\Lib\Codes\DeleteCodes;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelクラスを継承したクラス
 * 他のモデルクラスは、基本的にこのクラスを継承する
 */
class AbstractModel extends Model {

    /**
     * サブクラスで使用するテーブル名を取得する
     * @return サブクラスのテーブル名
     */
    public static function getTableName() {
        return ( new static )->getTable();
    }
    
    /**
     * 指定されているidが空かどうかを調べる
     * @return boolean [description]
     */
    public function isNew() {
        return empty( $this->id );
    }

    /**
     * 更新者を動的にバインド
     */
    public function updator() {
        return $this->hasOne( 'App\Models\UserAccount', 'id', 'updated_by' );
    }

    ###########################
    ## スコープメソッド(条件式)
    ###########################
    
    /**
     * 値が合致するかの条件文を追加
     * @param  [type] $query [description]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function scopeWhereMatch( $query, $key, $value ){
        if( !empty( $value ) ) {
            $query->where( $key, $value );
        }
        return $query;
    }    
    
    /**
     * 値が一つと複数が混在している場合の条件文を追加
     * @param  [type] $query [description]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]  ※複数の場合：a_b_c → [a,b,c]
     * @return [type]        [description]
     */
    public function scopeWhereMatchIn( $query, $key, $value ) {
        if( !empty( $value ) ) {
            // 複数の場合は _ で連結してある（a_b_c）
            if( preg_match("/_/", $value ) ){
                // 配列に変換（[a,b,c]）
                $res = explode( '_', $value );
                $query->whereIn( $key, $res );
            }
            // tgc_status 検索用
            // 未確定は20 or null ※201810
            elseif ( $value == 'n' ) {
                $query->where(function ( $query ) use ( $key ) {
                    // tgc_statusの新意向
                    $n = '20';
                    // 新意向以外 or NULLを判定
                    $query->where( $key,$n )
                            ->orWhereNull( $key );
                });
            }
//            elseif ( $value == 'n' ) {
//                $query->where(function ( $query ) use ( $key ) {
//                    // tgc_statusの新意向
//                    $n_arr = [
//                        '11','12','13','14','15','16','17','18'
//                    ];
//                    // 新意向以外 or NULLを判定
//                    $query->whereNotIn( $key,$n_arr )
//                            ->orWhereNull( $key );
//                });
//            }
            // 検索する値が一つの場合
            else{
                $query->where( $key, $value );
            }
        }
        return $query;
    }
    
    /**
     * Likeの条件文を追加
     * @param  [type] $query [description]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function scopeWhereLike( $query, $key, $value ){
        if( !empty( $value ) ) {
            $query->where( $key, 'like', '%'.$value.'%' );
        }
        return $query;
    }

    /**
     * 期間の条件文を追加
     * @param  [type] $query [description]
     * @param  [type] $key   [description]
     * @param  [type] $from  [description]
     * @param  [type] $to    [description]
     * @return [type]        [description]
     */
    public function scopeWherePeriod( $query, $key, $from, $to ){
        if( !empty( $from ) ) {
            // 処理が速くなるので、同じ値の時は一つにする
            if( $from == $to ){
                $query = $query->where( $key, '=', $from );
            }else{
                $query = QueryUtil::between( $query, $key, $from, $to );
            }
        }
        return $query;
    }

    /**
     * 期間の条件文を追加
     * @param  [type] $query [description]
     * @param  [type] $key   [description]
     * @param  [type] $from  [description]
     * @param  [type] $to    [description]
     * @return [type]        [description]
     */
    public function scopeWherePeriodNormal( $query, $key, $from, $to ){
        if( !empty( $from ) ) {
            // 処理が速くなるので、同じ値の時は一つにする
            if( $from == $to ){
                $query = $query->where( $key, '=', $from );
            }else{
                $query = QueryUtil::betweenNormal( $query, $key, $from, $to );
            }
        }
        return $query;
    }

    /**
     * 有無の判定の条件文を追加
     * @param  [type] $query [description]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function scopeWhereUmuNull( $query, $key, $value ){
        if( !empty( $value ) ) {
            if ( $value === '1' ) {
                $query->whereNotNull( $key );
                
            } elseif ( $value === '0' ) {
                $query->whereNull( $key );
                
            }
        }
        return $query;
    }
    
    /**
     * チェックボックスで有無の判定をする時のスコープメソッド
     * @param  object $query QueryBuilder
     * @param  string $key   指定のカラム
     * @param  array $value 検索した値
     * @return object        QueryBuilder
     */
    public function scopeWhereAbcCheckbox($query, $key, $value)
    {
        //$s = microtime(true);
        if (is_array($value)) {
            $query->where(function ($query) use ($key, $value) {
                foreach ($value as $v) {
                    if ( in_array( $v, ['A', 'B', 'C'] ) ) {
                        $query->orWhere( $key, $v );
                    }
                }
            });
        }

        //dd(microtime(true) - $s);
        
        return $query;
    }

    /**
     * チェックボックスで有無の判定をする時のスコープメソッド
     * @param  object $query QueryBuilder
     * @param  string $key   指定のカラム
     * @param  array $value 検索した値
     * @return object        QueryBuilder
     */
    public function scopeWhereUmuNullCheckbox($query, $key, $value)
    {
        //$s = microtime(true);
        if (is_array($value)) {
            $query->where(function ($query) use ($key, $value) {
                foreach ($value as $v) {
                    if ($v === '0') {
                        $query->orWhereNull($key);
                    } elseif ($v === '1') {
                        $query->orWhereNotNull($key);
                    }
                }
            });
        }

        return $query;
    }

    /**
     * チェックボックスで有無の判定をする時のスコープメソッド
     * @param  object $query QueryBuilder
     * @param  array $value 検索した値
     * @return object        QueryBuilder
     */
    public function scopeWhereCiaoCheck($query, $value)
    {
        if (is_array($value)){
//            // 両方にチェック
//            if( in_array("0", $value) && in_array("1", $value) ){
//                $queryCiao = "(tgc_ciao_course is null OR tgc_ciao_course is not null)  ";
//            }
//            // 無にチェック
//            elseif( in_array("0", $value) ){
//                $queryCiao = "(tgc_ciao_course is null OR ";
//                //ciao_course == 'SS'　の場合
//                //　・syaken_next_date < ciao_end_date　→　有り
//                $queryCiao .= "((tgc_ciao_course = 'SS' AND tgc_syaken_next_date >= tgc_ciao_end_date) OR ";
//                //ciao_course != 'SS'　の場合
//                //　・syaken_next_date <= ciao_end_date　→　有り
//                 $queryCiao .= "(tgc_ciao_course is not null AND tgc_ciao_course != 'SS' AND tgc_syaken_next_date > tgc_ciao_end_date))) ";
//            }
//            // 有にチェック
//            elseif( in_array("1", $value) ){
//                $queryCiao = "tgc_ciao_course is not null AND ";
//                //ciao_course == 'SS'　の場合
//                //　・syaken_next_date < ciao_end_date　→　有り
//                $queryCiao .= "((tgc_ciao_course = 'SS' AND tgc_syaken_next_date < tgc_ciao_end_date) OR ";
//                //ciao_course != 'SS'　の場合
//                //　・syaken_next_date <= ciao_end_date　→　有り
//                $queryCiao .= "(tgc_ciao_course != 'SS' AND tgc_syaken_next_date <= tgc_ciao_end_date)) ";
//            }

            // 両方にチェック
            if( in_array("0", $value) && in_array("1", $value) ){
                $queryCiao = "";
            }
            // 無にチェック
            elseif( in_array("0", $value) ){
                $queryCiao = "tgc_ciao_course is null ";
            }
            // 有にチェック
            elseif( in_array("1", $value) ){
                $queryCiao = "tgc_ciao_course is not null ";
            }
            if ($queryCiao != "") {
                $query = $query->whereRaw($queryCiao);
            }
            return $query;
        }
    }

    /**
     * チェックボックスで有無の判定をする時のスコープメソッド
     * @param  object $query QueryBuilder
     * @param  string $key 項目
     * @param  array $value 有無配列
     * @param  string $check 比較値
     * @return object        QueryBuilder
     */
    public function scopeWhereCheckValue($query, $key, $value, $check)
    {
        if (is_array($value)){
            // 両方にチェック
            if( in_array("0", $value) && in_array("1", $value) ){
                $querySql = "";
            }
            // 無にチェック
            elseif( in_array("0", $value) ){
                $querySql = " ( $key is null OR $key != '".$check."') ";
            }
            // 有にチェック
            elseif( in_array("1", $value) ){
                $querySql = " $key = '".$check."' ";
            }
            if ($querySql != "") {
                $query = $query->whereRaw($querySql);
            }
        }
        return $query;
    }

    /**
     * チェックボックスで有無の判定をする時のスコープメソッド
     * @param  object $query QueryBuilder
     * @param  string $key 項目
     * @param  array $value 有無配列
     * @param  array $check 比較値
     * @return object        QueryBuilder
     */
    public function scopeWhereCheckValueIn($query, $key, $value, $check)
    {
        if (is_array($value)){
            $sql = '';
            foreach ( $check as $k => $v ){
                if($k ==0){
                    $sql = $sql . "'" . $v . "'";
                }else {
                    $sql = $sql . ",'" . $v . "'";
                }
            }
            // 両方にチェック
            if( in_array("0", $value) && in_array("1", $value) ){
                $querySql = "";
            }
            // 無にチェック
            elseif( in_array("0", $value) ){
                $querySql = "($key is null or  $key not in (".$sql.") )";
            }
            // 有にチェック
            elseif( in_array("1", $value) ){
                $querySql = " $key in (".$sql.") ";
            }
            if ($querySql != "") {
                $query = $query->whereRaw($querySql);
            }
        }
        return $query;
    }

    /**
     * チェックボックスで有無の判定をする時のスコープメソッド
     * @param  object $query QueryBuilder
     * @param  string $key 項目
     * @param  array $value 有無配列
     * @param  string $check 比較値
     * @return object        QueryBuilder
     */
    public function scopeWhereCheckNotValue($query, $key, $value, $check)
    {
        if (is_array($value)){
            // 両方にチェック
            if( in_array("0", $value) && in_array("1", $value) ){
                $querySql = "";
            }
            // 無にチェック
            elseif( in_array("1", $value) ){
                $querySql = " ( $key is not null AND $key != '".$check."') ";
            }
            // 有にチェック
            elseif( in_array("0", $value) ){
                $querySql = " $key is null ";
            }
            if ($querySql != "") {
                $query = $query->whereRaw($querySql);
            }
        }
        return $query;
    }

    /**
     * チェックボックスで有無の判定をする時のスコープメソッド
     * @param  object $query QueryBuilder
     * @param  string $key 項目
     * @param  array $value 有無配列
     * @return object        QueryBuilder
     */
    public function scopeWhereCheckValueNull($query, $key, $value)
    {
        if (is_array($value)){
            // 両方にチェック
            if( in_array("0", $value) && in_array("1", $value) ){
                $querySql = "";
            }
            // 無にチェック
            elseif( in_array("0", $value) ){
                $querySql = " ( $key is null ) ";
            }
            // 有にチェック
            elseif( in_array("1", $value) ){
                $querySql = " ( $key is not null ) ";
            }
            if ($querySql != "") {
                $query = $query->whereRaw($querySql);
            }
        }
        return $query;
    }

    /**
     * チェックボックスで有無の判定をする時のスコープメソッド（検索値が複数のケース）
     * @param  object $query QueryBuilder
     * @param  string $key 項目
     * @param  array $value 有無配列
     * @param  string $check 比較値
     * @return object $query
     */    
    public function scopeWhereCheckMulti($query, $key, $value, $check)
    {
        if (is_array($value)){
            // 両方にチェック
            if( in_array("0", $value) && in_array("1", $value) ){
                $querySql = "";
            }
            // 無にチェック
            elseif( in_array("0", $value) ){
                
                // 複数の場合は _ で連結してある（a_b_c）
                if( preg_match("/_/", $check ) ){
                    // 配列に変換（[a,b,c]）
                    $res = explode( '_', $check );
                    
                    $query->where(function($query) use ($key, $res) {
                        $query->whereNotIn( $key, $res )
                                ->orWhereNull( $key );
                    });
                }
                // 検索する値が一つの場合
                else{
                    $query->where( $key, $check );
                }
            }
            // 有にチェック
            elseif( in_array("1", $value) ){
                
                // 複数の場合は _ で連結してある（a_b_c）
                if( preg_match("/_/", $check ) ){
                    // 配列に変換（[a,b,c]）
                    $res = explode( '_', $check );
                    $query->whereIn( $key, $res );
                }
                // 検索する値が一つの場合
                else{
                    $query->where( $key, $check );
                }
            }
        }
        return $query;
    }

    /**
     * 取得するデータに削除データを含めるかどうかのメソッド
     * @param  query $query それまでのクエリ
     * @param  [type] $value [description]
     * @return query
     */
    public function scopeIncludeDeleted( $query, $value ) {
        if( !empty( $value ) ) {
            // 削除データのみを対象
            if( DeleteCodes::isDelete( $value ) ) {
                $query->onlyTrashed();

            }else if( $value == "1" ){
                // 削除されていないものを表示
                $query->withTrashed()
                      ->whereNull( $this->getTableName().'.'.$this->getDeletedAtColumn() );

            // 両方を対象
            } else {
                $query->withTrashed();
                
            }
            return $query;
        }
    }

    ###########################
    ## スコープメソッド(並び替え)
    ###########################
    
    /**
     * 複数のorder byを指定するメソッド
     * @param  [type] $query  [description]
     * @param  [type] $orders [description]
     * @return [type]         [description]
     */
    public static function scopeOrderBys( $query, $orders ) {
        if( !empty( $orders ) ) {
            foreach ( $orders as $key => $value ) {
                $query->orderBy( $key, $value );
            }
        }
        return $query;
    }

    /**
     * チェックボックスで有無の判定をする時のスコープメソッド
     * @param object $query QueryBuilder
     * @param string $key 指定のカラム
     * @param array $value 検索した値
     * @return object        QueryBuilder
     */
    public function scopeWhereUmuCheckbox($query, $key, $value) {
        if (!is_null($value) && is_array($value)) {
            $query->where(function ($query) use ($key, $value) {
                foreach ($value as $k => $v) {
                    if ($v == '0') {
                        $query->orWhere($key, '=', '0');
                    } else if ($v == '1') {
                        $query->where($key, '=', '1');
                    }
                }
            });
        }
        return $query;
    }
}
