<?php

namespace App\Models\DmHanyou;

use App\Lib\Codes\CheckCodes;
use App\Models\AbstractModel;
use Log;
use DB;

/**
 * 顧客データに関するモデル
 */
class CustomerDm extends AbstractModel {
    // テーブル名
    protected $table = 'tb_customer_dm_1';

    // 変更可能なカラム
    protected $fillable = [
        "inspection_ym",
        "car_manage_number",
        "base_code",
        "base_name",
        "user_id",
        "user_name",
        "customer_code",
        "customer_name_kanji",
        "gensen_code_name",
        "customer_category_code_name",
        "customer_postal_code",
        "customer_address",
        "customer_tel",
        "customer_office_tel",
        "car_name",
        "car_year_type",
        "first_regist_date_ym",
        "cust_reg_date",
        "car_base_number",
        "car_model",
        "car_service_code",
        "car_frame_number",
        "car_buy_type",
        "car_new_old_kbn_name",
        "syaken_times",
        "syaken_next_date",
        "customer_insurance_type",
        "customer_insurance_company",
        "customer_insurance_end_date",
        "customer_kouryaku_flg",
        "customer_dm_flg",
        "customer_name_kata",
        "credit_hensaihouhou",
        "credit_hensaihouhou_name",
        "first_shiharai_date_ym",
        "keisan_housiki_kbn",
        "credit_card_select_kbn",
        "memo_syubetsu",
        "shiharai_count",
        "sueoki_zankaritsu",
        "last_shiharaikin",
        "customer_kojin_hojin_flg",
        "original_dm_flg",
        "original_dm_unnecessary_reason",
        "original_car_new_old_flg",
        "created_at",
        "updated_at",
        "deleted_at",
        "created_by",
        "updated_by"
    ];

    ###########################
    ## スコープメソッド(Join文)
    ###########################
    
    /**
     * 拠点テーブルのJOIN
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function scopeJoinBase( $query ) {
        $query = $query->leftJoin(
                    "tb_base",
                    function( $join ){
                        $join->on( 'tb_customer_dm_1.base_code', '=', 'tb_base.base_code' )
                             ->whereNull( 'tb_base.deleted_at' );
                    }
                );

        //dd( $query->toSql() );
        return $query;

    }
    
    /**
     * 担当者テーブルのJOIN
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function scopeJoinSales( $query ) {
        $query = $query->leftJoin(
                    'tb_user_account',
                    function( $join ){
                        $join->on( 'tb_customer_dm_1.user_id', '=', 'tb_user_account.user_id' )
                             ->whereNull( 'tb_user_account.deleted_at' );
                    }
                );

        //dd( $query->toSql() );
        return $query;
    }

    /**
     * チャオテーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinCiao( $query ){
//        $query = $query->leftJoin(
//                    'v_ciao',
//                    function( $join ) {
//                    $join->on( 'tb_customer_dm_1' . '.customer_code', '=', 'v_ciao.ciao_customer_code' )
//                        ->on( 'tb_customer_dm_1' . '.car_manage_number', '=', 'v_ciao.ciao_car_manage_number' )
//                        ->on( 'syaken_next_date', '<=', "v_ciao.ciao_end_date" )
//                        ->wherenull( 'v_ciao.deleted_at' );
//                });
        $query = $query->leftJoin(
            'tb_target_cars',
            function( $join ) {
                $join->on( 'tb_customer_dm_1' . '.customer_code', '=', 'tb_target_cars.tgc_customer_code' )
                    ->on( 'tb_customer_dm_1' . '.car_manage_number', '=', 'tb_target_cars.tgc_car_manage_number' )
                    ->on( 'syaken_next_date', '<=', "tb_target_cars.tgc_ciao_end_date" )
                    ->wherenull( 'tb_target_cars.deleted_at' );
            });

        //dd( $query->toSql() );
        return $query;
    }

    ###########################
    ## スコープメソッド(条件式)
    ###########################
    
    /**
     * 不要チェックの有り無し
     * @param  [type] $query [description]
     * @param  string $value 検索するDMフラグ
     * @return $query
     */
    public function scopeWhereCustomerDmFlg( $query, $value='' ) {
        if ( !is_null( $value ) && is_array( $value ) ) {
            $query->where( function( $query ) use ( $value ) {
                foreach ( $value as $k => $v ) {
                    if ( CheckCodes::isNashi( $v ) ) {
                        $query->orWhereNull( 'tb_customer_dm_1.original_dm_flg' );
                    } else if ( CheckCodes::isAri( $v ) ) {
                        $query->orWhere( 'tb_customer_dm_1.original_dm_flg', '=', '1' );
                    }
                }
            });
        }
        return $query;
    }

