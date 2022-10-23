<?php

namespace App\Console\Commands;

use App\Models\DmHanyou\CustomerDm;
use App\Commands\Extract\ExtractDmHanyouCommand;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;

class ExtractDmHanyou extends Command {

    use DispatchesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:ExtractDmHanyou';

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
    public function handle(){
        // データ件数があるかを確認する
        $count = CustomerDm::count();
        
        // 総数がない時に処理
        if( empty( $count ) == True ){
            try {
                $this->comment(date('Y-m-d H:i:s') ." - Start extract customer hanyou DMデータ.");
                $this->dispatch(
                    new ExtractDmHanyouCommand()
                );
                $this->comment(date('Y-m-d H:i:s') ." - End extract customer hanyou DMデータ.");
                $this->comment("");
                
            } catch (Exception $e) {
                //echo "例外キャッチ：", $e->getMessage(), "\n";
            }
            
        }

    }

}
