<?php

namespace App\Commands;

use Illuminate\Contracts\Bus\SelfHandling;

/**
 * コマンドを使用する際の親クラス
 *
 * @author yhatsutori
 *
 */
abstract class Command implements SelfHandling{

    /**
     * メインの処理
     */
    public function handle() {}
}
