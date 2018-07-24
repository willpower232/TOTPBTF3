<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        // ensure that the session has an encryption key during auth check in controller
        if (auth()->check() && strlen(session('encryptionkey')) < 1) {
            auth()->logout();
            return redirect()->to('/login')->with('message', 'Your session has expired because your encryption key is missing');
        }

        // blend in with original handle function from middleware
        return call_user_func_array('parent::handle', func_get_args());
    }
}
