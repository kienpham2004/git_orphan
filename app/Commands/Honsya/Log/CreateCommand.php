<?php

namespace App\Commands\Honsya\Log;

use App\Commands\Command;
use App\Models\LogMonth;

/**
 * ログ月を作成のコマンド
 *
 * @author hung_hkn
 */
class CreateCommand extends Command{

    /**
     * コンストラクタ
     * @param [type]  $log_ym       [description]
     */
    public function __construct( $log_ym){
        $this->log_ym = $log_ym;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        return LogMonth::updateOrCreate(
            [
                'lm_ym'        => $this->log_ym
            ],
            [
                'lm_file_name' => $this->log_ym .'.zip',
                'lm_ym'        => $this->log_ym
            ]
        );
    }

}
