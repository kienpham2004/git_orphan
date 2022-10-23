<?php

namespace App\Console\Commands;

use App\Commands\Extract\ExtractResultCarsCommand;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;

class ExtractResultCars extends Command {

    use DispatchesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:ExtractResultCars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '実績ロックデータ作成コマンド';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment(date('Y-m-d H:i:s') ." - Start extract result cars 実績データ.");
        $this->dispatch(
            new ExtractResultCarsCommand()
        );
        $this->comment(date('Y-m-d H:i:s') ." - End extract result cars 実績データ.");
        $this->comment("");

    }
}
