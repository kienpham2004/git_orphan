<?php

namespace App\Console\Commands;

use App\Commands\Extract\ExtractDmSyakenCommand;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;

class ExtractDmSyaken extends Command {

    use DispatchesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:ExtractDmSyaken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DMデータ(車検)作成コマンド';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        // 発送確認月(～10日まで)の3ヶ月後が対象月
        $target_ym = date( "Ym", strtotime( date("Y-m-01"). "+3 month" ) );
        
        $this->comment(date('Y-m-d H:i:s') ." - Start extract customer DMデータ(車検).");
        $this->dispatch(
            new ExtractDmSyakenCommand($target_ym)
        );
        $this->comment(date('Y-m-d H:i:s') ." - End extract customer DMデータ(車検).");
        $this->comment("");

    }
}
