<?php

namespace App\Commands\TableCleaning;

use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use DB;

/**
 * 重複データを解消するコマンド
 */
class CustomerCommand extends Command implements ShouldBeQueued {
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        
        ini_set('memory_limit', '8196M');
    }
    
    /**
     * メインの処理
     */
    public function handle() {
        $sql = "delete from tb_customer
            using
            (
                select *
                from
                (
                    select row_number() over() as idx, * from 
                    (
                        select * from tb_customer
                        where
                        (car_manage_number) in
                        (
                            select car_manage_number from tb_customer
                            group by car_manage_number
                            having count(car_manage_number) > 1
                        )
                        order by car_manage_number, updated_at desc
                    ) ordered
                ) indexed
                where mod(idx, 2) = 0
            ) filtered
            where tb_customer.id = filtered.id";
        
        DB::statement( $sql );
    }
}
