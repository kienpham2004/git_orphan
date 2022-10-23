<?php

namespace App\Models;

use Log;
use App\Original\Util\SessionUtil;
use DB;
use App\Lib\Util\Constants;

/**
 * 顧客データに関するモデル
 */
class Customer extends AbstractModel {
    // テーブル名
    protected $table = 'tb_customer';

    // 変更可能なカラム
    protected $fillable = [
        "base_code_init",               //初期拠点コード
        "base_name_csv",                //拠点名csv
        "user_id",                       //担当者Id
        "user_code_init",               //初期担当者コード
        "user_name_csv",                //担当者名csv
        "car_manage_number",            //統合車両管理ＮＯ 
        "customer_code",                //顧客コード 
        "customer_name_kanji",          //顧客名 
        "customer_name_kata",           //顧客カナ 
        "gensen_code_name",             //源泉 
        "customer_category_code_name",  //顧客分類 
        "customer_postal_code",         //自宅郵便番号 
        "customer_address",             //自宅住所 
        "customer_tel",                 //自宅TEL 
        "mobile_tel",                   //携帯TEL
        "customer_office_tel",          //勤務先TEL 
        "car_name",                     //車名 
        "car_year_type",                //年式 
        "first_regist_date_ym",         //初度登録年月　YYYYMM 
        "cust_reg_date",                //登録日　YYYY/MM/DD 
        "car_base_number",              //＊車両基本登録No 
        "car_model",                    //型式 
        "car_service_code",             //サービス通称名 
        "car_frame_number",             //フレームＮＯ 
        "car_buy_type",                 //自販他販区分 
        "car_new_old_kbn_name",         //新中区分 
        "syaken_times",                 //車検回数 
        "syaken_next_date",             //次回車検日　YYYY/MM/DD 
        "customer_insurance_type",      //任意保険加入区分 
        "customer_insurance_company",   //任意保険会社 
        "customer_insurance_end_date",  //任意保険終期　YYYY/MM/DD 
        "first_shiharai_date_ym",       //初回支払年月　YYYYMM 
        "credit_hensaihouhou",          //クレジット返済方法 
        "credit_hensaihouhou_name",     //クレジット返済方法名称 
        "keisan_housiki_kbn",           //計算方式 
        "credit_card_select_kbn",       //クレジット・カード選択区分 
        "memo_syubetsu",                //メモ種別 
        "shiharai_count",               //支払回数 
        "sueoki_zankaritsu",            //据置・残価率 
        "last_shiharaikin",             //最終回支払金 
        "customer_kouryaku_flg",        //攻略対象車 
        "customer_dm_flg",              //Ｄ／Ｍ不要区分 
        "customer_kojin_hojin_flg",     //個人法人コード 
        "car_type",                     //車種 
        "abc_zone",                     //ABCゾーン 
        "htc_number",                   //HTC会員番号 
        "htc_car",                      //HTC契約車両 
        "ciao_course",                  //チャオコース/プラン 
        "ciao_end_date"                 //会員証有効期間終期 
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
        
        Customer::updateOrCreate(
            [
                'car_manage_number' => $values['car_manage_number']
            ],
            $values
        );
        
    }
    
    ###########################
    ## スコープメソッド(Join文)
    ###########################
    
    /**
     * 拠点テーブルのJOIN
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function scopeJoinBase( $query ) {
//        $query = $query->leftJoin(
//                    "tb_base",
//                    function( $join ){
//                        $join->on( 'tb_customer.base_id', '=', 'tb_base.id' )
//                             ->whereNull( 'tb_base.deleted_at' );
//                    }
//                );
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
                $join->on("tb_customer.user_id","=","tb_user_account.id");
            });
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
                        $join->on( 'tb_customer.user_id', '=', 'tb_user_account.id' )
                             ->whereNull( 'tb_user_account.deleted_at' );
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
    public function scopeJoinSalesAll( $query ) {
        $query = $query->leftJoin(
            'tb_user_account',
            function( $join ){
                $join->on( 'tb_customer.user_id', '=', 'tb_user_account.id' );
                    //->whereNull( 'tb_user_account.deleted_at' );
            }
        );

        //dd( $query->toSql() );
        return $query;
    }

    /**
     * チャオテーブルとJoinするスコープメソッド     要変更（いらない）
     *
     * @param unknown $query
     */
    //　条件式要約
    //　ciao_course = 'チャオＳＳ'　且つ　syaken_next_date < ciao_end_date
    //　ciao_course != 'チャオＳＳ'　且つ　syaken_next_date <= ciao_end_date
    
    
    public function scopeJoinCiao( $query ){
//        $query = $query->leftJoin(
//                    'v_ciao',
//                    function( $join ) {
//                    $join->on( 'tb_customer.customer_code', '=', 'v_ciao.ciao_customer_code' )
//                         ->on( 'tb_customer.car_manage_number', '=', 'v_ciao.ciao_car_manage_number' )
//                         ->on(
//                                DB::raw( "(" ),
//                                "(
//                                    v_ciao.ciao_course = 'チャオＳＳ' AND
//                                    tb_customer.syaken_next_date < v_ciao.ciao_end_date
//                                )
//                                OR
//                                (
//                                    v_ciao.ciao_course != 'チャオＳＳ' AND
//                                    tb_customer.syaken_next_date <= v_ciao.ciao_end_date
//                                )",
//                                DB::raw( ")" )
//                         )
//                        ->wherenull( 'v_ciao.deleted_at' );
//                });
//        return "";
        $query = $query->leftJoin(
                    'tb_target_cars',
                    function( $join ) {
                    $join->on( 'tb_target_cars.tgc_car_manage_number', '=', 'tb_customer.car_manage_number' )
                        ->wherenull( 'tb_customer.deleted_at' );
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
                    $join->on( 'tb_customer.customer_code', '=', 'tb_customer_umu.umu_customer_code' )
                        ->on( 'tb_customer.car_manage_number', '=', 'tb_customer_umu.umu_car_manage_number' )
                        ->wherenull( 'tb_customer_umu.deleted_at' );
                });

        //dd( $query->toSql() );
        return $query;
    }

