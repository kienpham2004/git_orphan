<?php

namespace App\Models;

use App\Lib\Util\DateUtil;
use DB;

/**
 * 保険ロックデータモデル
 *
 */
class Credit extends AbstractModel {
    // テーブル名
    protected $table = 'tb_credit';

    // 変更可能なカラム
    protected $fillable = [
        "cre_inspection_ym",
        "cre_customer_id",
        "cre_car_manage_number",
        "cre_base_code",
        "cre_base_name",
        "cre_user_id",
        "cre_user_name",
        "cre_customer_code",
        "cre_customer_name_kanji",
        "cre_customer_name_kata",
        "cre_gensen_code_name",
        "cre_customer_category_code_name",
        "cre_action_pattern_code",
        "cre_customer_postal_code",
        "cre_customer_address",
        "cre_customer_tel",
        "cre_customer_office_tel",
        "cre_car_name",
        "cre_car_year_type",
        "cre_first_regist_date_ym",
        "cre_cust_reg_date",
        "cre_car_base_number",
        "cre_car_maker_code",
        "cre_car_model",
        "cre_car_service_code",
        "cre_car_frame_number",
        "cre_car_type_code",
        "cre_car_buy_type",
        "cre_car_new_old_kbn_name",
        "cre_syaken_times",
        "cre_syaken_next_date",
        "cre_credit_hensaihouhou",
        "cre_credit_hensaihouhou_name",
        "cre_first_shiharai_date_ym",
        "cre_keisan_housiki_kbn",
        "cre_credit_card_select_kbn",
        "cre_memo_syubetsu",
        "cre_shiharai_count",
        "cre_sueoki_zankaritsu",
        "cre_last_shiharaikin",
        "cre_credit_manryo_date_ym",
        "cre_customer_kouryaku_flg",
        "cre_customer_dm_flg",
        "cre_dm_flg",
        "cre_dm_unnecessary_reason",
        "cre_status",
        "cre_action",
        "cre_satei_flg",
        "cre_memo",
        "created_by",
        "updated_by"
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
        $fieldData = static::convertFromViewCredit( $values );
        //\Log::debug( '>>> fieldData' );
        //\Log::debug( $fieldData );

        Credit::updateOrCreate(
            [
                'cre_credit_manryo_date_ym' => $values['credit_manryo_date_ym'],
                'cre_car_manage_number' => $values['car_manage_number']
            ],
            $fieldData
        );
        
    }

    /**
     * Viewからのデータを登録用に変換する
     *
     * @param unknown $values
     */
    public static function convertFromViewCredit( $values ) {
        $filter = collect([
                'id',
                'created_at',
                'updated_at',
                'deleted_at',
                'created_by',
                'updated_by'
        ]);

        foreach ( $values as $key => $value ) {
            $ex = $filter->contains( $key );
            \Log::debug( $ex );
            // $filter->contains()の戻り値はboolianなので、
            // emptyではちょっと違和感を覚えました。
            // if (! $ex) {} でもいいように思います
            if( empty( $ex ) ) {
                $result['cre_' . $key] = $value;
            }
        }
        $result['cre_customer_id'] = $values['id'];
        $result['created_by'] = $values['created_by'];
        $result['updated_by'] = $values['updated_by'];
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
                        $join->on( 'tb_credit'.'.cre_base_code', '=', 'tb_base' . '.base_code' )
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
                        $join->on( 'tb_credit.cre_user_id', '=', 'tb_user_account.user_id' )
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
//                    $join->on( 'tb_credit.cre_customer_code', '=', 'v_ciao.ciao_customer_code' )
//                         ->on( 'tb_credit.cre_car_manage_number', '=', 'v_ciao.ciao_car_manage_number' )
//                         ->on( 'tb_credit.cre_inspection_ym', '<=', DB::raw("to_char( v_ciao.ciao_end_date, 'yyyymm' )") )
//                         ->wherenull( 'v_ciao.deleted_at' );
//                    }
//                );
        $query = $query->leftJoin(
            'tb_target_cars',
            function( $join ) {
                $join->on( 'tb_credit.cre_customer_code', '=', 'tb_target_cars.tgc_customer_code' )
                    ->on( 'tb_credit.cre_car_manage_number', '=', 'tb_target_cars.tgc_car_manage_number' )
                    ->on( 'tb_credit.cre_inspection_ym', '<=', DB::raw("to_char( tb_target_cars.tgc_ciao_end_date, 'yyyymm' )") )
                    ->wherenull( 'tb_target_cars.deleted_at' );
            }
        );

        //dd( $query->toSql() );
        return $query;
    }

    /**
     * tb_manage_infoテーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinInfo( $query ){
        $query = $query->leftJoin(
                    'v_credit_info',
                    function( $join ){
                    $join->on( 'tb_credit.cre_car_manage_number', '=', 'v_credit_info.tgci_tgc_car_manage_number' )
                        ->on( 'tb_credit.cre_inspection_ym', '=', 'v_credit_info.tgci_tgc_inspection_ym' );
                    }
                );

        //dd( $query->toSql() );
        return $query;
    }

    ###########################
    ## Tenken List Commands
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
            ->whereLike( 'cre_car_manage_number', $requestObj->cre_car_manage_number )
            // 拠点コード
            ->whereLike( 'cre_credit_hensaihouhou_name', $requestObj->cre_credit_hensaihouhou_name )
            // 拠点コード
            ->whereLike( 'cre_base_code', $requestObj->base_code )
            // 担当者コード
            ->whereLike( 'cre_user_id', $requestObj->user_id )
            // 顧客コード
            ->whereLike( 'cre_customer_code', $requestObj->cre_customer_code )
            // 顧客名
            ->whereLike( 'cre_customer_name_kanji', $requestObj->cre_customer_name )
             //契約満了月
            ->wherePeriodNormal( 'tb_credit.cre_inspection_ym', $requestObj->cre_shipping_ym_from, $requestObj->cre_shipping_ym_to  )
            //車両基本Ｎｏ
            ->whereLike( 'cre_car_base_number', $requestObj->cre_car_base_number )
            //車種名
            ->whereLike( 'cre_car_name', $requestObj->cre_car_name )
            //意向結果
            ->whereMatch( 'cre_status', $requestObj->cre_status )
            // チャオ
            //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao );
            ->whereUmuNullCheckbox( 'tb_target_cars.tgc_ciao_course', $requestObj->ciao );


        return $query;
    }
    
    
}
