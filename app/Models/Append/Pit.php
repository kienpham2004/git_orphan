<?php

namespace App\Models\Append;

use App\Lib\Util\DateUtil;
use App\Models\AbstractModel;
use DB;

/**
 * ピット管理データに関するモデル
 */
class Pit extends AbstractModel {
    // テーブル名
    protected $table = 'tb_result';

    // 変更可能なカラム
    protected $fillable = [
        'rst_base_code_init',
        'rst_accept_date', 
        'rst_customer_code', 
        'rst_customer_name', 
        'rst_user_id',
        'rst_user_code_init',
        'rst_user_name_csv',
        'rst_user_base_code', 
        'rst_car_name', 
        'rst_manage_number', 
        'rst_start_date', 
        'rst_end_date', 
        'rst_detail', 
        'rst_hosyo_kbn', 
        'rst_youmei', 
        'rst_hikitori_value',
        'rst_put_in_date', 
        'rst_get_out_date',
        'rst_daisya_value',
        'rst_reserve_commit_date', 
        'rst_reserve_status', 
        'rst_delivered_date', 
        'rst_work_put_date', 
        'rst_syaken_next_date', 
        'rst_matagi_group_number',
        'rst_machi_seibi_value',
        'rst_web_reserv_flg'
    ];
    
    ###########################
    ## CSVの処理
    ###########################
    
    /**
     * データの登録と更新
     * @param  [type] $values [description]
     * @return [type]         [description]
     */
    public static function merge( $values ) {
        //\Log::debug( $values );

        if ( !is_null( $values['rst_manage_number'] ) == True ){
            // 統合車両管理Noと作業内容と次回車検日が合致するデータは更新
            Pit::updateOrCreate(
                [
                    'rst_manage_number' => $values['rst_manage_number'], 
                    'rst_detail' => $values['rst_detail'],
                    'rst_syaken_next_date' => $values['rst_syaken_next_date'],
                    'rst_customer_code' => $values['rst_customer_code']
                ],
                $values
            );
            
        }
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
                        $join->on( 'tb_result'.'.rst_base_code', '=', 'tb_base' . '.base_code' )
                             ->whereNull( 'tb_base' . '.deleted_at' );
                    }
                );

        //dd( $query->toSql() );
        return $query;
    }

    /**
     * 顧客テーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinCustomer( $query ) {
        $query = $query->leftJoin(
                    'tb_customer',
                    function( $join ){
                        $join->on( 'tb_result'.'.rst_manage_number', '=', 'tb_customer' . '.car_manage_number' )
                             ->whereNull( 'tb_customer' . '.deleted_at' );
                    }
                );

        //dd( $query->toSql() );
        return $query;
    }
    
    /**
     * vチャオテーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinCiao( $query ){
//        $query = $query->leftJoin(
//                    'v_ciao',
//                    function( $join ) {
//                    $join->on( 'tb_result' . '.rst_customer_code', '=', 'v_ciao.ciao_customer_code' )
//                        ->on( 'tb_result' . '.rst_manage_number', '=', 'v_ciao.ciao_car_manage_number' )
//                        ->on( 'tb_result' . '.rst_syaken_next_date', '<=', 'v_ciao.ciao_end_date' ) // ここの処理は自身ないです。
//                        ->wherenull( 'v_ciao.deleted_at' );
//                });
        $query = $query->leftJoin(
            'tb_target_cars',
            function( $join ) {
                $join->on( 'tb_result' . '.rst_customer_code', '=', 'tb_target_cars.tgc_customer_code' )
                    ->on( 'tb_result' . '.rst_manage_number', '=', 'tb_target_cars.tgc_car_manage_number' )
                    ->on( 'tb_result' . '.rst_syaken_next_date', '<=', 'tb_target_cars.tgc_ciao_end_date' ) // ここの処理は自身ないです。
                    ->wherenull( 'tb_target_cars.deleted_at' );
            });

        //dd( $query->toSql() );
        return $query;
    }

    /**
     * 保険テーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinInsurance( $query ){
        $query = $query->leftJoin(
                    'tb_insurance',
                    function( $join ) {
                    $join->on( 'tb_result.rst_manage_number', '=', 'tb_insurance.insu_car_manage_number' )
                        //->on( DB::raw('to_char( tb_result.rst_start_date, \'yyyymm\' )'), '<=', 'tb_insurance.insu_inspection_ym' )
                        ->on( DB::raw('to_char( tb_result.rst_start_date, \'yyyymm\' )'), '<=',
                            DB::raw('
                                (
                                    select
                                        min( insu2.insu_inspection_ym )
                                    from
                                        tb_insurance insu2
                                    where
                                        tb_result.rst_manage_number = insu2.insu_car_manage_number
                                ) '
                            )
                        )
                        ->wherenull( 'tb_insurance.deleted_at' );
                });

        return $query;
    }

    ###########################
    ## Target List Commands
    ###########################
    
    /**
     * 検索条件を指定するメソッド
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereRequest( $query, $requestObj ){
        return $query;
    }

}