    /**
     * tb_manage_infoテーブルとJoinするスコープメソッド
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

    //2022/03/21 add new join
    /**
     * tb_manage_infoテーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinInfoSyaken( $query ) {
        $query = $query->leftJoin(
            'tb_manage_info',
            function( $join ) {
                $join->on( 'tb_customer.customer_code', '=', 'tb_manage_info.mi_customer_code' )
                    ->on('tb_customer.car_manage_number', '=' , 'tb_manage_info.mi_car_manage_number')
                ->where( 'tb_manage_info.mi_inspection_id', '=', 4 );
            });

        //dd( $query->toSql() );
        return $query;
    }

    /**
     * tb_htcテーブルとJoinするスコープメソッド
     *
     * @param unknown $query
     */
    public function scopeJoinHtc( $query ) {
        $query = $query->leftJoin(
                    'tb_htc',
                    function( $join ) {
                    $join->on( 'tb_customer.customer_code', '=', 'tb_htc.htc_customer_number' )
                    ->wherenull( 'tb_htc.deleted_at' );
                });

        //dd( $query->toSql() );
        return $query;
    }


    ###########################
    ## スコープメソッド(条件式)
    ###########################
    
    /**
     * 車検回数を検索するスコープメソッド
     * @param  [type] $query [description]
     * @param  string $value 車検回数に入力された値
     * @return query
     */
    public function scopeWhereSyakenTimes( $query, $value ) {
        // 全ての半角全角スペースを除去
        $value = preg_replace( "/( |　)/", "", $value );
        
        // 空なら何もしない
        if( empty( $value ) ){
            return $query;
        }

        // 全角数字を半角にする
        $value = mb_convert_kana( $value, 'n' );

        return $query->where( 'syaken_times', $value );
    }
  
    ###########################
    ## Csv Command
    ###########################
    
    /**
     * 顧客コードで検索
     *
     * @return Collection
     */
    public static function findByCustomerCode( $customerCode ) {
        return Customer::where( 'tb_customer.customer_code', 'like', "%{$customerCode}%")
                       ->first();
    }
    
    /**
     * 顧客コードで一致検索
     *
     * @return Collection
     */
    public static function findByCustomerCodeMatch( $customerCode ) {
        // 数値に値を指定して取得
        $customerCode = intval( $customerCode );

        return Customer::where( 'tb_customer.customer_code', '=', "{$customerCode}")
        ->first();
    }

    /**
     * 統合車両管理Noで一致検索
     *
     * @return Collection
     */
    public static function findByCarManageNumberMatch( $car_manage_number ) {
        return Customer::where( 'tb_customer.car_manage_number', '=', "{$car_manage_number}")
        ->first();
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
            ->whereLike( 'tb_user_account.base_code', $requestObj->base_code )
            // 担当者コード
            //->whereLike( 'tb_customer.user_id', $requestObj->user_id )
            // 担当者名の有無
            ->whereUmuNullCheckbox( 'tb_user_account.user_name', $requestObj->user_flg )
            // 顧客コード
            ->whereLike( 'customer_code', $requestObj->customer_code )
            // 顧客名
            ->whereLike( 'customer_name_kanji', $requestObj->customer_name )
            //車両基本Ｎｏ
            ->whereLike( 'car_base_number', $requestObj->car_base_number )
            //車種名
            ->whereLike( 'car_name', $requestObj->car_name )
            // 保険
            ->whereUmuNullCheckbox( 'customer_insurance_end_date', $requestObj->insu_flg )
            // 保険区分
            ->whereMatch( 'customer_insurance_type', $requestObj->insu_type )
            // クレ
            ->whereUmuNullCheckbox( 'first_shiharai_date_ym', $requestObj->cre_flg )
            // クレ区分
            ->whereMatchIn( 'credit_hensaihouhou_name', $requestObj->credit_type )
            // 車点検月
            ->wherePeriodNormal( DB::raw("to_char( syaken_next_date, 'yyyymm')"), $requestObj->inspection_ym_from, $requestObj->inspection_ym_to )
            // チャオ   要変更
            //->whereUmuNullCheckbox( 'v_ciao.ciao_course', $requestObj->ciao )
            // ABC      要変更
            // ->whereAbcCheckbox('tb_abc.abc_abc', $requestObj->abc);
            // ACB
            ->whereAbcCheckbox( 'abc_zone', $requestObj->abc )
            // チャオ
            ->whereUmuNullCheckbox( 'ciao_course', $requestObj->ciao )
            // HTCログイン
                // 20200318 update where
//            ->whereUmuCheckbox('tb_htc.htc_login_status', $requestObj->htc_login_status);
            ->whereUmuCheckbox('tb_manage_info.mi_htc_login_flg', $requestObj->mi_htc_login_flg);
            // 任意保険終期
            if(!empty($requestObj->insu_end_date)){
                $query = $query->where(DB::raw("to_char( customer_insurance_end_date, 'yyyymm')"), '=', $requestObj->insu_end_date);
            }

        // 退職者の場合
        if($requestObj->user_id == Constants::CONS_TAISHOKUSHA_CODE){
            $query = $query->WhereUmuNull( 'tb_user_account.deleted_at', '1' ); // deleted_at is not null
        }else{
            $query = $query->whereMatch( 'tb_user_account.id', $requestObj->user_id );
        }

        return $query;
    }

}
