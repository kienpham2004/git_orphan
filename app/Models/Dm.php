<?php

namespace App\Models;
use App\Lib\CsvData\tCsvImport;

use DB;

/**
 * DM送付データのみを抽出したモデル
 */
class Dm extends AbstractModel {
    // テーブル名
    protected $table = 'tb_dm';

    // 変更可能なカラム
    protected $fillable = [
        'dm_inspection_id',
        'dm_inspection_ym',
        'dm_customer_id',
        'dm_car_manage_number',
        'dm_base_code',
        'dm_base_name',
        'dm_user_id',
        'dm_user_name',
        'dm_customer_code',
        'dm_customer_name_kanji',
        'dm_gensen_code_name',
        'dm_customer_category_code_name',
        'dm_customer_postal_code',
        'dm_customer_address',
        'dm_customer_tel',
        'dm_customer_office_tel',
        'dm_car_name',
        'dm_car_year_type',
        'dm_first_regist_date_ym',
        'dm_cust_reg_date',
        'dm_car_base_number',
        'dm_car_model',
        'dm_car_service_code',
        'dm_car_frame_number',
        'dm_car_buy_type',
        'dm_car_new_old_kbn_name',
        'dm_syaken_times',
        'dm_syaken_next_date',
        'dm_customer_insurance_type',
        'dm_customer_insurance_company',
        'dm_customer_insurance_end_date',
        'dm_customer_kouryaku_flg',
        'dm_customer_dm_flg',
        'dm_customer_kojin_hojin_flg',
        'created_by',
        'updated_by',
        'dm_car_type',
        'dm_abc_zone',
        'dm_htc_number',
        'dm_htc_car',
        'dm_status'
    ];
    
    // 日付のカラム
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    ###########################
    ## 抽出の処理
    ###########################
    
