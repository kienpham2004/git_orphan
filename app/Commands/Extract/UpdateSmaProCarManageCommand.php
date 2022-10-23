<?php

namespace App\Commands\Extract;

use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 主に車点検リスト用
 * tb_contactだと同じ月に何件か接触をしている可能性があるため、
 * 1件だけを取得
 */
class UpdateSmaProCarManageCommand extends Command implements ShouldBeQueued{

    /**
     * コンストラクタ
     */
    public function __construct(){
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 対象月の最新接触一覧を取得
        $smaProValues = $this->setCarManageNumber();
    }

    /**
     * 対象月の最新接触一覧を取得
     * @return [type] [description]
     */
    private function setCarManageNumber(){
        // 月の指定の箇所を見直す
        $sql = "    UPDATE
                        tb_smart_pro
                    SET
                        smart_car_manage_number = tb_customer.car_manage_number
                    FROM
                        tb_customer
                    WHERE
                        tb_customer.car_base_number = tb_smart_pro.smart_car_base_number AND
                        tb_customer.car_model = tb_smart_pro.smart_nintei_type ";

        return \DB::statement( $sql );
    }
}
