<?php

namespace App\Commands\Top;

use App\Models\Info;
use App\Commands\Command;

/**
 * TOPのお知らせ情報を取得
 * ※最大10件
 *
 * @author yhatsutori
 */
class FindInfoCommand extends Command{

    /**
     * コンストラクタ
     */
    public function __construct(){
        $this->sort = ['sort' => []];
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        $infos = Info::showInfoList();

        return $infos;
    }
    
}
