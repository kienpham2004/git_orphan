<?php

namespace App\Http\Middleware\Role;

use App\Original\Util\SessionUtil;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Closure;

/**
 * コントローラー内で、店長権限かどうかを判定
 */
class RoleTentyou {

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
        $loginAccountObj = SessionUtil::getUser();
        
        // 店長権限の有無を確認
        if( in_array( $loginAccountObj->getRolePriority(), [1,2,3,4,5] ) != True ) {
            return new RedirectResponse( url('/auth/logout') );
        }

        return $next( $request );
    }

}
