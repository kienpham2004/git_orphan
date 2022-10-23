<?php

namespace App\Original\ViewComposers;

use App\Original\Util\SessionUtil;
use Illuminate\Contracts\View\View;

/**
 * ログインしているユーザー情報を取得する
 * ビューコンポーサー用のクラス
 * app\Providers\ViewComposerServiceProvider.php:
 */
class LoginAccountComposer{
    
    protected $loginAccountObj;

    /**
     * ログイン情報の取得
     */
    public function __construct(){
        // ユーザー情報を取得(セッション)
        $loginAccountObj = SessionUtil::getUser();
        
        if ( !empty( $loginAccountObj ) ) {
            $this->loginAccountObj = $loginAccountObj;
        } else {
            $this->loginAccountObj = null;
        }
    }

    public function compose( View $view ){
        $view->with( 'loginAccountObj', $this->loginAccountObj );
    }

}