<?php

namespace App\Console\Commands;

use App\Commands\Extract\ExtractIkouCommand;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;

class ExtractIkou extends Command {

    use DispatchesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:ExtractIkou';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '意向テーブルからの最新意向データ更新コマンド';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment(date('Y-m-d H:i:s') ." - Start extract ikou to target cars latest ikou.");
        $this->dispatch(
            new ExtractIkouCommand()
        );
        $this->comment(date('Y-m-d H:i:s') ." - End extract ikou to target cars latest ikou");
        $this->comment("");

    }
}
