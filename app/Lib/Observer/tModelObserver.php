<?php

namespace App\Lib\Observer;

use App\Lib\Util\DateUtil;
use Auth;

/**
 * ORMでinsertやupdateする前後にフックして
 * 何かしらの処理を指定
 * creating　→　インサート前
 * created　→　インサート後
 * updating　→　アップデート前
 * updated　→　アップデート後
 * saving　→　インサート前及びアップデート前
 * saved　→　インサート後及びアップデート後
 * deleting　→　デリート前
 * deleted　→　デリート後
 * restoring　→　ソフトデリート復帰前
 * restored　→　ソフトデリート復帰後
 */
trait tModelObserver {

    // インサート前の処理
    public function creating($model) {
        $this->injectValueCaseCreating($model);
    }

    // インサート後の処理
    public function created($model) {
    }

    // アップデート前の処理
    public function updating($model) {
    }

    // アップデート後の処理
    public function updated($model) {
    }

    // インサート前及びアップデート前の処理
    public function saving($model) {
        $this->injectValueCaseSaving($model);
    }

    private function injectValueCaseCreating($model) {
        $user = Auth::user();
        if(!empty($user)) {
            $model->created_by = $user->id;
            $model->updated_by = $user->id;
        } else {
            // seeder用
            $model->created_by = '1';
            $model->updated_by = '1';
        }
    }

    private function injectValueCaseUpdating($model) {
        $user = Auth::user();
        if(!empty($user)) {
            $model->updated_by = $user->id;

        } else {
            // seeder用
            $model->updated_by = '1';
        }
    }

    private function injectValueCaseSaving($model) {
        $user = Auth::user();
        $user_id = '1';
        if(!empty($user)) {
            $user_id = $user->id;
        }
        if(!$model->isNew()) {
            $model->updated_by = $user_id;
            $model->updated_at = DateUtil::now();
        } else {
            $model->created_by = $user_id;
            $model->updated_by = $user_id;
            $model->created_at = DateUtil::now();
            $model->updated_at = DateUtil::now();
        }
    }
}
