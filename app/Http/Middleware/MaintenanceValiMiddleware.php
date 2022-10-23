<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use App\Original\Util\SessionUtil;
use App\Lib\Util\Constants;

class MaintenanceValiMiddleware
{

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // メンテナンスチェック
        $mainten_start = config('original.mainten_start');
        try {

            SessionUtil::put(Constants::SEC_MAINTEN_FLAG, 0); //初期設定値

            $match_date = date('Y/m/d', strtotime($mainten_start));
            // メンテナンス対象日
            if($mainten_start != "" && $match_date == date("Y/m/d") ){
                // 開始時間
                $star_hour = strtotime(config('original.mainten_start'));
                // 終了時間
                $end_hour = strtotime(config('original.mainten_end'));

                // 設定時間チェック
                if ($star_hour < $end_hour) {
                    // メンテナンス条件チェック
                    if ($star_hour <= time() && time()  <= $end_hour) {
                        // メッセージが出ないフラグ設定
                        SessionUtil::put(Constants::SEC_MAINTEN_FLAG, 2);

                        // ユーザー情報を削除(セッション)
                        SessionUtil::removeUser();
                        $this->auth->logout();

                        // メンテナンス画面へ遷移する
                        return redirect('/mainten');

                    }else{
                        // メンテナンスのメッセージ表示チェック
                        $before_alert = config('original.mainten_before_alert');
                        $timeNow = date("Y/m/d H:i");
                        $startTime = date('Y/m/d H:i', strtotime($mainten_start));
                        // ログインせずの場合
                        if (is_numeric($before_alert) && $startTime >= $timeNow ){
                            $alert_time = strtotime("+$before_alert minutes"); // 30 minutes * 60 seconds/minute
                            if ($star_hour <= $alert_time) {
                                SessionUtil::put(Constants::SEC_MAINTEN_FLAG, 1);
                            }
                        }
                    }
                }
            }
        } catch ( Exception $ex ) {
            \Log::error('メンテナンス値設定不正'.ex);
        }
        return $next($request);
    }
}
