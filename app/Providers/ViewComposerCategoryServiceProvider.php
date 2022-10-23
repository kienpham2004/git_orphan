<?php

namespace App\Providers;

use App\Original\Codes\IkouLatestCodes;
use App\Original\Codes\TenkenIkouLatestCodes;
use App\Original\Util\ViewUtil;
// モーダル関連
use App\Original\Codes\Modal\ActionCodes;
// Inspect
use App\Original\Codes\Inspect\InspectSyatenkenTypes;
use App\Original\Codes\Inspect\InspectDmTypes;
use App\Original\Codes\Inspect\InspectDmEventTypes;
use App\Original\Codes\Inspect\InspectInsuTypes;
use App\Original\Codes\Inspect\InspectCreditTypes;
// Intent
use App\Original\Codes\Intent\IntentSyatenkenCodes;
use App\Original\Codes\Intent\IntentCreditCodes;
use App\Original\Codes\Intent\IntentInsuranceCodes;
use App\Original\Codes\Intent\IntentNousyaCodes;
// 保険関連
use App\Original\Codes\Insurance\InsuJisyaTasyaCodes;
use App\Original\Codes\Insurance\InsuStatusCodes;
use App\Original\Codes\Insurance\InsuActionCodes;
use App\Original\Codes\Insurance\InsuJiguCodes;
use App\Original\Codes\Insurance\InsuContactTaisyoCodes;
use App\Original\Codes\Insurance\InsuSyaryoCodes;
use App\Original\Codes\Insurance\InsuTetsudukiCodes;
use App\Original\Codes\Insurance\InsuPeriodCodes;
use App\Original\Codes\Insurance\InsuCompanyCodes;
use App\Original\Codes\Insurance\InsuNaiyouCodes;
use App\Original\Codes\Insurance\InsuStatusGensenCodes;

use Illuminate\Support\ServiceProvider;
use View;

/**
 * Viewに値を埋め込むサービスプロバイダー
 *
 * @author yhatsutori
 *
 */
class ViewComposerCategoryServiceProvider extends ServiceProvider {

