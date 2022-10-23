<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use App\Models\UserAccount;
use App\Events\Event;

/**
 * ログインした時のイベント
 * App\Handlers\Events\LoginedHandler.phpと連動する
 */
class LoginedEvent extends Event
{
    use SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct( UserAccount $user )
    {
        $this->user = $user;
    }

}
