<?php

namespace App\Models;

use App\Lib\Codes\CheckCodes;
use App\Lib\Util\DateUtil;
use App\Original\Util\SessionUtil;
use DB;
use App\Original\Codes\IkouLatestCodes;
use App\Models\Append\Ikou;
use App\Lib\Util\Constants;

/**
 * 顧客ロックデータモデル
 *
 */
class TargetCars extends AbstractModel {
    // テーブル名
    protected $table = 'tb_target_cars';

    // 変更可能なカラム
    protected $fillable = [
        
        "tgc_inspection_id",                //車点検区分
        "tgc_inspection_ym",                //車点検年月
        "tgc_customer_id",                  //tb_customerのID
        "tgc_base_code_init",               //初期拠点コード
        "tgc_base_name_csv",                //拠点名csv
        "tgc_user_id",                      //担当者Id
        "tgc_user_code_init",              //初期担当者コード
        "tgc_user_name_csv",                //担当者名csv
        "tgc_car_manage_number",            //統合車両管理ＮＯ 
        "tgc_customer_code",                //顧客コード 
        "tgc_customer_name_kanji",          //顧客名 
        "tgc_customer_name_kata",           //顧客カナ 
        "tgc_gensen_code_name",             //源泉 
        "tgc_customer_category_code_name",  //顧客分類 
        "tgc_customer_postal_code",         //自宅郵便番号 
        "tgc_customer_address",             //自宅住所 
        "tgc_customer_tel",                 //自宅TEL 
        "tgc_mobile_tel",                   //携帯TEL
        "tgc_customer_office_tel",          //勤務先TEL 
        "tgc_car_name",                     //車名 
        "tgc_car_year_type",                //年式 
        "tgc_first_regist_date_ym",         //初度登録年月　YYYYMM 
        "tgc_cust_reg_date",                //登録日　YYYY/MM/DD 
        "tgc_car_base_number",              //＊車両基本登録No 
        "tgc_car_model",                    //型式 
        "tgc_car_service_code",             //サービス通称名 
        "tgc_car_frame_number",             //フレームＮＯ 
        "tgc_car_buy_type",                 //自販他販区分 
        "tgc_car_new_old_kbn_name",         //新中区分 
        "tgc_syaken_times",                 //車検回数 
        "tgc_syaken_next_date",             //次回車検日　YYYY/MM/DD 
        "tgc_customer_insurance_type",      //任意保険加入区分 
        "tgc_customer_insurance_company",   //任意保険会社 
        "tgc_customer_insurance_end_date",  //任意保険終期　YYYY/MM/DD 
        "tgc_first_shiharai_date_ym",       //初回支払年月　YYYYMM 
        "tgc_credit_hensaihouhou",          //クレジット返済方法 
        "tgc_credit_hensaihouhou_name",     //クレジット返済方法名称 
        "tgc_keisan_housiki_kbn",           //計算方式 
        "tgc_credit_card_select_kbn",       //クレジット・カード選択区分
        "tgc_credit_manryo_date_ym",        //クレジット契約満了月
        "tgc_memo_syubetsu",                //メモ種別        
        "tgc_last_shiharaikin",             //最終回支払金 
        "tgc_shiharai_count",               //支払回数 
        "tgc_sueoki_zankaritsu",            //据置・残価率 
        "tgc_customer_kouryaku_flg",        //攻略対象車 
        "tgc_customer_dm_flg",              //Ｄ／Ｍ不要区分(オリジナル)
        "tgc_customer_kojin_hojin_flg",     //個人法人コード 
        "tgc_car_type",                     //車種 
        "tgc_abc_zone",                     //ABCゾーン 
        "tgc_htc_number",                   //HTC会員番号 
        "tgc_htc_car",                      //HTC契約車両 
        "tgc_ciao_course",                  //チャオコース/プラン 
        "tgc_ciao_end_date",                //会員証有効期間終期
        // 手入力更新可能な値
        "tgc_daigae_car_name",              //代替予定車種
        "tgc_dm_flg",                       //Ｄ／Ｍ不要区分 
        "tgc_dm_unnecessary_reason",        //Ｄ／Ｍ不要の理由
        "tgc_alert_memo",                   //メモ(モーダル)
        "tgc_memo",                         //伝達事項
        //意向テーブルからの更新
        "tgc_status",                       //意向結果
        "tgc_status_update",                //意向結果の更新日時
        
        "created_by",
        "updated_by",
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

        //\Log::debug( '>>> fieldData' );
        //\Log::debug( $fieldData );
        
        // 値が空でない時だけ更新
        if( !empty( $fieldData["tgc_customer_dm_flg"] ) == True ){
            // オリジナルのDMフラグは、顧客データのDMフラグを取得
            $fieldData["tgc_dm_flg"] = $fieldData["tgc_customer_dm_flg"];
        }
        
        // 値が空でない時だけ更新
        if( !empty( $fieldData["tgc_first_shiharai_date_ym"] ) == True ){
            // 初回支払い月に、支払い月を足した月
            $fieldData["tgc_credit_manryo_date_ym"] = date( "Ym", strtotime( $fieldData["tgc_first_shiharai_date_ym"] . "01 +" . intval( $fieldData["tgc_shiharai_count"] - 1 ) . "month" ) );
        }else{
            $fieldData["tgc_credit_manryo_date_ym"] = NULL;
        }
        
        //意向ロックフラグと最新意向の取得
        $ikou = "";
        if ($values['inspection_id'] == 4 ) {
            $ikou = \DB::select("SELECT ik_ikou_lock_flg,
                                        ik_latest_ikou
                                 FROM tb_ikou
                                 WHERE ik_customer_code = '{$fieldData["tgc_customer_code"]}'
                                       AND ik_syaken_next_date = '{$fieldData['tgc_syaken_next_date']}'
                                       AND substring('{$fieldData['tgc_car_base_number']}', length('{$fieldData['tgc_car_base_number']}') - 5 + 1, length('{$fieldData['tgc_car_base_number']}')) = substring(ik_car_base_number, length(ik_car_base_number) - 5 + 1, length(ik_car_base_number))
                                 ORDER BY tb_ikou.id desc
                                 LIMIT 1;
                              ");

            //意向ロック：フラグが「1」の時はtgc_statusを更新しない、「0」またはnullの場合はそのまま更新
            if (!empty($ikou)) {
                if (empty($ikou[0]->ik_ikou_lock_flg) || $ikou[0]->ik_ikou_lock_flg = '0') {
                    //最新意向のコードリスト
                    $latest_codes = (new IkouLatestCodes)->getOptions();

                    // 意向確認から最新意向を取得
                    $latest_ikou = $ikou[0]->ik_latest_ikou;

                    //最新意向の項目に対応するコードをtgc_statusに設定
                    if (!empty($latest_ikou) && array_search($latest_ikou, $latest_codes)) {
                        $fieldData['tgc_status'] = array_search($latest_ikou, $latest_codes);
                        //意向結果の更新日取得
                        $fieldData['tgc_status_update'] = date("Y/m/d");
                    }
                }
            }
        }

        TargetCars::updateOrCreate(
            [
                'tgc_inspection_id' => $values['inspection_id'],
                'tgc_inspection_ym' => $values['inspection_date'],
                'tgc_car_manage_number' => $values['car_manage_number']
            ],
            $fieldData
        );
        
    }

    
    /**
     * 意向確認.csvからの最新意向を更新
     */
    public static function mergeIkou() {
        
        $sql = "WITH tmp_ikou_history AS ( 
                    SELECT
                        * 
                    FROM
                        tb_ikou_history 
                    WHERE
                        ( 
                            tb_ikou_history.id
                            , ikh_customer_code
                            , ikh_syaken_next_date
                            , ikh_car_manage_number
                        ) IN ( 
                            SELECT
                                max(tb_ikou_history.id) as id
                                , ikh_customer_code
                                , ikh_syaken_next_date
                                , ikh_car_manage_number 
                            FROM
                                tb_ikou_history 
                            WHERE
                                ikh_katsudo_code IS NOT NULL 
                            GROUP BY
                                ikh_customer_code
                                , ikh_syaken_next_date
                                , ikh_car_manage_number
                        )
                )
                UPDATE tb_target_cars
                    SET
                    tgc_status = ( CASE
                                WHEN ikou.ikh_katsudo_code = 11 THEN 11
                                WHEN ikou.ikh_katsudo_code = 12 THEN 12
                                WHEN ikou.ikh_katsudo_code = 13 THEN 13
                                WHEN ikou.ikh_katsudo_code = 14 THEN 14
                                WHEN ikou.ikh_katsudo_code = 15 THEN 15
                                WHEN ikou.ikh_katsudo_code = 16 THEN 16
                                WHEN ikou.ikh_katsudo_code = 17 THEN 17
                                WHEN ikou.ikh_katsudo_code = 20 THEN 20
                                --WHEN ikou.ikh_katsudo_code = 0 THEN null
                        END ),
                    tgc_status_update = ikou.ikh_katsudo_date
                FROM tmp_ikou_history ikou
                WHERE
                (
                    tb_target_cars.tgc_customer_code         = ikou.ikh_customer_code 
                    AND tb_target_cars.tgc_inspection_ym     = ikou.ikh_inspection_ym
                    AND tb_target_cars.tgc_car_manage_number = ikou.ikh_car_manage_number  
                    AND tb_target_cars.tgc_inspection_id     = 4
                ) and
                (
                    (ikou.ikh_katsudo_code = 11 and tb_target_cars.tgc_status   != 11 ) or
                    (ikou.ikh_katsudo_code = 12 and tb_target_cars.tgc_status   != 12 ) or
                    (ikou.ikh_katsudo_code = 13 and tb_target_cars.tgc_status   != 13 ) or
                    (ikou.ikh_katsudo_code = 14 and tb_target_cars.tgc_status   != 14 ) or
                    (ikou.ikh_katsudo_code = 15 and tb_target_cars.tgc_status   != 15 ) or
                    (ikou.ikh_katsudo_code = 16 and tb_target_cars.tgc_status   != 16 ) or
                    (ikou.ikh_katsudo_code = 17 and tb_target_cars.tgc_status   != 17 ) or
                    (ikou.ikh_katsudo_code = 20 and tb_target_cars.tgc_status   != 20 ) or
                    --(ikou.ikh_katsudo_code = 0 and tb_target_cars.tgc_status is not null) or
                    tb_target_cars.tgc_status is null
                )";

        return \DB::statement( $sql );
    }

