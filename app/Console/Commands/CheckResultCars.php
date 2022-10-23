<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;
use App\Commands\TableCleaning\ResultCarsNotFullCommand;

class CheckResultCars extends Command {

    use DispatchesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:check-result-cars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '第一段階のマネージインフォのダブっているレコードを解消するコマンド';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 重複データの削除対象をバックアップ
        $this->comment(date('Y-m-d H:i:s') ." - Start tb_result_carsの重複データ解消.");
        // 完全一致でないターゲットカーズの重複チェック
        $this->dispatch( new ResultCarsNotFullCommand(
            ResultCarsNotFullCommand::CHECKING_ALL
        ));
        $this->comment(date('Y-m-d H:i:s') ." - End tb_result_carsの重複データ解消.\n");
    }

}