    /**
     * 車検回数で検索
     * @param  [type] $query [description]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function scopeWhereSyakenTimesFlg( $query, $value='' ){
        if( $value == "1" ){
            $query->where( 'syaken_times', '=', '1' );

        }elseif( $value == "2" ){
            $query->where( 'syaken_times', '>', '1' );

        }elseif( $value == "3" ){
            $query->whereNull( 'syaken_times' );

        }

        return $query;
    }

    /**
     * 新車/中古車区分
     * @param  [type] $query [description]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function scopeWhereNewOldKbn( $query, $value='' ){        
        if( $value == "1" ){
            $query->where( 'car_new_old_kbn_name', '=', '新車' );

        }elseif( $value == "2" ){
            $query->where( 'car_new_old_kbn_name', '=', '中古車' )
                  ->whereNotNull( 'syaken_times' );

        }elseif( $value == "3" ){
            $query->whereNull( 'syaken_times' );
        }

        return $query;
    }

    ###########################
    ## Customer List Commands
    ###########################
        
    /**
     * 検索条件を指定するメソッド
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereRequest( $query, $requestObj ){
        $query = $query
            // 統合車両管理NO
            ->whereLike( 'car_manage_number', $requestObj->car_manage_number )
            // 拠点コード
            ->whereLike( 'tb_customer_dm_1.base_code', $requestObj->base_code )
            // 担当者コード
            ->whereLike( 'tb_customer_dm_1.user_id', $requestObj->user_id )
            // 顧客コード
            ->whereLike( 'customer_code', $requestObj->customer_code )
            // 住所
            ->whereLike( 'customer_address', $requestObj->customer_address )
            // 車両No
            ->whereLike( 'car_base_number', $requestObj->car_base_number )
            // 車検回数
            ->whereSyakenTimesFlg( $requestObj->syaken_times )
            // 新車/中古車区分
            ->whereNewOldKbn( $requestObj->car_new_old_kbn_name )
            // チャオ
            //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
            ->whereUmuNullCheckbox( 'tb_target_cars.tgc_ciao_course', $requestObj->ciao )
            // 抽出用のときの条件式
            ->whereCustomerDmFlg( $requestObj->customer_dm_flg );

        return $query;
    }

    /**
     * 検索条件を指定するメソッド
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereMutRequest( $query, $requestObj ){
        $query = $query
            // DM不要でないもの
            ->whereNull( 'original_dm_flg' )
            // 統合車両管理NO
            ->whereLike( 'car_manage_number', $requestObj->car_manage_number )
            // 拠点コード
            ->whereLike( 'tb_customer_dm_1.base_code', $requestObj->base_code )
            // 担当者コード
            ->whereLike( 'tb_customer_dm_1.user_id', $requestObj->user_id )
            // 顧客コード
            ->whereLike( 'customer_code', $requestObj->customer_code )
            // 住所
            ->whereLike( 'customer_address', $requestObj->customer_address )
            // 車両No
            ->whereLike( 'car_base_number', $requestObj->car_base_number )
            // 車検回数
            ->whereSyakenTimesFlg( $requestObj->syaken_times )
            // 新車/中古車区分
            ->whereNewOldKbn( $requestObj->car_new_old_kbn_name );

        return $query;
    }

}
