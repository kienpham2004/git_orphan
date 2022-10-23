<?php

namespace App\Commands\Extract;

use App\Models\Contact\ContactShijo;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 主に車点検リスト用
 * tb_contactだと同じ月に何件か接触をしている可能性があるため、
 * 1件だけを取得
 */
class ExtractContactShijoCommand extends Command implements ShouldBeQueued{

    /**
     * コンストラクタ
     * @param string $targetYm 対象月
     */
    public function __construct( $targetYm="" ){
        // 対象月の取得
        $this->targetYm = orgIsset( $targetYm, date('Ym') );
    }
    
    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 対象月の最新接触一覧を取得
        $contactValues = $this->getContactValues( $this->targetYm );

        /**
         * 最新の接触情報を登録、もしくは更新する処理
         * 次の値が同じ場合に更新します
         * ・統合車両管理No
         * ・接触月
         */
        foreach( $contactValues as $data ){
            // $dataがarrayではなくobjectなのでキャストしてます。
            $data = (array)$data;

            // 統合車両管理Noと接触月が同じものは上書き
            ContactShijo::updateOrCreate(
                [
                    'ctcshi_customer_code' => $data['ctcshi_customer_code'],
                    'ctcshi_contact_ym' => $data['ctcshi_contact_ym'],
                ],
                $data
            );
        }
    }

    /**
     * 対象月の最新接触一覧を取得
     * @param  string $targetYm [description]
     * @return [type]             [description]
     */
    private function getContactValues( $targetYm="" ){
        // 対象月が空の時は当月を指定
        if( empty( $targetYm ) == True ){
            $targetYm = date('Ym');
        }
        
        // 月の指定の箇所を見直す
        $sql = "    
                    WITH tmp_ctnw AS (
                            SELECT
                                ctnw.ctc_customer_code,
                                ctnw.id
                            FROM
                            (
                                SELECT
                                    tb_contact_1.ctc_customer_code,
                                    max(tb_contact_1.id) AS id
                                FROM
                                    tb_contact tb_contact_1
                                WHERE
                                    ( tb_contact_1.ctc_result_code = '42' OR tb_contact_1.ctc_result_code = '042' ) AND
                                    (
                                        (tb_contact_1.ctc_customer_code::text, tb_contact_1.ctc_contact_date) IN
                                        (
                                            SELECT
                                                tb_contact_2.ctc_customer_code,
                                                max(tb_contact_2.ctc_contact_date) AS max
                                            FROM
                                                tb_contact tb_contact_2
                                            WHERE
                                                ( tb_contact_2.ctc_result_code = '42' OR tb_contact_2.ctc_result_code = '042' )    
                                            GROUP BY
                                                tb_contact_2.ctc_customer_code
                                        )
                                    )
                                GROUP BY
                                    tb_contact_1.ctc_customer_code
                            ) ctnw                    
                    )
                    SELECT
                        ctc.ctc_user_id as ctcshi_user_id,
                        ctc.ctc_user_code_init as ctcshi_user_code_init,
                        ctc.ctc_customer_code as ctcshi_customer_code,
                        ctc.ctc_contact_date as ctcshi_contact_date,
                        ctc.ctc_contact_ym as ctcshi_contact_ym,
                        ctc.ctc_car_manage_number as ctcshi_car_manage_number,
                        trim( ctc.ctc_result_code ) as ctcshi_result_code,
                        ctc.ctc_result_name as ctcshi_result_name,
                        created_by,
                        updated_by

                    FROM
                        tb_contact ctc
                      
                    WHERE
                        ctc.ctc_contact_ym = '{$targetYm}' AND
                        ctc.deleted_at is null AND
                        ( ctc.ctc_result_code = '42' OR ctc.ctc_result_code = '042' ) AND
                        EXISTS
                        (
                            SELECT *
                            FROM tmp_ctnw ctnw 
                            WHERE
                                ctnw.ctc_customer_code::text = ctc.ctc_customer_code::text AND
                                ctnw.id = ctc.id
                        )
                        
                        order by ctc_customer_code ";

        return \DB::select( $sql );
    }
}