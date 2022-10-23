<?php

namespace App\Original\ViewComposers;

// モーダル関連
use App\Original\Codes\Modal\ActionCodes;

// 車点検区分
use App\Original\Codes\Inspect\InspectSyatenkenTypes;
use App\Original\Codes\Inspect\InspectCreditTextToTexts;

// 意向区分
use App\Original\Codes\Intent\IntentSyatenkenCodes;
use App\Original\Codes\Intent\IntentCreditCodes;
use App\Original\Codes\Intent\IntentInsuranceCodes;
use App\Original\Codes\Intent\IntentNousyaCodes;

// 顧客コード.csv用
use App\Original\Codes\Intent\IntentKeisanHousikiCodes;
use App\Original\Codes\Intent\IntentKojinHojinCodes;

use Illuminate\Contracts\View\View;

/**
 * Codeクラスを使う為の
 * ビューコンポーサー用のクラス
 * app\Providers\ViewComposerServiceProvider.php:
 */
class CodeComposer
{
    
    /**
     * Codeクラスのオブジェクトを取得
     */
    public function __construct(){}

    public function compose( View $view ){

        // モーダル関連
        // 活動内容
        $view->with( 'ActionCodes', new ActionCodes() );
        
        // 車点検区分
        $view->with( 'InspectSyatenkenTypes', new InspectSyatenkenTypes() );
        $view->with( 'InspectCreditTextToTexts', new InspectCreditTextToTexts() );

        // 意向区分
        $view->with( 'IntentSyatenkenCodes', new IntentSyatenkenCodes() );
        $view->with( 'IntentCreditCodes', new IntentCreditCodes() );
        $view->with( 'IntentInsuranceCodes', new IntentInsuranceCodes() );
        $view->with( 'IntentNousyaCodes', new IntentNousyaCodes() );
        $view->with( 'IntentKeisanHousikiCodes',new IntentKeisanHousikiCodes() );
        $view->with( 'IntentKojinHojinCodes', new IntentKojinHojinCodes() );
        
    }

}