<?php

namespace App\Http\Middleware\Role;

use App\Original\Util\SessionUtil;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Closure;

/**
 * コントローラー内で、本社スタッフ権限かどうかを判定
 */
class RoleHonsya {

    protected $auth;

    public function __construct( Guard $auth ) {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle( $request, Closure $next ){
        // ユーザー情報を取得(セッション)
        $user = SessionUtil::getUser();
        
        // 本社スタッフ権限の有無を確認
        if( in_array( $user->getRolePriority(), [1,2,3,4] ) != True ) {
            return new RedirectResponse( url('/auth/logout') );
        }
        return $next( $request );
    }

}
