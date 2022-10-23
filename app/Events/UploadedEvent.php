<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use App\Models\UserAccount;
use App\Events\Event;

/**
 * csvをアップロードした時のイベント
 * App\Events\Handlers\UploadedHandler.phpと連動する
 */
class UploadedEvent extends Event
{
    use SerializesModels;

    public $user;
    public $resultCnt;
    public $file_type;

    /**
     * Create a new event instance.
     * UploadedHandlerが本体
     * @return void
     */
    public function __construct( UserAccount $user, $resultCnt, $file_type )
    {
        $this->user = $user;
        $this->resultCnt = $resultCnt;
        $this->file_type = $file_type;
    }


}
