<?php

namespace App\Commands\Extract;

use App\Lib\Util\DateUtil;
use App\Models\Hoken\InsuranceDB;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 保険データの未接触敗戦データを調べる処理
 * tb_insurance
 *
 * @author yhatsutori
 *
 */
class ExtractInsuranceNoContactCommand extends Command implements ShouldBeQueued{

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
        // メモリの設定
        ini_set('memory_limit', '4096M');
        
        // 未接触敗戦者をさがして、未接触敗戦扱いにする
        InsuranceDB::setMisessyokuHaisenRecords();
            echo PHP_EOL.'class ----------------- ： '.__CLASS__;
            echo PHP_EOL.'line ------------------ ： '.__LINE__;
            echo PHP_EOL."終了後 ---------------- ： ".memory_get_usage() / (1024 * 1024) ."MB\n";
    }

}
