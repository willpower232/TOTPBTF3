<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

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
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        // https://stackoverflow.com/a/34791686
        if ($this->isHttpException($exception) && method_exists($exception, 'getStatusCode')) {
            switch ($exception->getStatusCode()) {
                // not authorized
                case '403':
                    return response()->view('error', array('errormessage' => 403), 403);
                    break;

                // not found
                case '404':
                    return response()->view('error', array('errormessage' => 404), 404);
                    break;

                // method not allowed
                case '405': // $exception instanceof MethodNotAllowedHttpException
                    return response()->view('error', array('errormessage' => 405), 405);
                    break;

                // internal error
                case '500':
                    return response()->view('error', array('errormessage' => 500), 500);
                    break;
            }

            return $this->renderHttpException($exception);
        }

        return parent::render($request, $exception);
    }
}
