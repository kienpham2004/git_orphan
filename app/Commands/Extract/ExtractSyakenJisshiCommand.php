<?php

namespace App\Commands\Extract;

use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 車検実施リストのcsv取り込み処理
 */
class ExtractSyakenJisshiCommand extends Command implements ShouldBeQueued {

    /**
     * コンストラクタ
     */
    public function __construct( $targetYm=null ) {

    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle() {
        //データ更新
        $this->setTargetCarShukkaDate();
    }

    /**
     * tb_target_carsの出荷報告日のデータを更新する
     * @return [type] [description]
     */
    private function setTargetCarShukkaDate(){
        // tgc_sj_shukka_dateを更新する
        $sql = "UPDATE tb_target_cars AS ttc		
                SET tgc_sj_shukka_date = tsj.sj_shukka_date	
                FROM
                    tb_syaken_jisshi AS tsj
                WHERE
                    tsj.sj_car_manage_number = ttc.tgc_car_manage_number AND
                    tsj.sj_customer_code = ttc.tgc_customer_code AND
                    -- tsj.sj_shukka_date is not null AND  -- 入力済の場合、次回NULLがある。
                    to_char(tsj.updated_at, 'yyyymmdd') = to_char(current_date, 'yyyymmdd')";

        return \DB::statement( $sql );
    }
}