    /**
     * サービスプロバイダーを定義する時のルールです
     *
     */
    public function boot()
    {   

        ######################
        ## ダイアログの項目
        ######################
        
        // 活動内容
        View::composer('elements.modal.action_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new ActionCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });
        
        ######################
        ## 車点検・クレジット種別
        ######################
        
        // 車点検区分
        View::composer('elements.inspect.select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new InspectSyatenkenTypes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 車点検区分(DM)
        View::composer('elements.inspect.dm_select', function($view) {
            // 車点検の種別
            $dmTypes = (new InspectDmTypes())->getOptions();
            // 車検を削除
            unset( $dmTypes['2'] );

            $view->select = ViewUtil::genSelectTag(
                $dmTypes,
                true // 空の値のフラグ
            );
        });

        // 車点検区分(DM)
        View::composer('elements.inspect.dm_event_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new InspectDmEventTypes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 点検区分
        View::composer('elements.inspect.tenken_select', function($view) {
            // 車点検の種別
            $syatenkenTypes = (new InspectSyatenkenTypes())->getOptions();
            // 車検を削除
            unset( $syatenkenTypes['4'] );

            $view->select = ViewUtil::genSelectTag(
                $syatenkenTypes,
                true // 空の値のフラグ
            );
        });
        
        // 点検区分(グラフ用)
        View::composer('elements.inspect.tenken_select_graph', function($view) {
            // 車点検の種別
            $syatenkenTypes = (new InspectSyatenkenTypes())->getOptions();
            // 車検を削除
            unset( $syatenkenTypes['4'] );

            $view->select = ViewUtil::genSelectTag(
                $syatenkenTypes,
                false
            );
        });
        
        // 保険区分
        View::composer('elements.inspect.insu_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new InspectInsuTypes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // クレジット区分
        View::composer('elements.inspect.credit_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new InspectCreditTypes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        ######################
        ## 活動意向
        ######################
        
        // 活動意向コード(車点検)
        View::composer('elements.intent.syatenken_code_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new IntentSyatenkenCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 活動意向コード（クレジット）
        View::composer('elements.intent.credit_code_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new IntentCreditCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 活動意向コード（保険）
        View::composer('elements.intent.insurance_code_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new IntentInsuranceCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 活動意向コード（納車）
        View::composer('elements.intent.nousya_code_select', function($view) {
            $view->select = ViewUtil::genSelectTag(
                (new IntentNousyaCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });
        
        ######################
        ## 保険の項目
        ######################
        
        // 自社・他社
        View::composer('elements.insurance.insu_jisya_tasya_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new InsuJisyaTasyaCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 意向結果(他社分・追加分)
        View::composer('elements.insurance.insu_status_tasya_select', function($view) {
            // 獲得状況の種別
            $insuStatus = (new InsuStatusCodes())->getOptions();
            // 未接触敗戦を削除
            unset( $insuStatus['99'] );

            // 自社分の値を削除
            // 更新済（同条件）を削除
            unset( $insuStatus['21'] );
            // 更新済（変更あり）を削除
            unset( $insuStatus['22'] );
            // 更新予定を削除
            unset( $insuStatus['23'] );
            // 未継続見込を削除
            unset( $insuStatus['24'] );
            // 未継続確定を削除
            unset( $insuStatus['25'] );
            // 長期の確認を削除
            unset( $insuStatus['26'] );

            $view->select = ViewUtil::genSelectMultiTag(
                $insuStatus,
                true // 空の値のフラグ
            );
        });

        // 意向結果(自社分)
        View::composer('elements.insurance.insu_status_jisya_select', function($view) {
            // 獲得状況の種別
            $insuStatus = (new InsuStatusCodes())->getOptions();
            // 未接触敗戦を削除
            unset( $insuStatus['99'] );

            // 他社分・追加分の値を削除
            // 獲得済を削除
            unset( $insuStatus['6'] );
            // 見込度：90%を削除
            unset( $insuStatus['4'] );
            // 見込度：70%を削除
            unset( $insuStatus['3'] );
            // 見込度：50%を削除
            unset( $insuStatus['2'] );
            // 見込度：30%を削除
            unset( $insuStatus['1'] );
            // 敗戦を削除
            unset( $insuStatus['5'] );

            $view->select = ViewUtil::genSelectMultiTag(
                $insuStatus,
                true // 空の値のフラグ
            );
        });

        // 意向結果(自社分)
        View::composer('elements.insurance.insu_status_jisya_null_select', function($view) {
            // 獲得状況の種別
            $insuStatus = (new InsuStatusCodes())->getOptions();
            // 未接触敗戦を削除
            unset( $insuStatus['99'] );

            // 他社分・追加分の値を削除
            // 獲得済を削除
            unset( $insuStatus['6'] );
            // 見込度：90%を削除
            unset( $insuStatus['4'] );
            // 見込度：70%を削除
            unset( $insuStatus['3'] );
            // 見込度：50%を削除
            unset( $insuStatus['2'] );
            // 見込度：30%を削除
            unset( $insuStatus['1'] );
            // 敗戦を削除
            unset( $insuStatus['5'] );

            // 未接触を特別に追加
            $insuCustomStatus = [];
            $insuCustomStatus['0'] = '未接触';

            foreach ($insuStatus as $key => $value) {
                $insuCustomStatus[$key] = $value;
            }

            $view->select = ViewUtil::genSelectMultiTag(
                $insuCustomStatus,
                true // 空の値のフラグ
            );
        });

        // 意向結果(自社分)
        View::composer('elements.insurance.insu_status_jisya_list_select', function($view) {
            // 獲得状況の種別
            $insuStatus = (new InsuStatusCodes())->getOptions();
            // 未接触敗戦を削除
            unset( $insuStatus['99'] );
            // 対象外を削除
            unset( $insuStatus['100'] );

            // 他社分・追加分の値を削除
            // 獲得済を削除
            unset( $insuStatus['6'] );
            // 見込度：90%を削除
            unset( $insuStatus['4'] );
            // 見込度：70%を削除
            unset( $insuStatus['3'] );
            // 見込度：50%を削除
            unset( $insuStatus['2'] );
            // 見込度：30%を削除
            unset( $insuStatus['1'] );
            // 敗戦を削除
            unset( $insuStatus['5'] );

            // 未接触を特別に追加
            $insuCustomStatus = [];
            $insuCustomStatus['0'] = '未接触';

            foreach ($insuStatus as $key => $value) {
                $insuCustomStatus[$key] = $value;
            }

            $view->select = ViewUtil::genSelectMultiTag(
                $insuCustomStatus,
                true // 空の値のフラグ
            );
        });

        // 活動内容
        View::composer('elements.insurance.insu_action_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new InsuActionCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 治具内容
        View::composer('elements.insurance.insu_jigu_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new InsuJiguCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 接触対象(本人：本人以外)
        View::composer('elements.insurance.insu_contacttaisyo_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new InsuContactTaisyoCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });
        
        // 車両保険付帯
        View::composer('elements.insurance.insu_syaryo_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new InsuSyaryoCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });
        
        // 手続き内容
        View::composer('elements.insurance.insu_tetsuduki_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new InsuTetsudukiCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 保険期間（長期の推進）
        View::composer('elements.insurance.insu_period_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new InsuPeriodCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 保険会社
        View::composer('elements.insurance.insu_company_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new InsuCompanyCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });
        
        // 処理内容
        View::composer('elements.insurance.insu_naiyou_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new InsuNaiyouCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 獲得源泉
        View::composer('elements.insurance.insu_status_gensen_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new InsuStatusGensenCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 2022/02/17 update
        // 意向結果
        View::composer('elements.insurance.tgc_status_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new IkouLatestCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

        // 意向結果
        View::composer('elements.insurance.tgc_status_tenken_select_search', function($view) {
            $option = (new TenkenIkouLatestCodes())->getOptions();
            unset($option[21]);
            unset($option[22]);
            unset($option[23]);
            $option[23] = '自社代替';
            $view->select = ViewUtil::genSelectMultiTag(
                $option,
                true // 空の値のフラグ
            );
        });

        // 意向結果
        View::composer('elements.insurance.tgc_status_tenken_select', function($view) {
            $view->select = ViewUtil::genSelectMultiTag(
                (new TenkenIkouLatestCodes())->getOptions(),
                true // 空の値のフラグ
            );
        });

    }
    
    // 
    public function register(){}

}
