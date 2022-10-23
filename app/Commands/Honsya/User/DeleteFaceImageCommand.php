<?php

namespace App\Commands\Honsya\User;

use App\Commands\Command;
use App\Models\UserAccount;

class DeleteFaceImageCommand extends Command
{
    protected $user_id;

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    public function handle()
    {
        $login_user = UserAccount::find($this->user_id);

        $login_user->img_uploaded_flg = 0; // アップロードフラグを0にする
        $login_user->file_name = null; // パスをnullにする

        return $login_user->save();
    }
}
