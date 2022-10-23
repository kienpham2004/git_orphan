<?php

namespace App\Commands\Extract;

use App\Models\Contact\ContactShodan;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 主に車点検リスト用
 * tb_contactだと同じ月に何件か接触をしている可能性があるため、
 * 1件だけを取得
 */
class ExtractContactShodanCommand extends Command implements ShouldBeQueued{

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
            ContactShodan::updateOrCreate(
                [
                    'ctcsho_customer_code' => $data['ctcsho_customer_code'],
                    'ctcsho_contact_ym' => $data['ctcsho_contact_ym'],
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
                                    (
                                        tb_contact_1.ctc_result_code = '50' OR tb_contact_1.ctc_result_code = '050' OR
                                        tb_contact_1.ctc_result_code = '51' OR tb_contact_1.ctc_result_code = '051' OR
                                        tb_contact_1.ctc_result_code = '52' OR tb_contact_1.ctc_result_code = '052' OR
                                        tb_contact_1.ctc_result_code = '53' OR tb_contact_1.ctc_result_code = '053' OR
                                        tb_contact_1.ctc_result_code = '54' OR tb_contact_1.ctc_result_code = '054' OR
                                        tb_contact_1.ctc_result_code = '55' OR tb_contact_1.ctc_result_code = '055'
                                    ) AND
                                    (
                                        (tb_contact_1.ctc_customer_code::text, tb_contact_1.ctc_contact_date) IN
                                        (
                                            SELECT
                                                tb_contact_2.ctc_customer_code,
                                                max(tb_contact_2.ctc_contact_date) AS max
                                            FROM
                                                tb_contact tb_contact_2
                                            WHERE
                                                (
                                                    tb_contact_2.ctc_result_code = '50' OR tb_contact_2.ctc_result_code = '050' OR
                                                    tb_contact_2.ctc_result_code = '51' OR tb_contact_2.ctc_result_code = '051' OR
                                                    tb_contact_2.ctc_result_code = '52' OR tb_contact_2.ctc_result_code = '052' OR
                                                    tb_contact_2.ctc_result_code = '53' OR tb_contact_2.ctc_result_code = '053' OR
                                                    tb_contact_2.ctc_result_code = '54' OR tb_contact_2.ctc_result_code = '054' OR
                                                    tb_contact_2.ctc_result_code = '55' OR tb_contact_2.ctc_result_code = '055'
                                                )                                                
                                            GROUP BY
                                                tb_contact_2.ctc_customer_code
                                        )
                                    )
                                GROUP BY
                                    tb_contact_1.ctc_customer_code
                            ) ctnw                    
                    )    
                    SELECT
                        ctc.ctc_user_id as ctcsho_user_id,
                        ctc.ctc_user_code_init as ctcsho_user_code_init,
                        ctc.ctc_customer_code as ctcsho_customer_code,
                        ctc.ctc_contact_date as ctcsho_contact_date,
                        ctc.ctc_contact_ym as ctcsho_contact_ym,
                        ctc.ctc_car_manage_number as ctcsho_car_manage_number,
                        trim( ctc.ctc_result_code ) as ctcsho_result_code,
                        ctc.ctc_result_name as ctcsho_result_name,
                        created_by,
                        updated_by

                    FROM
                        tb_contact ctc
                      
                    WHERE
                        ctc.ctc_contact_ym = '{$targetYm}' AND
                        ctc.deleted_at is null AND
                        (
                            ctc.ctc_result_code = '50' OR ctc.ctc_result_code = '050' OR
                            ctc.ctc_result_code = '51' OR ctc.ctc_result_code = '051' OR
                            ctc.ctc_result_code = '52' OR ctc.ctc_result_code = '052' OR
                            ctc.ctc_result_code = '53' OR ctc.ctc_result_code = '053' OR
                            ctc.ctc_result_code = '54' OR ctc.ctc_result_code = '054' OR
                            ctc.ctc_result_code = '55' OR ctc.ctc_result_code = '055'
                        ) AND
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