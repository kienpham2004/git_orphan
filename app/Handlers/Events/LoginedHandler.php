<?php

namespace App\Handlers\Events;

use App\Events\LoginedEvent;
use Log;

/**
 * ログインイベント処理のハンドラ
 */
class LoginedHandler
{
    /**
     * Create the event handler.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * LoginedEventが発火されたタイミングで動作
     * 最終ログイン日時（ログイン日時）を登録する処理
     *
     * @param  LoginedEvent  $event
     * @return void
     */
    public function handle(LoginedEvent $event)
    {
        Log::debug('LoginedHandler::handle!');

        // ログインしたユーザーの最終更新日時を変更
        $user = $event->user;
        $user->last_logined = date( 'Y-m-d H:i:s' );
        $user->save();
    }

}
