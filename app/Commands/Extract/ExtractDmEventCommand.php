<?php

namespace App\Commands\Extract;

use App\Lib\Util\DateUtil;
use App\Models\DmEvent\CustomerDm;
use App\Models\DmEvent\ExtractDB;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 汎用DMデータの抽出処理
 * tb_customer_dm
 *
 * @author yhatsutori
 *
 */
class ExtractDmEventCommand extends Command implements ShouldBeQueued{

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
        // データ件数があるかを確認する
        $count = CustomerDm::count();
        
        // 総数がない時に処理
        if( empty( $count ) == True ){
            ExtractDB::getTargetCustomer();
        }
        
    }

}
