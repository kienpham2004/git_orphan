<?php

namespace App\Commands\Extract;

use App\Models\Hoken\InsuranceDB;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 保険データのデータを調べる処理
 * tb_insurance
 *
 * @author ルック
 *
 */
class ExtractInsuranceUpdateCommand extends Command implements ShouldBeQueued{

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
        ini_set('memory_limit', '1024M');

        // CSVインポートの時、ステータス更新を行う
        InsuranceDB::updateInsuranceStatus();
    }

}
