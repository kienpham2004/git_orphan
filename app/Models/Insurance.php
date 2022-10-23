<?php

namespace App\Models;

use App\Lib\Util\DateUtil;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\Lib\Util\Constants;

/**
 * 保険ロックデータモデル
 *
 */
class Insurance extends AbstractModel {
    
    use SoftDeletes;

    // テーブル名
    protected $table = 'tb_insurance';

    // 変更可能なカラム
    protected $fillable = [
        "insu_inspection_target_ym",
        "insu_inspection_ym",
        "insu_jisya_tasya",
        //"base_id",
        "insu_base_code_init",
        "insu_base_name_csv",
        "insu_insurance_end_date",
        "insu_insurance_start_date",
        "insu_user_id",
        "insu_user_code_init",
//        "insu_csv_user_id",
        "insu_user_name_csv",
        "insu_kikan",
        "insu_syumoku_code",
        "insu_syumoku",
        "insu_company_code",
        "insu_company_name",
        "insu_uketuke_kbn_code",
        "insu_uketuke_kbn",
        "insu_customer_name",
        "insu_tel_no",
        "insu_syoken_number",
        "insu_syadai_number",
        "insu_car_base_number",
        "insu_syaryo_type",
        "insu_jisseki_hokenryo",
        "insu_daisya",
        "insu_tokyu",
        "insu_jinsin_syogai",
        "insu_cashless_flg",
        "insu_kanyu_dairiten",
        "insu_syoken_sindan_date",
        "insu_keiyaku_kekka",
        "insu_syoken_extract_number",
        "insu_syoken_suishin_1",
        "insu_syoken_suishin_2",
        "insu_syoken_source",
        "insu_status",
        "insu_action",
        "insu_memo",
        "insu_contact_plan_date",
        "insu_contact_date",
        "insu_contact_mail_tuika",
        "insu_contact_jigu",
        "insu_contact_taisyo",
        "insu_contact_taisyo_name",
        "insu_contact_syaryo_type",
        "insu_contact_daisya",
        "insu_status_gensen",

        "insu_contact_period",
        "insu_contact_daigae_car_name",
        "insu_contact_keijyo_ym",
        "insu_updated_history",
        "insu_kakutoku_company_name",
        
        "insu_add_tetsuduki_date",
        "insu_add_tetsuduki_detail",
        "insu_add_keijyo_date",
        "insu_add_keijyo_ym",

        "insu_status_gensen_detail",
        "insu_staff_info_toss",
        "insu_toss_staff_name",
        "insu_pair_fleet",

        "created_at",
        "updated_at",
        "deleted_at",
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
        //\Log::debug( $values );
        Insurance::updateOrCreate(
            [
                'insu_inspection_target_ym' => $values['insu_inspection_target_ym'],
                'insu_insurance_end_date' => $values['insu_insurance_end_date'],
                'insu_syoken_number' => $values['insu_syoken_number']
            ],
            $values
        );
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
                        base.id = account.base_id
                        --AND base.deleted_at IS NULL -- 退職者も含める        
              ) as tb_user_account"),function($join){
                $join->on("tb_insurance.insu_user_id","=","tb_user_account.id");
            });

        //dd( $query->toSql() );
        return $query;
    }
    
    /**
     * 担当者テーブルとJoinするスコープメソッド
     * 担当者コードがない為コメントアウト
     * @param unknown $query
     */
    public function scopeJoinSales( $query ) {
        $query = $query->leftJoin(
                    'tb_user_account',
                    function( $join ){
                        $join->on( 'tb_insurance.insu_user_id', '=', 'tb_user_account.id' )
                             ->whereNull( 'tb_user_account.deleted_at' );
                    }
                );

        //dd( $query->toSql() );
        return $query;
    }

    /**
     * 担当者テーブルとJoinするスコープメソッド
     * 担当者コードがない為コメントアウト
     * @param unknown $query
     */
    public function scopeJoinSalesAll( $query ) {
        $query = $query->leftJoin(
            'tb_user_account',
            function( $join ){
                $join->on( 'tb_insurance.insu_user_id', '=', 'tb_user_account.id' );
                    //->whereNull( 'tb_user_account.deleted_at' );
            }
        );

        //dd( $query->toSql() );
        return $query;
    }

    
 	###########################
    ## Hoken List Commands
    ###########################
    
    /**
     * 検索条件を指定するメソッド
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    public function scopeWhereRequest( $query, $requestObj, $insu_jisya_tasya ){
        $query = $query
                //契約満了月
                //->wherePeriodNormal( 'tb_insurance.insu_inspection_ym', $requestObj->insu_inspection_ym_from, $requestObj->insu_inspection_ym_to  )
                //接触日
                ->wherePeriodNormal( 'tb_insurance.insu_contact_date', $requestObj->insu_contact_date_from, $requestObj->insu_contact_date_to  )
                // 拠点コード
                ->whereMatch( 'tb_user_account.base_code', $requestObj->base_code )
                // 担当者名
                //->whereLike( 'insu_user_name', $requestObj->user_name )
                // 担当者名
                //->whereMatch( 'insu_user_id', $requestObj->user_id )
                // 契約者名
                ->whereLike( 'insu_customer_name', $requestObj->insu_customer_name )
                // 保険会社
                ->whereLike( 'insu_company_name', $requestObj->insu_company_name )
                // 証券番号
                //->whereLike( 'insu_syoken_number', $requestObj->insu_syoken_number )
                // 特記1
                ->whereLike( 'insu_syoken_suishin_1', $requestObj->insu_syoken_suishin_1 )
                // 特記2
                ->whereLike( 'insu_syoken_suishin_2', $requestObj->insu_syoken_suishin_2 );

        // 未接触の時は特別検索
        if( $requestObj->insu_status == '0' ){
            $query = $query->whereNull( 'insu_status' ); // 意向結果
        }else{// 意向結果
            $query = $query->whereMatch( 'insu_status', $requestObj->insu_status );
        }

        // 退職者の場合
        if($requestObj->user_id == Constants::CONS_TAISHOKUSHA_CODE){
            $query = $query->WhereUmuNull( 'tb_user_account.deleted_at', '1' ); // deleted_at is not null
        }else{
            $query = $query->whereMatch( 'tb_user_account.id', $requestObj->user_id );
        }

        // 自社・他社・追加
        //->whereMatch( 'insu_jisya_tasya', $requestObj->insu_jisya_tasya )
        // 活動内容
        //->whereMatch( 'insu_action', $requestObj->insu_action )
        // 治具
        //->whereMatch( 'insu_contact_jigu', $requestObj->insu_contact_jigu )
        // 車両保険付帯
        //->whereMatch( 'insu_contact_syaryo_type', $requestObj->insu_contact_syaryo_type );

        // 対象年月の指定があれば
        if( !empty( $requestObj->insu_inspection_target_ym_from ) == True ){
            $query = $query
                // 対象年月
                ->where( 'tb_insurance.insu_inspection_target_ym', '=', $requestObj->insu_inspection_target_ym_from );
        }

        // 満期年月の指定があれば
        if( !empty( $requestObj->insu_inspection_ym_from ) == True ){
            if( $insu_jisya_tasya == "他社分" ){
                $query = $query
                    // 満期年月
                    ->whereRaw( 
                        " -- 計上月か、保険対象月を対象とする
                        (
                            (
                                -- 計上月が空でないときは、計上月
                                tb_insurance.insu_contact_keijyo_ym IS NOT NULL AND
                                tb_insurance.insu_contact_keijyo_ym = '{$requestObj->insu_inspection_ym_from}'
                            ) OR (
                                -- 計上月が空のときは、計上月
                                tb_insurance.insu_contact_keijyo_ym IS NULL AND
                                tb_insurance.insu_inspection_ym = '{$requestObj->insu_inspection_ym_from}'
                            ) 
                        ) "
                     );
            }else{// 満期年月
                $query = $query->where( 'tb_insurance.insu_inspection_ym', '=', $requestObj->insu_inspection_ym_from );
            }
            
        }
        
        /*
        // 0の値もあるので、特別対応
        if( $requestObj->insu_contact_daisya != "" ){
            // 代車特約付帯
            $query = $query->where( 'insu_contact_daisya', '=', $requestObj->insu_contact_daisya );
        }
        */
        
        if( $insu_jisya_tasya == "自社分" ){
            // 自社分の値を取得
            $jisyaTasyaSql = " insu_jisya_tasya = '自社分' ";
            $query = $query->whereRaw( \DB::raw( $jisyaTasyaSql ) );

        }elseif( $insu_jisya_tasya == "他社分" ){
            // 他社分・追加分の値を取得
            $jisyaTasyaSql = "  (
                                    insu_jisya_tasya = '他社分' OR
                                    insu_jisya_tasya = '純新規'
                                )
                            ";
            $query = $query->whereRaw( \DB::raw( $jisyaTasyaSql ) );
        }
        return $query;
    }
    
    /**
     * 検索条件を指定するメソッド(本社担当の処理)
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    /*
    public function scopeWhereHonsyaRequest( $query, $requestObj, $insu_jisya_tasya ){
        $query = $query
            // 拠点コード
            ->whereLike( 'insu_base_code', $requestObj->base_code )
            // 担当者名
            ->whereLike( 'insu_user_name', $requestObj->user_name )
            // 契約者名
            ->whereLike( 'insu_customer_name', $requestObj->insu_customer_name )
            // 保険会社
            ->whereLike( 'insu_company_name', $requestObj->insu_company_name )
            // 証券番号
            //->whereLike( 'insu_syoken_number', $requestObj->insu_syoken_number )
            // 特記1
            ->whereLike( 'insu_syoken_suishin_1', $requestObj->insu_syoken_suishin_1 )
            // 特記2
            ->whereLike( 'insu_syoken_suishin_2', $requestObj->insu_syoken_suishin_2 )
            // 意向結果
            ->whereMatch( 'insu_status', $requestObj->insu_status );
            // 手続き完了日のある日付の一覧を表示(処理を条件付き「情報トスの指定なし」の場合に変更)
            //->whereNotNull( 'insu_add_tetsuduki_date' );

        // 本社処理のある時
        if( $requestObj->insu_add_keijyo_ym == "1" ){
            // 計上処理日のある日付の一覧を表示
            $query = $query->whereNotNull( 'insu_add_keijyo_date' );
            
        }else{
            // 計上処理日のない日付の一覧を表示
            $query = $query->whereNull( 'insu_add_keijyo_date' );
            
        }
        
        // 対象年月の指定があれば
        if( !empty( $requestObj->insu_inspection_target_ym_from ) == True ){
            $query = $query
                // 対象年月
                ->where( 'tb_insurance.insu_inspection_target_ym', '=', $requestObj->insu_inspection_target_ym_from );
        }
        
        // 満期年月の指定があれば
        if( !empty( $requestObj->insu_inspection_ym_from ) == True ){
            $query = $query
                // 満期年月
                ->where( 'tb_insurance.insu_inspection_ym', '=', $requestObj->insu_inspection_ym_from );
        }

        // 手続き完了日のある日付の一覧を表示
        $query = $query->whereNotNull( 'insu_add_tetsuduki_date' );

        // Memo: 自社分も追加するという指示があり
        // 他社分・追加分の値を取得
//        $jisyaTasyaSql = "  (
//                                insu_jisya_tasya = '他社分' OR
//                                insu_jisya_tasya = '純新規'
//                            )
//                        ";
//
//        $query = $query->whereRaw( \DB::raw( $jisyaTasyaSql ) );
        
        return $query;
    }
    */

    /**
     * 検索条件を指定するメソッド(情報トス実績)
     * @param  [type] $query      [description]
     * @param  [type] $requestObj [description]
     * @return [type]             [description]
     */
    /*
    public function scopeWhereInfoTossRequest( $query, $requestObj, $insu_jisya_tasya ){
        $query = $query
            // 拠点コード
            ->whereLike( 'insu_base_code', $requestObj->base_code )
            // 担当者名
            ->whereLike( 'insu_user_name', $requestObj->user_name )
            // 契約者名
            ->whereLike( 'insu_customer_name', $requestObj->insu_customer_name )
            // 証券番号
            ->whereLike( 'insu_syoken_number', $requestObj->insu_syoken_number )
            // 保険会社
            ->whereLike( 'insu_company_name', $requestObj->insu_company_name )
            // 情報トス スタッフ名
            ->whereLike( 'insu_toss_staff_name', $requestObj->insu_toss_staff_name )
            // 獲得源泉
            ->whereMatch( 'insu_status_gensen', $requestObj->insu_status_gensen )
            // 意向結果
            ->whereMatch( 'insu_status', $requestObj->insu_status );
            // 手続き完了日のある日付の一覧を表示(処理を条件付き「情報トスの指定なし」の場合に変更)
            //->whereNotNull( 'insu_add_tetsuduki_date' );
        
        // 対象年月の指定があれば
        if( !empty( $requestObj->insu_inspection_target_ym_from ) == True ){
            $query = $query
                // 対象年月
                ->where( 'tb_insurance.insu_inspection_target_ym', '=', $requestObj->insu_inspection_target_ym_from );
        }
        
        // 満期年月の指定があれば
        if( !empty( $requestObj->insu_inspection_ym_from ) == True ){
            $query = $query
                // 満期年月
                ->where( 'tb_insurance.insu_inspection_ym', '=', $requestObj->insu_inspection_ym_from );
        }
        
        // スタッフからの情報トス
        $query = $query->where( 'tb_insurance.insu_staff_info_toss', '=', 1 );
        
        // Memo: 自社分も追加するという指示があり
        // 他社分・追加分の値を取得
//        $jisyaTasyaSql = "  (
//                                insu_jisya_tasya = '他社分' OR
//                                insu_jisya_tasya = '純新規'
//                            )
//                        ";
//
//        $query = $query->whereRaw( \DB::raw( $jisyaTasyaSql ) );
        
        return $query;
    }
    */

}