    /**
     * Viewからのデータを登録用に変換する
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
                $result['tgc_' . $key] = $value;
            }
        }
        
        $result['tgc_customer_id'] = $values['id'];

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
        $query = $query
            ->leftjoin(
                DB::raw("(
                    SELECT 
                        account.id, account.user_code, account.user_name, 
                        account.base_id, base.base_code, base.base_name, base.base_short_name,
                        account.deleted_at
                    FROM 
                        tb_user_account account
                    LEFT JOIN tb_base base ON
                        base.id = account.base_id AND
                        base.deleted_at IS NULL        
              ) as tb_user_account"),function($join){
                $join->on("tb_target_cars.tgc_user_id","=","tb_user_account.id");
            });
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
                        $join->on( 'tb_target_cars'.'.tgc_user_id', '=', 'tb_user_account' . '.id' )
                        ->whereNull( 'tb_user_account' . '.deleted_at' );
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
    public function scopeJoinSalesAll( $query ) {
        $query = $query->leftJoin(
            'tb_user_account',
            function( $join ){
                $join->on( 'tb_target_cars'.'.tgc_user_id', '=', 'tb_user_account' . '.id' );
//                    ->whereNull( 'tb_user_account' . '.deleted_at' );
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
//                    $join->on( 'tb_target_cars' . '.tgc_customer_code', '=', 'v_ciao.ciao_customer_code' )
//                         ->on( 'tb_target_cars' . '.tgc_car_manage_number', '=', 'v_ciao.ciao_car_manage_number' )
//                         //->on( 'tb_target_cars' . '.tgc_inspection_ym', '<=', DB::raw("to_char( v_ciao.ciao_end_date, 'yyyymm' )") )
//                         ->on(
//                                DB::raw( "(" ),
//                                "(
//                                    v_ciao.ciao_course = 'チャオＳＳ' AND
//                                    tb_target_cars.tgc_inspection_ym < to_char( v_ciao.ciao_end_date, 'yyyymm')
//                                )
//                                OR
//                                (
//                                    v_ciao.ciao_course != 'チャオＳＳ' AND
//                                    tb_target_cars.tgc_inspection_ym <= to_char( v_ciao.ciao_end_date, 'yyyymm')
//                                )",
//                                DB::raw( ")" )
//                         )
//                        ->wherenull( 'v_ciao.deleted_at' );
//                });
        //dd( $query->toSql() );
        return "";
    }
    
    /**
     * 担当者テーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinInfo( $query ) {
        $query = $query->leftJoin(
                    'tb_manage_info',
                    function( $join ) {
                    $join->on( 'tb_target_cars.tgc_car_manage_number', '=', 'tb_manage_info.mi_car_manage_number' )
                         ->on( 'tb_target_cars.tgc_inspection_id', '=', 'tb_manage_info.mi_inspection_id' )
                         ->on( 'tb_target_cars.tgc_inspection_ym', '=', 'tb_manage_info.mi_inspection_ym' );
                });

        //dd( $query->toSql() );
        return $query;
    }
    
    /**
     * 有無テーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinUmu( $query ){
        $query = $query->leftJoin(
                    'tb_customer_umu',
                    function( $join ) {
                    $join->on( 'tb_target_cars' . '.tgc_customer_code', '=', 'tb_customer_umu.umu_customer_code' )
                        ->on( 'tb_target_cars' . '.tgc_car_manage_number', '=', 'tb_customer_umu.umu_car_manage_number' )
                        ->wherenull( 'tb_customer_umu.deleted_at' );
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
                    $join->on( 'tb_target_cars.tgc_car_manage_number', '=', 'tb_customer.car_manage_number' )
                        ->wherenull( 'tb_customer.deleted_at' );
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
                        $query->orWhereNull( 'tb_target_cars.tgc_dm_flg' );
                    } else if ( CheckCodes::isAri( $v ) ) {
                        $query->orWhere( 'tb_target_cars.tgc_dm_flg', '=', '1' );
                    }
                }
            });
        }
        return $query;
    }
    
    public function scopeWhereDmConfirmFlgMulti( $query, $value ) {
        if ( !is_null( $value ) && is_array( $value ) ) {
            $query->where(function($query) use ($value) {
                foreach ( $value as $k => $v ) {
                    if ( CheckCodes::isNashi( $v ) ) {
                        $query->orWhere( 'tb_target_cars.dm_confirm_flg', '=', '0' );
                    } else if ( CheckCodes::isAri( $v ) ) {
                        $query->orWhere( 'tb_target_cars.dm_confirm_flg', '=', '1' );
                    }
                }
            });
        }
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
    public function scopeWhereRequest( $query, $requestObj, $showType="syaken" ){
        
        $query = $query
            // 統合車両管理NO
            ->whereLike( 'tgc_car_manage_number', $requestObj->tgc_car_manage_number )
            // 拠点コード
//            ->whereLike( 'tgc_base_code', $requestObj->base_code )
            // 担当者コード
            //->whereLike( 'tgc_user_id', $requestObj->user_id )
            // 顧客コード
            ->whereLike( 'tgc_customer_code', $requestObj->tgc_customer_code )
            // 顧客名
            ->whereLike( 'tgc_customer_name_kanji', $requestObj->tgc_customer_name )
            //車両基本Ｎｏ
            ->whereLike( 'tgc_car_base_number', $requestObj->tgc_car_base_number )
            //車種名
            ->whereLike( 'tgc_car_name', $requestObj->tgc_car_name )
            //活動意向
            //->whereMatch( 'tgc_status', $requestObj->tgc_action_code )    ※20180829変更前
            //TMR意向（）
            ->whereMatch( 'tb_manage_info.mi_tmr_in_sub_intention', $requestObj->tgc_tmr_name )
            // 保険
            ->whereUmuNullCheckbox( 'tgc_customer_insurance_end_date', $requestObj->insu_flg )
            // 保険区分
            ->whereMatch( 'tgc_customer_insurance_type', $requestObj->insu_type )
            // クレ
            ->whereUmuNullCheckbox( 'tgc_credit_manryo_date_ym', $requestObj->cre_flg )
            // クレ区分
            //->whereMatch( 'tgc_credit_hensaihouhou_name', $requestObj->cre_type )     ※まとめて検索のため、下記に移動
            // 車点検月
            ->wherePeriodNormal( 'tgc_inspection_ym', $requestObj->tgc_inspection_ym_from, $requestObj->tgc_inspection_ym_to )
            // 車点検区分
            ->whereMatch( 'tgc_inspection_id', $requestObj->tgc_inspection_id )
            // 車検回数
            ->whereMatch( 'tgc_syaken_times', $requestObj->syaken_time )
            // チャオ２コース
            //->whereMatchIn( 'v_ciao.ciao_course', $requestObj->ciao_type )
            // チャオ
            //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
            //->whereUmuNullCheckbox( 'tb_target_cars.tgc_ciao_course', $requestObj->ciao )
            ->whereCiaoCheck( $requestObj->ciao)
            // ABC
            //->whereAbcCheckbox('tb_manage_info.mi_abc_abc', $requestObj->abc);
            ->whereAbcCheckbox('tb_target_cars.tgc_abc_zone', $requestObj->abc)
            // HTCログイン
            ->whereUmuCheckbox('tb_manage_info.mi_htc_login_flg', $requestObj->mi_htc_login_flg);
            if( $showType == "syaken" ){
                $query = $query
//                    // 予約済
//                    ->whereCheckValueNull('tb_manage_info.mi_dsya_syaken_reserve_date', $requestObj->reserve_flg)
                    // 入庫済
                    ->whereCheckValueNull('tb_manage_info.mi_dsya_syaken_jisshi_date', $requestObj->get_out_flg);
            } else {
                $query = $query
                    // 予約済
                    ->whereCheckValueIn('tb_manage_info.mi_rstc_reserve_status', $requestObj->reserve_flg, ['完成','入庫','診断','作業待ち','作業中','検査','作業完了','出庫待ち','予約確定（承認）','納品書発行済'])
                    // 入庫済
                    ->whereCheckValue('tb_manage_info.mi_rstc_reserve_status', $requestObj->get_out_flg, '出荷済');
                    if(!empty($requestObj->tgc_status) && $requestObj->tgc_status != 1) {
                        $query = $query->where('tgc_status', '=', $requestObj->tgc_status);
                    }
            }
            // 2022/03/29 Add where check search reserve_flg
            if($showType == "syaken" && isset($requestObj->reserve_flg)){
                if($requestObj->reserve_flg[0]){
                    $query = $query->whereCheckValueNull('tb_manage_info.mi_dsya_syaken_reserve_date', $requestObj->reserve_flg);
                    $queryExclusion = " to_char(tgc_syaken_next_date + '-1 years'::interval, 'YYYYMM'::text) < to_char(tb_manage_info.mi_dsya_syaken_reserve_date, 'YYYYMM') ";
                    $query = $query->whereRaw($queryExclusion);
                }
//                2022/04/05 update
                elseif (!$requestObj->reserve_flg[0] && !isset($requestObj->reserve_flg[1] )){
                    $queryEx = " (tb_manage_info.mi_dsya_syaken_reserve_date is null or to_char(tgc_syaken_next_date + '-1 years'::interval, 'YYYYMM'::text) >= to_char(tb_manage_info.mi_dsya_syaken_reserve_date, 'YYYYMM') )";
                    $query = $query->WhereRaw($queryEx);
                }

            }


            // 入庫済
//            if(!empty( $requestObj->get_out_flg )){
//                $query = $query->whereMatchIn( 'tb_manage_info.mi_rstc_reserve_status', '出荷済_入庫_診断_作業待ち_作業中_検査_作業完了_出庫待ち' );
//                if ($requestObj->get_out_flg == '1') {
//                    $query = $query->whereMatchIn( 'tb_manage_info.mi_rstc_reserve_status', '出荷済_入庫_診断_作業待ち_作業中_検査_作業完了_出庫待ち' );
//                }
//                if ($requestObj->get_out_flg == '0') {
//                }
//            }else{
//                    $query = $query->whereNotIn( 'tb_manage_info.mi_rstc_reserve_status', ['出荷済','入庫','診断','作業待ち','作業中','検査','作業完了','出庫待ち'] )
//                            ->orWhereNull( 'tb_manage_info.mi_rstc_reserve_status' );
//            }
            
            // 退職者の場合
            if($requestObj->user_id == Constants::CONS_TAISHOKUSHA_CODE){
                $query = $query->WhereUmuNull( 'tb_user_account.deleted_at', '1' ); // deleted_at is not null
            }else{
                $query = $query->whereMatch( 'tb_user_account.id', $requestObj->user_id );
            }

            if( $showType == "tenken" ){
                $query = $query->whereNotIn('tgc_inspection_id', [4] )
                    // TMR意向（）
                    ->whereMatch( 'tb_manage_info.mi_tmr_in_sub_intention', $requestObj->tgc_tmr_name );

                // 2022/03/22 Add where
                $queryExclusion = " (tgc_customer_kouryaku_flg <> '1' OR tgc_customer_kouryaku_flg IS NULL) ";
                $query = $query->whereRaw($queryExclusion);

            }else if( $showType == "syaken" ){
                $query = $query->where('tgc_inspection_id', 4 )
                    // TMR意向（）
                    ->whereMatch( 'tb_manage_info.mi_tmr_in_sub_intention', $requestObj->tgc_tmr_name )
                    // クレジットタイプ
                    ->whereMatchIn( 'tgc_credit_hensaihouhou_name', $requestObj->credit_type );
                
//                    // 6ヶ月前でデータロック（それ以降は対象外　※2020/5/1までに登録済のデータは除く）
//                    ->where(function( $query ){
//                        $query->where(
//                                DB::raw("to_char((tgc_syaken_next_date - interval '6 months'), 'yyyymm')") ,
//                                ">=", DB::raw("to_char(tb_target_cars.created_at, 'yyyymm')"))
//                                ->orWhere("tb_target_cars.created_at", "<", "2020/05/01");
//                    });
                    //$lock = CodeUtil::getUmuFlag($requestObj->tgc_lock_flg6);
                    $queryLock = " 1=1 ";
                    if ( isset($requestObj->tgc_lock_flg6) && $requestObj->tgc_lock_flg6 != ''){
                        $queryLock = " tgc_lock_flg6 = 0 ";
                    }
                    $query = $query->whereRaw($queryLock);

                    if($requestObj->tgc_action_code == '20') {
                        $queryStatusKatsudo = "(tgc_status = '20' OR tgc_status IS NULL)";
                        $query = $query->whereRaw($queryStatusKatsudo);
                    }else {
                        $query->whereMatchIn('tgc_status', $requestObj->tgc_action_code);
                    }

                    // 先行実施を非表示　※車検期間の条件（45日前まで可）を加味  @201907 非表示→表示へ変更
//                    ->where(function( $query ){ 
//                        $query
//                            ->where( 'tb_target_cars.tgc_syaken_next_date' , "<=",
//                                DB::raw(" date_trunc( 'day',tb_manage_info.mi_rstc_delivered_date + '45 days' ) ") )
//                            ->where( 'tb_target_cars.tgc_inspection_ym' , "!=",
//                                DB::raw(" to_char( tb_manage_info.mi_rstc_delivered_date + '1 months','yyyymm' ) ") )
//                            ->where( 'tb_target_cars.tgc_inspection_ym' , "!=",
//                                DB::raw(" to_char( tb_manage_info.mi_rstc_delivered_date + '2 months','yyyymm' ) ") )
//                           ->orWhereNull("tb_manage_info.mi_rstc_delivered_date");
//                    });
            }
            // 任意保険終期
            if(!empty($requestObj->insu_end_date)){
                $query = $query->where(DB::raw("to_char( tgc_customer_insurance_end_date, 'yyyymm')"), '=', $requestObj->insu_end_date);
            }
            
        // 担当者情報を取得
        $loginAccountObj = SessionUtil::getUser();

        // 店長, 工場長, 営業担当, CSの人の時は、所属拠点のみ表示   →　営業担当・CSの時へ変更
        //if( in_array( $loginAccountObj->getRolePriority(), [4,5,6,7] ) ){
        if( in_array( $loginAccountObj->getRolePriority(), [6,7] ) ){
            // 拠点コードを取得
            $base_code = $loginAccountObj->getBaseCode();
            $query = $query->where( 'tb_user_account.base_code', '=', $base_code );

        }elseif( in_array( $loginAccountObj->getRolePriority(), [1,2,3,4,5] )){
            // 
            $query = $query->whereLike( 'base_code', $requestObj->base_code );
        }

        return $query;
    }
    
    
    
    

    ###########################
    ## Dm List Commands
    ###########################
    
    /**
     * 検索条件を指定するメソッド(車検6ヶ月前)
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereDmTenkenLastRequest( $query, $requestObj ){
        $query = $query
            // 統合車両管理NO
            ->whereLike( 'tgc_car_manage_number', $requestObj->tgc_car_manage_number )
            // 拠点コード
//            ->whereLike( 'tgc_base_code', $requestObj->base_code )
            // 担当者コード
            ->whereLike( 'tgc_user_id', $requestObj->user_id )
            // 担当者名の有無
            ->whereUmuNullCheckbox( 'tb_user_account.user_id', $requestObj->user_id_flg )
            // 顧客コード
            ->whereLike( 'tgc_customer_code', $requestObj->tgc_customer_code )
            // 住所
            ->whereLike( 'tgc_customer_address', $requestObj->tgc_customer_address )
            // お車ナンバー
            ->whereLike( 'tgc_car_base_number', $requestObj->tgc_car_base_number )
            //意向結果
            ->whereMatch( 'tgc_status', $requestObj->tgc_action_code )
            // 保険
            ->whereUmuNullCheckbox( 'tgc_customer_insurance_end_date', $requestObj->insu_flg )
            // クレ
            ->whereUmuNullCheckbox( 'tgc_credit_manryo_date_ym', $requestObj->cre_flg )
            // 予約
            //->whereUmuNullCheckbox( 'tb_manage_info.mi_rstc_reserve_commit_date', $requestObj->reserve_flg )
            ->whereCheckValue( 'tb_manage_info.mi_rstc_reserve_status', $requestObj->reserve_flg , '予約確定（承認）' )
            // 出庫
            //->whereUmuNullCheckbox( 'tb_manage_info.mi_rstc_delivered_date', $requestObj->get_out_flg )
            ->whereCheckValue( 'tb_manage_info.mi_rstc_reserve_status', $requestObj->get_out_flg, '出荷済' )
            // チャオ
            //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
            //->whereUmuNullCheckbox( 'tb_tager_cars.tgc_ciao_course', $requestObj->ciao )
            ->whereCiaoCheck( $requestObj->ciao)
            // 点検のみ
            ->whereIn('tgc_inspection_id', [2] )
            // 車検6ヶ月前(最後の安心快適)のみとする
            ->where('tgc_inspection_ym', '=', DB::raw("to_char( tgc_syaken_next_date + '-6 mons'::interval, 'yyyymm')") )
            // 抽出用のときの条件式
            ->whereCustomerDmFlg( $requestObj->customer_dm_flg )
            //予約済みを除く
            ->where(function( $query ){ 
                $query
                    ->whereNotIn('tb_manage_info.mi_rstc_reserve_status', ['予約確定（承認）'] )
                    ->orWhereNull( 'tb_manage_info.mi_rstc_reserve_status' );
            })
            // 対象月
            ->where( 'tgc_inspection_ym', date( "Ym", strtotime( $requestObj->tgc_inspection_ym . "01" ) ) );
        
        // ログイン情報を取得
        $loginAccountObj = SessionUtil::getUser();
        
        // 店長, 工場長, 営業担当, CSの人の時は、所属拠点のみ表示   →　営業担当・CSの時へ変更
        //if( in_array( $loginAccountObj->getRolePriority(), [4,5,6,7] ) ){
        if( in_array( $loginAccountObj->getRolePriority(), [6,7] ) ){
            // 拠点コードを取得
            $base_code = $loginAccountObj->getBaseCode();
            $query = $query->where( 'tb_user_account.base_code', '=', $base_code );

        }elseif( in_array( $loginAccountObj->getRolePriority(), [1,2,3,4,5] )){
            //
            $query = $query->whereLike( 'base_code', $requestObj->base_code );
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
                        tb_target_cars.tgc_customer_code = tb_customer_umu.umu_customer_code AND
                        tb_target_cars.tgc_car_manage_number = tb_customer_umu.umu_car_manage_number
                )
            )'
        );
        
        return $query;
    }
    
    /**
     * 検索条件を指定するメソッド(点検)
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereDmTenkenRequest( $query, $requestObj ){
        $query = $query
            // 統合車両管理NO
            ->whereLike( 'tgc_car_manage_number', $requestObj->tgc_car_manage_number )
            // 拠点コード
//            ->whereLike( 'tgc_base_code', $requestObj->base_code )
            // 担当者コード
            ->whereLike( 'tgc_user_id', $requestObj->user_id )
            // 担当者名の有無
            ->whereUmuNullCheckbox( 'tb_user_account.user_id', $requestObj->user_id_flg )
            // 顧客コード
            ->whereLike( 'tgc_customer_code', $requestObj->tgc_customer_code )
            // 車点検区分
            ->whereMatch( 'tgc_inspection_id', $requestObj->tgc_inspection_id )
            // 住所
            ->whereLike( 'tgc_customer_address', $requestObj->tgc_customer_address )
            // お車ナンバー
            ->whereLike( 'tgc_car_base_number', $requestObj->tgc_car_base_number )
            //意向結果
            ->whereMatch( 'tgc_status', $requestObj->tgc_action_code )
            // 保険
            ->whereUmuNullCheckbox( 'tgc_customer_insurance_end_date', $requestObj->insu_flg )
            // クレ
            ->whereUmuNullCheckbox( 'tgc_credit_manryo_date_ym', $requestObj->cre_flg )
            // 予約
            //->whereUmuNullCheckbox( 'tb_manage_info.mi_rstc_reserve_commit_date', $requestObj->reserve_flg )
            ->whereCheckValue( 'tb_manage_info.mi_rstc_reserve_status', $requestObj->reserve_flg , '予約確定（承認）' )
            // 出庫
            //->whereUmuNullCheckbox( 'tb_manage_info.mi_rstc_delivered_date', $requestObj->get_out_flg )
            ->whereCheckValue( 'tb_manage_info.mi_rstc_reserve_status', $requestObj->get_out_flg, '出荷済' )
            // チャオ
            //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
            //->whereUmuNullCheckbox( 'tb_target_cars.tgc_ciao_course', $requestObj->ciao )
            ->whereCiaoCheck( $requestObj->ciao)
            // 点検のみ
            ->whereNotIn('tgc_inspection_id', [4] )
            // 車検6ヶ月前(最後の安心快適)を除く
            ->where('tgc_inspection_ym', '!=', DB::raw("to_char( tgc_syaken_next_date + '-6 mons'::interval, 'yyyymm')") )
            // 抽出用のときの条件式
            ->whereCustomerDmFlg( $requestObj->customer_dm_flg )
            //予約済みを除く
            ->where(function( $query ){ 
                $query
                    ->whereNotIn('tb_manage_info.mi_rstc_reserve_status', ['予約確定（承認）'] )
                    ->orWhereNull( 'tb_manage_info.mi_rstc_reserve_status' );
            })
            // 対象月
            ->where( 'tgc_inspection_ym', date( "Ym", strtotime( $requestObj->tgc_inspection_ym . "01" ) ) );

        // ログイン情報を取得
        $loginAccountObj = SessionUtil::getUser();
        
        // 店長, 工場長, 営業担当, CSの人の時は、所属拠点のみ表示   →　営業担当・CSの時へ変更
        //if( in_array( $loginAccountObj->getRolePriority(), [4,5,6,7] ) ){
        if( in_array( $loginAccountObj->getRolePriority(), [6,7] ) ){
            // 拠点コードを取得
            $base_code = $loginAccountObj->getBaseCode();
            $query = $query->where( 'tb_user_account.base_code', '=', $base_code );

        }elseif( in_array( $loginAccountObj->getRolePriority(), [1,2,3,4,5] )){
            // 
            $query = $query->whereLike( 'base_code', $requestObj->base_code );
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
                        tb_target_cars.tgc_customer_code = tb_customer_umu.umu_customer_code AND
                        tb_target_cars.tgc_car_manage_number = tb_customer_umu.umu_car_manage_number
                )
            )'
        );
        
        return $query;
    }
    
    /**
     * 検索条件を指定するメソッド
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereDmRequest( $query, $requestObj, $inspection_id ){
        $query = $query
            // 統合車両管理NO
            ->whereLike( 'tgc_car_manage_number', $requestObj->tgc_car_manage_number )
            // 拠点コード
//            ->whereLike( 'tgc_base_code', $requestObj->base_code )
            // 担当者コード
            ->whereLike( 'tgc_user_id', $requestObj->user_id )
            // 担当者名の有無
            ->whereUmuNullCheckbox( 'tb_user_account.user_id', $requestObj->user_id_flg )
            // 顧客コード
            ->whereLike( 'tgc_customer_code', $requestObj->tgc_customer_code )
            // 住所
            ->whereLike( 'tgc_customer_address', $requestObj->tgc_customer_address )
            // お車ナンバー
            ->whereLike( 'tgc_car_base_number', $requestObj->tgc_car_base_number )
            //意向結果
            ->whereMatch( 'tgc_status', $requestObj->tgc_action_code )
            // 保険
            ->whereUmuNullCheckbox( 'tgc_customer_insurance_end_date', $requestObj->insu_flg )
            // クレ
            ->whereUmuNullCheckbox( 'tgc_credit_manryo_date_ym', $requestObj->cre_flg )
            // 予約
            //->whereUmuNullCheckbox( 'tb_manage_info.mi_rstc_reserve_commit_date', $requestObj->reserve_flg )
            ->whereCheckValue( 'tb_manage_info.mi_rstc_reserve_status', $requestObj->reserve_flg , '予約確定（承認）' )
            // 出庫
            //->whereUmuNullCheckbox( 'tb_manage_info.mi_rstc_delivered_date', $requestObj->get_out_flg )
            ->whereCheckValue( 'tb_manage_info.mi_rstc_reserve_status', $requestObj->get_out_flg, '出荷済' )
            // チャオ
            //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
            //->whereUmuNullCheckbox( 'tb_target_cars.tgc_ciao_course', $requestObj->ciao )
            ->whereCiaoCheck( $requestObj->ciao)
            // 車検判断
            ->whereIn('tgc_inspection_id', [$inspection_id] )
            // 抽出用のときの条件式
            ->whereCustomerDmFlg( $requestObj->customer_dm_flg )
            // Nシリーズ
            ->whereMatchIn( 'tgc_car_name', $requestObj->tgc_car_name )
            //予約済みを除く
            ->where(function( $query ){ 
                $query
                    ->whereNotIn('tb_manage_info.mi_rstc_reserve_status', ['予約確定（承認）'] )
                    ->orWhereNull( 'tb_manage_info.mi_rstc_reserve_status' );
            })
            // 対象月
            ->where( 'tgc_inspection_ym', date( "Ym", strtotime( $requestObj->tgc_inspection_ym . "01" ) ) );

        // ログイン情報を取得
        $loginAccountObj = SessionUtil::getUser();
        
        // 店長, 工場長, 営業担当, CSの人の時は、所属拠点のみ表示   →　営業担当・CSの時へ変更
        //if( in_array( $loginAccountObj->getRolePriority(), [4,5,6,7] ) ){
        if( in_array( $loginAccountObj->getRolePriority(), [6,7] ) ){
            // 拠点コードを取得
            $base_code = $loginAccountObj->getBaseCode();
            $query = $query->where( 'tb_user_account.base_code', '=', $base_code );

        }elseif( in_array( $loginAccountObj->getRolePriority(), [1,2,3,4,5] )){
            // 
            $query = $query->whereLike( 'base_code', $requestObj->base_code );
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
                        tb_target_cars.tgc_customer_code = tb_customer_umu.umu_customer_code AND
                        tb_target_cars.tgc_car_manage_number = tb_customer_umu.umu_car_manage_number
                )
            )'
        );
        
        return $query;
    }
    
    /*
     * 意向ロックフラグの一括更新
     */
    public static function updateIkouLockFlag() {
        
//        //意向確認のロック条件を取得、「1」ならロック、「0」ならロックしない
//        $ikou_lock_list = DB::select( "SELECT CASE WHEN info.mi_rstc_reserve_status = '出荷済'
//                                                        AND info.mi_rstc_delivered_date is not null
//                                                        AND info.mi_rstc_delivered_date <> '1970-01-01 09:00:00' THEN 1
//                                                   WHEN tgc.tgc_status is not null
//                                                        AND info.mi_ik_final_achievement <> '自社車検' THEN 1
//                                                   ELSE 0
//                                              END AS flg,
//                                              tgc.tgc_customer_code AS customer_code,
//                                              tgc.tgc_syaken_next_date AS syaken_next_date,
//                                              tgc.tgc_car_base_number AS car_base_number
//                                       FROM tb_target_cars tgc LEFT JOIN tb_manage_info info
//                                            ON tgc.tgc_inspection_id = info.mi_inspection_id
//                                            AND tgc.tgc_inspection_ym = info.mi_inspection_ym
//                                            AND tgc.tgc_car_manage_number = info.mi_car_manage_number
//                                       WHERE tgc.tgc_inspection_id = 4
//                                       GROUP BY flg,customer_code,syaken_next_date,car_base_number
//                                       ORDER BY tgc.tgc_customer_code desc
//                                    " );
//
//        //フラグリストが取得できた場合のみ、ik_ikou_lock_flgを更新
//        if(!empty($ikou_lock_list)){
//            foreach ($ikou_lock_list as $val){
//                // 末尾5文字を使用する処理
//                $str_length = mb_strlen($val->car_base_number);
//                $last5str = mb_substr($val->car_base_number, $str_length - 5, $str_length, 'utf-8');
//
//                DB::statement( "UPDATE tb_ikou SET ik_ikou_lock_flg = '{$val->flg}' ,
//                                    ik_ikou_lock_flg_update = (current_timestamp)
//                                WHERE ik_customer_code = '{$val->customer_code}'
//                                    AND ik_syaken_next_date = '{$val->syaken_next_date}'
//                                    AND ik_car_base_number LIKE '%{$last5str}' " );
//            }
//        }

        // 意向ロックフラグを更新
        $update_ikou_lock = "
            WITH tmp_lock AS (
                SELECT 
                    CASE
                          WHEN info.mi_rstc_reserve_status  = '出荷済'  
                          THEN 1 ELSE 0
                      END AS flg,
                      tgc.tgc_customer_code AS customer_code,
                      tgc.tgc_syaken_next_date AS syaken_next_date,
                      tgc.tgc_car_base_number AS car_base_number
            
                FROM tb_target_cars tgc
                 
                LEFT JOIN tb_manage_info info ON 
                    tgc.tgc_inspection_id = info.mi_inspection_id 
                AND tgc.tgc_inspection_ym = info.mi_inspection_ym 
                AND tgc.tgc_car_manage_number = info.mi_car_manage_number
            
                WHERE tgc.tgc_inspection_id = 4
            )
            UPDATE tb_ikou SET
                ik_ikou_lock_flg = tmp_lock.flg ,
                ik_ikou_lock_flg_update = (current_timestamp)
            FROM tmp_lock
            WHERE tb_ikou.ik_customer_code = tmp_lock.customer_code
            AND tb_ikou.ik_syaken_next_date = tmp_lock.syaken_next_date
            AND right(tb_ikou.ik_car_base_number,5) =  right(tmp_lock.car_base_number,5) 
        ";
        \DB::statement( $update_ikou_lock );

    }
    
}
