<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\View\FileViewFinder;
use Closure;

class ViewFinder {

    protected $view;

    public function __construct( ViewFactory $view ) {
        $this->view = $view;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        // UAによってViewを探すパスを変更する
        $ua = $request->server->get('HTTP_USER_AGENT');
        //if(!\Agent::isDesktop($ua)) {
        //
        if( \Agent::isPhone( $ua ) ) {
            \Log::debug('>>>>>>>>>>>>>>> SmartPhone');

            $app = app();
            $paths = $app['config']['view.paths'];
            array_unshift($paths, $app['config']['view.sp_path']);

            $this->view->setFinder(
                new FileViewFinder( $app['files'], $paths )
            );
        }

        return $next( $request );
    }

}
