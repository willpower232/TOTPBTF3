<?php

namespace App\Http\Controllers;

use App\Models\User;
use Defuse\Crypto\KeyProtectedByPassword;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SessionsController implements HasMiddleware
{
    use ValidatesRequests;

    public static function middleware(): array
    {
        return [
            'auth',
        ];
    }

    // GET /profile
    public function show(): View
    {
        return view('sessions/show')->with('user', user());
    }

    // GET /profile/edit
    public function edit(): View
    {
        if (config()->boolean('app.readonly')) {
            abort(RedirectResponse::HTTP_NOT_FOUND);
        }

        return view('sessions/form')->with('user', user());
    }

    // POST /profile
    public function update(): RedirectResponse
    {
        if (config()->boolean('app.readonly')) {
            abort(RedirectResponse::HTTP_NOT_FOUND);
        }

        try {
            $this->validate(request(), User::getValidationRules('update'));
        } catch (ValidationException) {
            return redirect(route('session.edit'))
                ->withInput(request()->only([ // don't return the plaintext passwords to the view
                    'name',
                    'email',
                ]))
                ->with('message', 'Please check your input');
        }

        if (
            ! auth()->guard()->validate([
                'email' => user()->email,
                'password' => request()->string('currentpassword'),
            ])
        ) {
            return redirect(route('session.edit'))
                ->withInput(request()->only([ // don't return the plaintext passwords to the view
                    'name',
                    'email',
                ]))
                ->withErrors([
                    'currentpassword' => 'You did not enter your current password correctly',
                ]);
        }

        $user = user();

        $user->name = request()->string('name');
        $user->email = request()->string('email');

        $passwordchanged = false;
        if (strlen(request()->string('newpassword')) > 0) {
            $passwordchanged = true;

            $user->password = request()->string('newpassword');

            $protected_key = KeyProtectedByPassword::loadFromAsciiSafeString(
                user()->protected_key_encoded
            );

            // changing the key password can throw exceptions so shouldn't save the user yet
            $protected_key = $protected_key->changePassword(
                request()->string('currentpassword'),
                request()->string('newpassword')
            );

            $user->protected_key_encoded = $protected_key->saveToAsciiSafeString();
        }

        $user->save();

        if ($passwordchanged) {
            Auth::logoutOtherDevices(request()->string('newpassword'));

            session()->flush(); // remove encryption key
            auth()->guard()->logout();

            return redirect(route('login'));
        }

        return redirect(route('session.show'));
    }
}
