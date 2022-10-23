<?php

namespace App\Original\Observer;

use App\Original\Observer\ModelsObserver;
use App\Lib\Observer\tModelObserver;

/**
 * 担当者用モデルオブザーバー
 * ・主に定型項目の処理
 *
 * @author yhatsutori
 *
 */
class UserAccountModelObserver extends ModelsObserver {

    use tModelObserver;

    /**
     * 登録前処理
     *
     * @param unknown $model
     */
    public function creating($model) {
        $model->password = \Hash::make($model->password);
        $this->injectValueCaseCreating($model);
    }

    /**
     * 更新前処理
     *
     * @param unknown $model
     */
    public function updating($model) {
        $model->user_password = $model->user_password;
        $model->password = \Hash::make($model->user_password);
        $this->injectValueCaseUpdating($model);
    }

    /**
     * セーブ前処理
     *
     * @param unknown $model
     */
    public function saving($model) {
        $this->injectValueCaseSaving($model);
    }
}
