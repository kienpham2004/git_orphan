<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\CustomerUmu;
use App\Models\Base;
use App\Models\Info;
use App\Models\UserAccount;
use App\Models\UploadHistory;
use App\Models\Contact\Contact;
use App\Models\Append\Abc;
use App\Models\Append\Ciao;
use App\Models\Append\Pit;
use App\Models\Append\Tmr;
use App\Models\Append\SmartPro;
use App\Models\Append\Ikou;
use App\Models\Append\DaigaeSyaken;
use App\Models\ResultCars;
use App\Models\TargetCars;
use App\Models\ManageInfo;
use App\Models\Plan;
use App\Models\Insurance;
use App\Models\Append\Recall;
use App\Original\Observer\BaseModelObserver;
use App\Original\Observer\ModelsObserver;
use App\Original\Observer\InfoModelObserver;
use App\Original\Observer\UserAccountModelObserver;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Lib\Util\DateUtil;

/**
 * Eventサービスプロバイダー
 *
 */
class EventServiceProvider extends ServiceProvider {

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'event.name' => [
            'EventListener',
        ],
        // ログイン完了イベント
        'App\Events\LoginedEvent' => [
            'App\Handlers\Events\LoginedHandler'
        ],
        // アップロード完了イベント
        'App\Events\UploadedEvent' => [
            'App\Handlers\Events\UploadedHandler'
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        // 拠点データオブザーバー
        Base::observe( new BaseModelObserver() );
        // ユーザーアカウント用オブザーバー
        UserAccount::observe( new UserAccountModelObserver() );
        // お知らせオブザーバー
        Info::observe( new InfoModelObserver() );

        // 顧客データ用オブザーバー
        Customer::observe( new ModelsObserver() );
        // 顧客データの有無用オブザーバー
        CustomerUmu::observe( new ModelsObserver() );

        // ABCデータオブザーバー
        //Abc::observe( new ModelsObserver() );
        // チャオデータオブザーバー
        //Ciao::observe( new ModelsObserver() );

        // Tmrデータオブザーバー
        Tmr::observe( new ModelsObserver() );
        // Pitデータオブザーバー
        Pit::observe( new ModelsObserver() );
        // スマートプロ査定データオブザーバー
        SmartPro::observe( new ModelsObserver() );
        // 個人予定
        Contact::observe( new ModelsObserver() );

        // 顧客ロックデータ
        TargetCars::observe( new ModelsObserver() );
        // 集計データ
        ResultCars::observe( new ModelsObserver() );
        // マネージメントインフォデータ
        ManageInfo::observe( new ModelsObserver() );
        
        // 意向確認データオブザーバー
        Ikou::observe( new ModelsObserver() );
        // 保険データ
        Insurance::observe( new ModelsObserver() );
        // リコール
        Recall::observe( new ModelsObserver() );

        // 代替車検推進管理ザーバー
        DaigaeSyaken::observe( new ModelsObserver() );

        // アップロード履歴用オブザーバー
        UploadHistory::observe( new ModelsObserver() );
        
        // 計画データオブザーバー
        Plan::observe( new ModelsObserver() );

        // 実行時間を取得する。
        DateUtil::$runStart = microtime(true);
    }

}
