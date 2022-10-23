<?php

namespace App\Providers;

# Lib
use App\Lib\Codes\FinishedCodes;
use App\Lib\Codes\WorkLevelCodes;
use App\Lib\Codes\ViewStatusCodes;
use App\Lib\Codes\CheckCodes;
use App\Lib\Codes\CheckAriCodes;
use App\Lib\Codes\DispCodes;
use App\Lib\Codes\MaruBatsuCodes;
use App\Lib\Codes\MaruCodes;
use App\Lib\Codes\RowNumCodes;
use App\Lib\Codes\DmUnnecessaryReasonCodes;
use App\Lib\Util\Constants;
use App\Lib\Util\DateUtil;

# Original
use App\Original\Codes\CheckboxAriCodes;
use App\Original\Util\SessionUtil;
use App\Original\Util\ViewUtil;

# Model
use App\Models\Role;
use App\Models\Base;
use App\Models\UserAccount;

use Illuminate\Support\ServiceProvider;
use View;
use App\Models\TargetCars;

/**
 * Viewに値を埋め込むサービスプロバイダー
 *
 * @author yhatsutori
 *
 */
class ViewComposerServiceProvider extends ServiceProvider {

    /**
     * サービスプロバイダーを定義する時のルールです
     *
     */
    public function boot()
    {

        #####################
        ## Original
        #####################
        
        // ログインユーザー情報の埋め込み
        View::composer( ['*'], 'App\Original\ViewComposers\LoginAccountComposer' );
        
        ######################
        ## 日付の項目
        ######################

        // 年月（From）のSelect
        View::composer('elements.date.ym_select_from', function($view) {
            $view->select = ViewUtil::genSelectTag(
                ViewUtil::defaultYmOptions(),
                true // 空の値のフラグ
            );
        });

        // 年月（To）のSelect
        View::composer('elements.date.ym_select_to', function($view) {
            $view->select = ViewUtil::genSelectTag(
                ViewUtil::defaultYmOptions(),
                true // 空の値のフラグ
            );
        });

        // 年月（過去日なし）
        View::composer('elements.date.ym_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                ViewUtil::ymOptions( DateUtil::toYm( DateUtil::now(), '-' ), 6 ),
                false
            );
        });

        // 対象月の範囲（現在の月から半年前の月を初月とし、そこから1年分）
        View::composer('elements.date.target_select_ym', function($view) {
            $view->select = ViewUtil::genSelectTag(
                ViewUtil::ymOptions( DateUtil::nowMonthAgo(6), 12 ),
                false
            );
        });


        // 年のみのセレクト
        View::composer('elements.date.year_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                DateUtil::optionYear(),
                true // 空の値のフラグ
            );
        });

        // 月のみのセレクト
        View::composer('elements.date.month_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                DateUtil::optionMonth(),
                true // 空の値のフラグ
            );
        });

        ######################
        ## 共通の項目
        ######################

        // 行数のSelect
        View::composer('elements.tag.row_num_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new RowNumCodes())->getOptions(),
                false
            );
        });
        
        // 有/無のセレクトボックス
        View::composer('elements.tag.check_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new CheckCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 有/無のラジオボタン
        View::composer('elements.tag.check_radio', function($view) {
            $view->radio = new CheckCodes;
        });

        // 有/無のチェックボックス
        View::composer('elements.tag.check_checkbox', function($view) {
            $view->checkbox = new CheckCodes;
        });
        
        // 有/無のセレクトボックス
        View::composer('elements.tag.check_select_must', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new CheckCodes())->getOptions(),
                false // 空の値のフラグ
            );
        });

        // 有のチェックボックス
        View::composer('elements.tag.check_ari_checkbox', function($view) {
            $view->checkbox = new CheckboxAriCodes;
        });
        
        // 有のセレクトボックス
        View::composer('elements.tag.check_ari_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new CheckAriCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 済／未のチェックボックス
        View::composer('elements.tag.check_finished_checkbox', function($view) {
            $view->checkbox = new FinishedCodes();
        });

        // 新中区分
        View::composer('elements.tag.work_level_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new WorkLevelCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });
        
        // 拠点
        View::composer(['elements.tag.store_select', 'top.time_table'], function($view) {
            $view->select = ViewUtil::genSelectTag(
                Base::options(),
                true // 空の値のフラグ
            );
        });

        // 拠点
        View::composer(['elements.tag.store_select_id', 'top.time_table'], function($view) {
            $view->select = ViewUtil::genSelectTag(
                Base::optionsId(),
                true // 空の値のフラグ
            );
        });

        // 未選択項目なしの拠点
        View::composer('elements.tag.no_default_store_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                Base::options(),
                false
            );
        });
        
        // 機能権限
        View::composer('elements.tag.role_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                Role::options(),
                true // 空の値のフラグ
            );
        });
        
        // 表示/非表示
        View::composer('elements.tag.disp_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new DispCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });
        
        // ○/×
        View::composer('elements.tag.marubatsu_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new MaruBatsuCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // お知らせ（掲載期間）
        View::composer('elements.tag.view_status_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new ViewStatusCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });
                        
        /**
         * DM不要理由のセレクト
         */
        View::composer('elements.tag.dm_reason', function($view) {
            $codes = new DmUnnecessaryReasonCodes;
            $view->select = ViewUtil::genSelectTag(
                $codes->getOptions(),
                true // 空の値のフラグ
            );
        });

        ######################
        ## 拠点/担当者
        ######################
        
        // 担当者Code
        View::composer('elements.tag.staff_select', function($view) {
            //UserAccount::whereNotIn('id', [1]) // システムを除外
            $accoutList = UserAccount::whereNotIn('user_code', ['mut']) // システムを除外
                                        ->orderBys(['id' => 'asc'])
                                        ->lists('user_name', 'user_code');
            // 退職者を取得する。
            $deletedAccountList = UserAccount::staffOptionsDeleted(null);
            if (!$deletedAccountList->isEmpty()){
                array_add($accoutList, Constants::CONS_TAISHOKUSHA_CODE,"退職者");
            }

            $view->select = ViewUtil::genSelectTag(
                            $accoutList,
                           true // 空の値のフラグ
            );
        });

        // 担当者Id
        View::composer('elements.tag.staff_select_id', function($view) {
            //UserAccount::whereNotIn('id', [1]) // システムを除外
            $accoutList = UserAccount::whereNotIn('user_code', ['mut']) // システムを除外
                                        ->orderBys(['id' => 'asc'])
                                        ->lists('user_name', 'id');
            // 退職者を取得する。
            $deletedAccountList = UserAccount::staffOptionsDeleted(null);
            if (!$deletedAccountList->isEmpty()){
                array_add($accoutList, Constants::CONS_TAISHOKUSHA_CODE,"退職者");
            }

            $view->select = ViewUtil::genSelectTag($accoutList,
                                    true // 空の値のフラグ
            );
        });

        // 担当者(名前検索)
        View::composer('elements.tag.staff_name_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                //UserAccount::whereNotIn('id', [1,2,3]) // システムを除外
                UserAccount::whereNotIn('user_code', ['mut','ok0','ok1','ok2']) // システムを除外
                           ->orderBys(['base_id' => 'asc', 'user_name' => 'asc'])
                           //->lists('user_name', 'user_id'),
                           ->lists('user_name', 'id'),
                           true // 空の値のフラグ
            );
        });

        // トップページ担当者プルダウン
        View::composer('elements.tag.top_staff_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                //UserAccount::whereNotIn('id', [1]) // システムを除外
                UserAccount::whereNotIn('user_code', ['mut']) // システムを除外
                           ->orderBys(['id' => 'asc'])
                           ->lists('user_name', 'user_code'),
                           true // 空の値のフラグ
            );
        });

        // 本社担当者
        View::composer('elements.tag.head_staff_select', function($view) {
            // ユーザー情報を取得(セッション)
            $loginAccountObj = SessionUtil::getUser();
            
            $view->select = ViewUtil::genSelectTag(
                UserAccount::headStaffOptions(),
                true // 空の値のフラグ
            );
        });

        // 本社担当者
        View::composer('elements.tag.head_staff_select_id', function($view) {
            // ユーザー情報を取得(セッション)
            $loginAccountObj = SessionUtil::getUser();

            $view->select = ViewUtil::genSelectTag(
                UserAccount::headStaffOptionsId(),
                true // 空の値のフラグ
            );
        });

        // 車検回数
        View::composer('elements.inspect.syaken_times_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                TargetCars::whereNotNull('tgc_syaken_times')
                    ->where('tgc_inspection_id', '4')
                ->groupBy('tgc_syaken_times')
                ->orderBy('tgc_syaken_times', 'asc')
                ->lists('tgc_syaken_times','tgc_syaken_times'),
                true // 空の値のフラグ
            );
        });


    }
    
    // 
    public function register(){}

}
