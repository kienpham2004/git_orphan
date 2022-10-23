<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler {

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     * ※独自改修有り
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ( $e instanceof TokenMismatchException ) {
            return redirect()->route('logout')
                    //->with('csrf_error', 'ページを長時間開いていた為、セッションが切れました。<br />セキュリティのためログアウトしました。');
                    ->with('csrf_error', true);
        }

        if ( $this->isHttpException( $e ) ) {
            //
            $statusCode = $e->getStatusCode();
            
            switch ( $statusCode ) {
                case '404':
                    // あえてエラー表示を出力
                    //return response()->view('layouts/index', [
                    //    'content' => view('errors/404')
                    //]);
                    
                    // 強制ログアウト
                    //return response()->view('auth/login');
                    
                    // 強制ログアウト
                    //return redirect('auth/logout');
            }

            return $this->renderHttpException( $e );
        }

       return parent::render( $request, $e );
    }

}
