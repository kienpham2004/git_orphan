<?php

namespace App\Commands\Extract;

use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use App\Lib\Util\Constants;

/**
 * 主に車点検リスト用
 * tb_contactだと同じ月に何件か接触をしている可能性があるため、
 * 1件だけを取得
 */
class UpdateFlagStatusCommand extends Command implements ShouldBeQueued{

    protected $batchType;

    /**
     * コンストラクタ
     */
    public function __construct($batchType){
        $this->batchType = $batchType;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        if ($this->batchType == Constants::SHORT_BATCH) {
            // 4.4 tb_target_carsのtgc_syaken_jisshi_flgを追加する
            $this->setSyakenJisshiFlg();
            $this->setHtcFlg();
        }
    }

    /**
     * 4.4 tb_target_carsのtgc_syaken_jisshi_flgを追加する
     * ShortBatch
     * @return [type] [description]
     */
    private function setSyakenJisshiFlg( ){
        // tgc_syaken_jisshi_flgを更新する
        $sql = "UPDATE tb_target_cars
                SET
                    tgc_syaken_jisshi_flg =
                        (CASE
                            WHEN tgc_syaken_times =1 THEN 1
                            WHEN tgc_sj_shukka_date is not null THEN 2
                            ELSE 3
                        END)
                --WHERE
                    --to_char(tb_target_cars.updated_at, 'yyyymm') = to_char(current_date, 'yyyymm')
                    ";

        return \DB::statement( $sql );
    }

    /**
     * Update mi_htc_login_flg tb_manage_info
     */
    private function setHtcFlg()
    {
        $sql = "    
                WITH tmp_htc AS (
                    SELECT max(info.id) as info_id,
                           max(htc.htc_login_status) AS htc_login_status,
                           htc.htc_customer_number as htc_customer_number
                    FROM tb_htc htc
                    INNER JOIN tb_manage_info info
                         ON info.mi_customer_code::text = htc.htc_customer_number::text
                    GROUP BY htc.htc_customer_number
                )
                UPDATE tb_manage_info
                    SET mi_htc_login_flg = tmp_htc.htc_login_status
                FROM tmp_htc
                WHERE mi_customer_code = tmp_htc.htc_customer_number
                  AND id = tmp_htc.info_id ";

        return \DB::statement($sql);
    }
}
