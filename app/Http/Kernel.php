<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',

		'App\Http\Middleware\VerifyCsrfToken',
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth.basic' =>          'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',

		'auth' =>                'App\Http\Middleware\Authenticate',
		'guest' =>               'App\Http\Middleware\RedirectIfAuthenticated',
		'viewFinder' =>          'App\Http\Middleware\ViewFinder',

		'RoleAdmin' =>           'App\Http\Middleware\Role\RoleAdmin',
		'RoleManager' =>         'App\Http\Middleware\Role\RoleManager',
		'RoleHonsya' =>          'App\Http\Middleware\Role\RoleHonsya',
		'RoleTentyou' =>         'App\Http\Middleware\Role\RoleTentyou',

        'mainten' =>             'App\Http\Middleware\MaintenanceValiMiddleware',
	];

}
