<?php

namespace App\Commands\Extract;

use App\Models\TargetCars;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;


class ExtractIkouCommand extends Command implements ShouldBeQueued{

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
        
        // 最新意向の更新
        TargetCars::mergeIkou();
    }

}