<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use DB;
use Illuminate\Support\Facades\View;
use App\Lib\Util\DateUtil;

class AppServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        DB::listen(function($sql, $bindings, $time) {
            \Log::debug('SQL--------('. $time . ' sec)');
            \Log::debug($sql .PHP_EOL . json_encode($bindings));
            // \Log::debug($bindings);
            \Log::debug('--------');
        });

        //\View::addNamespace('pc', base_path('resources/views'));
        //\View::addNamespace('sp', base_path('resources/views/sp'));
        // 処理時間を記録
        // $runTime = DateUtil::getRunTime();
        View::share('runTime', NULL);

    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        // 20200303 Luc.dang 追加
        if($this->app->environment('production'))
        {
            $this->app['request']->server->set('HTTPS','on');
        }

        $this->app->bind(
            'Illuminate\Contracts\Auth\Registrar',
            'App\Services\Registrar'
        );
    }

}
