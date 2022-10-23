<?php

namespace App\Original\Observer;

use App\Lib\Util\DateUtil;
use App\Original\Observer\ModelsObserver;
use App\Lib\Observer\tModelObserver;

class InfoModelObserver extends ModelsObserver {

    use tModelObserver;

    /**
     * 登録前処理
     *
     * @param unknown $model
     */
    public function creating($model) {
        $this->defaultSetting($model);
        $this->injectValueCaseCreating($model);
    }

    /**
     * 更新前処理
     *
     * @param unknown $model
     */
    public function updating($model) {
        $this->defaultSetting($model);
        $this->injectValueCaseUpdating($model);
    }

    /**
     * セーブ前処理
     *
     * @param unknown $model
     */
    public function saving($model) {
        $this->defaultSetting($model);
        $this->injectValueCaseSaving($model);
    }

    /**
     * 初期値設定
     *
     * @param unknown $model
     */
    private function defaultSetting(&$model) {
        \Log::debug($model);
        if(empty($model->info_view_flg)) {
            $model->info_view_flg = 0;
        }

        if(empty($model->info_view_date_min)) {
            $model->info_view_date_min = DateUtil::currentFirstDay();
        }

        if(empty($model->info_view_date_max)) {
            $model->info_view_date_max = DateUtil::currentLastDay();
        }
    }
}
