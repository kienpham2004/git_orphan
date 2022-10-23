<?php

// メンテナンス画面に画面遷移
Route::get('/mainten','Auth\MaintenController@index');


Route::group(['middleware' => ['mainten']], function() {
// login画面に画面遷移
    Route::get('/', function () {
        return redirect('auth/login');
    });

// login画面に画面遷移
    Route::get('/auth', function () {
        return redirect('auth/login');
    });

// 認証階層
    Route::controller('auth', 'Auth\AuthController',
        [
            'postLogin' => 'post.login',
            'getLogin' => 'get.login',
            'getRegister' => 'get.register', // 使ってない・・・
            'postRegister' => 'post.register', // 使ってない・・・
            'getLogout' => 'logout'
        ]);
});

// TODO: 適当なコメントを入れること
// ○○階層
Route::group(['middleware' => ['auth','mainten']], function() {
    // TOP画面のコントローラー
    Route::group(['prefix' => 'top'], function () {
        Route::controller('/',    'Top\GraphController');
    });
    
    // 活動進捗グラフのコントローラー
    Route::group(['prefix' => 'graph'], function() {
        Route::controller('syaken_ikou', 'Graph\SyakenIkouController');
        
        // いづれ追加機能がありそうなのでコメントアウト
        //Route::controller('syaken_jisshi', 'Graph\SyakenJisshiController');
        
        Route::controller('tenken_ikou', 'Graph\TenkenIkouController');
        
        // いづれ追加機能がありそうなのでコメントアウト
        //Route::controller('hoken_ikou', 'Graph\HokenIkouController');
    });
    
    // メイン画面のコントローラー
    Route::group(['prefix' => 'main'], function () {
        Route::controller('syaken', 'Main\SyakenController');
        Route::controller('tenken', 'Main\TenkenController');
        Route::controller('customer', 'Main\CustomerController');

        // いづれ追加機能がありそうなのでコメントアウト
        //Route::controller('credit', 'Main\CreditController');
        //Route::controller('hoken',  'Main\HokenController');
    });
    
    // DMリスト画面のコントローラー
    Route::group(['prefix' => 'dm'], function() {
        Route::controller('dm_syaken',      'Dm\DmSyakenController');
        Route::controller('dm_tenken_last', 'Dm\DmTenkenLastController');
        Route::controller('dm_tenken',      'Dm\DmTenkenController');
        Route::controller('confirm',        'Dm\DmConfirmController');
        Route::controller('collect',        'Dm\DmCollectController');
        //Route::controller('/', 'Dm\DmController');
    });
    
    // DM汎用リスト画面のコントローラー
    Route::group(['prefix' => 'dm_hanyou'], function() {
        Route::controller('dm',             'DmHanyou\DmHanyouController');
        Route::controller('confirm',        'DmHanyou\DmConfirmController');
        Route::controller('collect',        'DmHanyou\DmCollectController');
        //Route::controller('/', 'Dm\DmController');
    });
    
    // 実績分析画面のコントローラー一覧
    Route::group(['prefix' => 'result'], function() {
        Route::controller('syaken',    'Result\SyakenController');
        Route::controller('tenken',    'Result\TenkenController');
        Route::controller('car',       'Result\CarTypeController');
        Route::controller('bouei',     'Result\BoueiController');
        //Route::controller('/', 'Result\SyatenkenController');
        Route::controller('status',     'Result\StatusController');
    });
    
    // 本社管理画面のコントローラー一覧
    Route::group(['prefix' => 'honsya'], function() {
        Route::controller('log',          'Honsya\LogMonthController');
        Route::controller('info',         'Honsya\InfoController');
        Route::controller('user',         'Honsya\UserController');
        Route::controller('base',         'Honsya\BaseController');
        Route::controller('/',            'Honsya\CsvController', ['postUpload' => 'post.honsya']);
    });
    
    // 六三管理画面のコントローラー一覧
    Route::group(['prefix' => 'mut'], function() {
        Route::controller('dm',          'Mut\DmController');
        Route::controller('dm_hanyou',   'Mut\DmHanyouController');
        Route::controller('kekka',       'Mut\KekkaController');
        //Route::controller('/',          'Mut\DmController');
    });

    // 保険画面のコントローラー
    Route::group(['prefix' => 'hoken'], function () {
        Route::controller('list_keizoku',   'Hoken\HokenKeizokuController');
        Route::controller('ikou',           'Hoken\HokenIkouController');
        Route::controller('result',         'Hoken\HokenResultController');
        Route::controller('result_staff',   'Hoken\HokenResultStaffController');
//        Route::controller('list_kakutoku',  'Hoken\HokenKakutokuController'); ？？？？？
    });
    
});
