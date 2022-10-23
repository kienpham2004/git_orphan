<?php

namespace App\Console\Commands;

use App\Commands\Extract\ExtractTargetCarsCommand;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;

class ExtractTargetCars extends Command {

    use DispatchesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:ExtractTargetCars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '顧客ロックデータ作成コマンド';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment(date('Y-m-d H:i:s') ." - Start extract target cars 顧客データ.");
        $this->dispatch(
            new ExtractTargetCarsCommand()
        );
        $this->comment(date('Y-m-d H:i:s') ." - End extract target cars 顧客データ.");
        $this->comment("");

    }
}
