<?php

namespace App\Providers;

use App\Original\Util\ViewUtil;

// ABC
use App\Original\Codes\Append\AbcCodes;
// TMR
use App\Original\Codes\Append\TmrIntentCodes;
// 接触情報
use App\Original\Codes\Append\ContactCodes;
use App\Lib\Codes\DispCodes;

use Illuminate\Support\ServiceProvider;
use View;

/**
 * Viewに値を埋め込むサービスプロバイダー
 *
 * @author yhatsutori
 *
 */
class ViewComposerAppendServiceProvider extends ServiceProvider {

    /**
     * サービスプロバイダーを定義する時のルールです
     *
     */
    public function boot()
    {

        ###########################
        ## ABC
        ###########################
        
        // ABC
        View::composer('elements.append.abc_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new AbcCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });
        
        // ABCのラジオボタン
        View::composer('elements.append.abc_radio', function($view) {
            $view->radio = new AbcCodes;
        });
        
        // ABCのチェックボックス
        View::composer('elements.append.abc_checkbox', function($view) {
            $view->checkbox = new AbcCodes;
        });

        // 表示、非表示のチェックボックス
        View::composer('elements.append.display_checkbox', function($view) {
            $view->checkbox = new DispCodes;
        });

        ###########################
        ## TMR
        ###########################
        
        // TMR意向
        View::composer('elements.append.tmr_intent_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new TmrIntentCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });
        
        ###########################
        ## 接触内容
        ###########################
        
        // 接触内容
        View::composer('elements.append.contact_type_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new ContactCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

    }

    // 
    public function register(){}

}
