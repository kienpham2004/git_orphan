<?php

namespace App\Commands\Honsya\Log;

use App\Commands\Command;
use App\Models\LogMonth;
use Illuminate\Support\Facades\Storage;

/**
 * ログ月一覧を取得するコマンド
 *
 * @author hung_hkn
 */
class ListCommand extends Command{

    /**
     * コンストラクタ
     * @param [type]  $sort       [description]
     * @param [type]  $requestObj [description]
     */
    public function __construct( $sort, $requestObj ){
        $this->sort = $sort;
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        return LogMonth::scopeGetAll();
    }

}