    /**
     * CSV取り込み時のマージ処理
     *
     * @param unknown $values 取り込みデータの行
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

        // 統合車両管理Noと接触月が同じものは上書き
        Dm::updateOrCreate(
            [
                'dm_inspection_id' => $fieldData['dm_inspection_id'],
                'dm_inspection_ym' => $fieldData['dm_inspection_ym'],
                'dm_car_manage_number' => $fieldData['dm_car_manage_number']
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
            \Log::debug( $ex );
            // $filter->contains()の戻り値はboolianなので、
            // emptyではちょっと違和感を覚えました。
            // if (! $ex) {} でもいいように思います
            if( empty( $ex ) ) {
                // DM用のカラム名を取得
                $setKey = str_replace( "tgc_", "dm_", $key );
                // 値の指定
                $result[$setKey] = $value;
            }
        }
        
        return $result;
    }

    ###########################
    ## スコープメソッド(Join文)
    ###########################

    /**
     * 拠点テーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinBase( $query ) {
        $query = $query->leftJoin(
                    'tb_base',
                    function( $join ){
                        $join->on( 'tb_dm.dm_base_code', '=', 'tb_base.base_code' )
                             ->whereNull( 'tb_base' . '.deleted_at' );
                    }
                );

        //dd( $query->toSql() );
        return $query;
    }

    /**
     * 担当者テーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinSales( $query ) {
        $query = $query->leftJoin(
                    'tb_user_account',
                    function( $join ){
                        $join->on( 'tb_dm'.'.dm_user_id', '=', 'tb_user_account' . '.user_id' )
                        ->whereNull( 'tb_user_account' . '.deleted_at' );
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
//                    $join->on( 'tb_dm' . '.dm_customer_code', '=', 'v_ciao.ciao_customer_code' )
//                        ->on( 'tb_dm' . '.dm_car_manage_number', '=', 'v_ciao.ciao_car_manage_number' )
//                        ->on( 'tb_dm' . '.dm_inspection_ym', '<=', DB::raw("to_char( v_ciao.ciao_end_date, 'yyyymm' )") )
//                        ->wherenull( 'v_ciao.deleted_at' );
//                });
        $query = $query->leftJoin(
            'tb_target_cars',
            function( $join ) {
                $join->on( 'tb_dm' . '.dm_customer_code', '=', 'tb_target_cars.tgc_customer_code' )
                    ->on( 'tb_dm' . '.dm_car_manage_number', '=', 'tb_target_cars.tgc_car_manage_number' )
                    ->on( 'tb_dm' . '.dm_inspection_ym', '<=', DB::raw("to_char( tb_target_cars.tgc_ciao_end_date, 'yyyymm' )") )
                    ->wherenull( 'tb_target_cars.deleted_at' );
            });

        //dd( $query->toSql() );
        return $query;
    }
    
    /**
     * tb_manage_infoとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinInfo( $query ) {
        $query = $query->leftJoin(
                    'tb_manage_info',
                    function( $join ) {
                    $join->on( 'tb_dm.dm_car_manage_number', '=', 'tb_manage_info.mi_car_manage_number' )
                         ->on( 'tb_dm.dm_inspection_id', '=', 'tb_manage_info.mi_inspection_id' )
                         ->on( 'tb_dm.dm_inspection_ym', '=', 'tb_manage_info.mi_inspection_ym' );
                });

        //dd( $query->toSql() );
        return $query;
    }
    
    /**
     * 顧客テーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinCustomer( $query ){
        $query = $query->leftJoin(
                    'tb_customer',
                    function( $join ) {
                    $join->on( 'tb_dm.dm_car_manage_number', '=', 'tb_customer.car_manage_number' )
                        ->wherenull( 'tb_customer.deleted_at' );
                });

        //dd( $query->toSql() );
        return $query;
    }


    ###########################
    ## Dm List Commands
    ###########################
    
    /**
     * 検索条件を指定するメソッド
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereDmRequest( $query, $requestObj ){
        
//        $query = $query
//            // 統合車両管理NO
//            ->whereLike( 'dm_car_manage_number', $requestObj->dm_car_manage_number )
//            // 拠点コード
//            ->whereLike( 'dm_base_code', $requestObj->base_code )
//            // 担当者コード
//            ->whereLike( 'dm_user_id', $requestObj->user_id )
//            // 担当者名の有無
//            ->whereUmuNullCheckbox( 'tb_user_account.user_id', $requestObj->user_id_flg )
//            // 顧客コード
//            ->whereLike( 'dm_customer_code', $requestObj->dm_customer_code )
//            // 住所
//            ->whereLike( 'dm_customer_address', $requestObj->dm_customer_address )
//            // チャオ
//            ->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
//            // 抽出用のときの条件式
//            //->whereCustomerDmFlg( $requestObj->customer_dm_flg )
//            //予約済みを除く
//            ->where(function( $query ){ 
//                $query
//                    ->whereNotIn('tb_manage_info.mi_rstc_reserve_status', ['予約確定（承認）'] )
//                    ->orWhereNull( 'tb_manage_info.mi_rstc_reserve_status' );
//            });
            
        // 車点検区分が選択された場合
        if( !empty( $requestObj->dm_inspection_id ) ){
            //
            if( $requestObj->dm_inspection_id != 4 ){ // 点検の時
                $query = $query
                    ->where(function( $query ) use ( $requestObj ){ 
                        $query
                            ->where( 'dm_inspection_ym', $requestObj->dm_shipping_ym )
                            ->where( 'dm_inspection_id', $requestObj->dm_inspection_id );
                    })
                    // 統合車両管理NO
                    ->whereLike( 'dm_car_manage_number', $requestObj->dm_car_manage_number )
                    // 拠点コード
                    ->whereLike( 'dm_base_code', $requestObj->base_code )
                    // 担当者コード
                    ->whereLike( 'dm_user_id', $requestObj->user_id )
                    // 担当者名の有無
                    ->whereUmuNullCheckbox( 'tb_user_account.user_id', $requestObj->user_id_flg )
                    // 顧客コード
                    ->whereLike( 'dm_customer_code', $requestObj->dm_customer_code )
                    // 住所
                    ->whereLike( 'dm_customer_address', $requestObj->dm_customer_address )
                    // チャオ
                    //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
                    ->whereUmuNullCheckbox( 'tb_target_cars.tgc_ciao_course', $requestObj->ciao )
                    // Nシリーズ
                    ->whereMatchIn( 'dm_car_name', $requestObj->dm_car_name )
                    //意向結果
                    ->whereMatch( 'dm_status', $requestObj->dm_action_code )
                    //予約済みを除く
                    ->where(function( $query ){ 
                        $query
                            ->whereNotIn('tb_manage_info.mi_rstc_reserve_status', ['予約確定（承認）'] )
                            ->orWhereNull( 'tb_manage_info.mi_rstc_reserve_status' );
                    });
                    
            }elseif( $requestObj->dm_inspection_id == 4 ){ // 早期入庫（車検）の時
                $query = $query
                    ->where(function( $query ) use ( $requestObj ){
                        $query
                            ->where( 'dm_inspection_ym', date( "Ym", strtotime( $requestObj->dm_shipping_ym . "01" .'+1 month' ) ) )
                            ->where( 'dm_inspection_id', $requestObj->dm_inspection_id );
                    })
                    // 統合車両管理NO
                    ->whereLike( 'dm_car_manage_number', $requestObj->dm_car_manage_number )
                    // 拠点コード
                    ->whereLike( 'dm_base_code', $requestObj->base_code )
                    // 担当者コード
                    ->whereLike( 'dm_user_id', $requestObj->user_id )
                    // 担当者名の有無
                    ->whereUmuNullCheckbox( 'tb_user_account.user_id', $requestObj->user_id_flg )
                    // 顧客コード
                    ->whereLike( 'dm_customer_code', $requestObj->dm_customer_code )
                    // 住所
                    ->whereLike( 'dm_customer_address', $requestObj->dm_customer_address )
                    // チャオ
                    //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
                    ->whereUmuNullCheckbox( 'tb_target_cars.tgc_ciao_course', $requestObj->ciao )
                    // Nシリーズ
                    ->whereMatchIn( 'dm_car_name', $requestObj->dm_car_name )
                    //意向結果
                    ->whereMatch( 'dm_status', $requestObj->dm_action_code )
                    //予約済みを除く
                    ->where(function( $query ){ 
                        $query
                            ->whereNotIn('tb_manage_info.mi_rstc_reserve_status', ['予約確定（承認）'] )
                            ->orWhereNull( 'tb_manage_info.mi_rstc_reserve_status' );
                    });
            }
            
        // 未選択時
        }else{
            $query = $query
                ->where(function( $query ) use ( $requestObj ){
                    $query
                        ->where( 'dm_inspection_ym', date( "Ym", strtotime( $requestObj->dm_shipping_ym . "01" .'+1 month' ) ) )
                        ->where( 'dm_inspection_id', 4 )
                        // 統合車両管理NO
                        ->whereLike( 'dm_car_manage_number', $requestObj->dm_car_manage_number )
                        // 拠点コード
                        ->whereLike( 'dm_base_code', $requestObj->base_code )
                        // 担当者コード
                        ->whereLike( 'dm_user_id', $requestObj->user_id )
                        // 担当者名の有無
                        ->whereUmuNullCheckbox( 'tb_user_account.user_id', $requestObj->user_id_flg )
                        // 顧客コード
                        ->whereLike( 'dm_customer_code', $requestObj->dm_customer_code )
                        // 住所
                        ->whereLike( 'dm_customer_address', $requestObj->dm_customer_address )
                        // チャオ
                        //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
                        ->whereUmuNullCheckbox( 'tb_target_cars.tgc_ciao_course', $requestObj->ciao )
                        // Nシリーズ
                        ->whereMatchIn( 'dm_car_name', $requestObj->dm_car_name )
                        //意向結果
                        ->whereMatch( 'dm_status', $requestObj->dm_action_code )
                        //予約済みを除く
                        ->where(function( $query ){ 
                            $query
                                ->whereNotIn('tb_manage_info.mi_rstc_reserve_status', ['予約確定（承認）'] )
                                ->orWhereNull( 'tb_manage_info.mi_rstc_reserve_status' );
                        });
                })
                ->orWhere(function( $query ) use ( $requestObj ){ 
                    $query
                        ->where( 'dm_inspection_ym', $requestObj->dm_shipping_ym )
                        ->whereNotIn( 'dm_inspection_id', [4] )
                        // 統合車両管理NO
                        ->whereLike( 'dm_car_manage_number', $requestObj->dm_car_manage_number )
                        // 拠点コード
                        ->whereLike( 'dm_base_code', $requestObj->base_code )
                        // 担当者コード
                        ->whereLike( 'dm_user_id', $requestObj->user_id )
                        // 担当者名の有無
                        ->whereUmuNullCheckbox( 'tb_user_account.user_id', $requestObj->user_id_flg )
                        // 顧客コード
                        ->whereLike( 'dm_customer_code', $requestObj->dm_customer_code )
                        // 住所
                        ->whereLike( 'dm_customer_address', $requestObj->dm_customer_address )
                        // チャオ
                        //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
                        ->whereUmuNullCheckbox( 'tb_target_cars.tgc_ciao_course', $requestObj->ciao )
                        // Nシリーズ
                        ->whereMatchIn( 'dm_car_name', $requestObj->dm_car_name )
                        //意向結果
                        ->whereMatch( 'dm_status', $requestObj->dm_action_code )
                        //予約済みを除く
                        ->where(function( $query ){ 
                            $query
                                ->whereNotIn('tb_manage_info.mi_rstc_reserve_status', ['予約確定（承認）'] )
                                ->orWhereNull( 'tb_manage_info.mi_rstc_reserve_status' );
                        });
                });
        }
        
        // 顧客の無を除外
        $query = $query->whereRaw(
            '(
                EXISTS 
                (
                    SELECT
                        umu_csv_flg
                    FROM
                        tb_customer_umu
                    WHERE
                        tb_dm.dm_customer_code = tb_customer_umu.umu_customer_code AND
                        tb_dm.dm_car_manage_number = tb_customer_umu.umu_car_manage_number
                )
            )'
        );
        
        return $query;
    }
    
}
