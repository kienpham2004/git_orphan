<?php

namespace App\Original\Observer;

use App\Original\Observer\ModelsObserver;
use App\Lib\Observer\tModelObserver;

class BaseModelObserver extends ModelsObserver {

    use tModelObserver;

    public function creating($model) {
        $this->defaultSetting($model);
        $this->injectValueCaseCreating($model);
    }

    public function updating($model) {
        $this->defaultSetting($model);
        $this->injectValueCaseUpdating($model);
    }

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
        if(empty($model->block_base_code)) {
            $model->block_base_code = '1';
        }
    }
}
