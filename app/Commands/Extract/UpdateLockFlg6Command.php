<?php

namespace App\Commands\Extract;

use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * Description of UpdateLockFlg6Command
 * 6ヶ月ロックフラグの設定処理
 * @author orikasa
 */
class UpdateLockFlg6Command extends Command implements ShouldBeQueued{

    // 月末実行のフラグ
    protected $runUpdateFlg = 0;
    /**
     * コンストラクタ
     */
    public function __construct( $customerUpdateThis = 0 ){

        if( $customerUpdateThis == 1 ){
            echo PHP_EOL.date('Y-m-d H:i:s') ." - 当月に顧客データ更新がある場合 -> 更新日を当月と比較";

            $this->runUpdateFlg = 1;
            // 当月に顧客データ更新がある場合 -> 更新日を当月と比較
            $this->ymCurrent = date( "Ym", strtotime( date("Y-m-d") ) );

        }else { // 月末の場合
            echo PHP_EOL.date('Y-m-d H:i:s') ." - 当月に顧客データ更新が無い場合 -> 更新日を前月と比較";

            // 当月に顧客データ更新が無い場合 -> 更新日を前月と比較
            $this->ymCurrent = date("Ym", strtotime(date("Y-m-d") . "- 1month"));

            $sql = "select to_char(updated_at,'yyyymm') as update_max from tb_customer order by updated_at desc limit 1";
            $data = \DB::select($sql);
            $update_max = $data[0]->update_max;
            if (date("Ym") > $update_max) {
                $this->runUpdateFlg = 1;
            }
            echo PHP_EOL.date('Y-m-d H:i:s') ." - update_max : ".$update_max;
        }

        echo PHP_EOL.date('Y-m-d H:i:s') ." - runUpdateFlg : ".$this->runUpdateFlg.PHP_EOL;
        // 6ヶ月後
        $this->ym6after  = date( "Ym", strtotime( date("Y-m-d") . "+ 6month" ) );
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 実行条件チェック
        if ($this->runUpdateFlg == 0)
            return;
        $this->setCustomerUpdate();
        $this->setLockFlg6();
    }

    /**
     * 顧客データの
     */
    private function setCustomerUpdate(){
        
        $sql = "    UPDATE
                        tb_target_cars
                    SET
                        tgc_customer_update = tb_customer.updated_at
                    FROM
                        tb_customer
                    WHERE
                        tb_target_cars.tgc_syaken_next_date  = tb_customer.syaken_next_date AND
                        tb_target_cars.tgc_car_manage_number = tb_customer.car_manage_number AND
                        tb_target_cars.tgc_customer_code     = tb_customer.customer_code AND
                        (
                            tb_target_cars.tgc_customer_update IS NULL
                            OR (
                            tb_target_cars.tgc_customer_update IS NOT NULL AND
                            tb_target_cars.tgc_customer_update  !=  CAST(tb_customer.updated_at AS DATE)
                            )
                        )";
            return \DB::statement( $sql );
    }

    /**
     * 6ヶ月ロック対象データにフラグ付与
     */
    private function setLockFlg6(){
        $sql = "    UPDATE
                        tb_target_cars
                    SET
                        tgc_lock_flg6 = 1,tgc_lock_flg6_update = current_date
                    WHERE
                        tgc_customer_update IS NOT NULL AND
                        to_char(tgc_customer_update, 'yyyymm') < '{$this->ymCurrent}' AND
                        tgc_inspection_ym = '{$this->ym6after}' AND
                        tgc_lock_flg6 = 0 ";
            return \DB::statement( $sql );
    }
}
