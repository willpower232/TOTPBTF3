<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class Authenticate extends Middleware
{
    /**
     * An extension of the corresponding method from the parent which
     * guarantees the session has an encryption key.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // ensure that the session has an encryption key during auth check in controller
        if (auth()->check() && strlen(session('encryptionkey')) < 1) {
            auth()->logout();
            return redirect()->to(route('session.create'))->with(
                'message',
                'Your session has expired because your encryption key is missing'
            );
        }

        // blend in with original handle function from middleware
        return call_user_func_array('parent::handle', func_get_args());
    }
}
