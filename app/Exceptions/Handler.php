<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use \Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * @inheritDoc
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if (! $request->expectsJson() && ! $exception->redirectTo()) {
            // the framework expects us to have a login route but I've changed things
            // so lets bypass the one time that route is expected
            return redirect()->guest(route('session.create'));
        }

        return parent::unauthenticated($request, $exception);
    }
}
