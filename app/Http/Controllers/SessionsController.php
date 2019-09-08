<?php
namespace App\Http\Controllers;

use App\Helpers\Encryption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class SessionsController extends Controller
{
    public function __construct()
    {
        $guestmethods = array(
            'create',
            'store',
        );

        $this->middleware('guest')
            ->only($guestmethods);

        $this->middleware('auth')
            ->except($guestmethods);
    }

    // GET /login
    public function create()
    {
        return view('sessions/create');
    }

    // POST /login
    public function store()
    {
        try {
            $this->validateRequest(user::getValidationRules('login'));
        } catch (ValidationException $ex) {
            return redirect(route('session.create'))
                ->withInput(request(array( // don't return the plaintext password to the view
                    'email',
                )))
                ->with('message', 'Please complete all fields');
        }

        if (! auth()->guard()->attempt(request(array('email', 'password')))) {
            return redirect(route('session.create'))
                ->withInput(request(array( // don't return the plaintext password to the view
                    'email',
                )))
                ->with('message', 'Unable to match credentials');
        }

        session()->put('encryptionkey', Encryption::makeKey(request('password')));

        return redirect(route('tokens.code'));
    }

    // GET /logout
    public function destroy()
    {
        session()->flush(); // remove encryption key
        auth()->guard()->logout();

        return redirect(route('session.create'));
    }

    // GET /profile
    public function show()
    {
        return view('sessions/show')->with('user', auth()->guard()->user());
    }

    // GET /profile/edit
    public function edit()
    {
        if (config('app.readonly')) {
            abort(404);
        }

        return view('sessions/form')->with('user', auth()->guard()->user());
    }

    // POST /profile
    public function update()
    {
        if (config('app.readonly')) {
            abort(404);
        }

        try {
            $this->validateRequest(User::getValidationRules('update'));
        } catch (ValidationException $ex) {
            return redirect(route('session.edit'))
                ->withInput(request(array( // don't return the plaintext passwords to the view
                    'name',
                    'email',
                )))
                ->with('message', 'Please check your input');
        }

        if (! auth()->guard()->validate(array(
            'email' => auth()->guard()->user()->email,
            'password' => request('currentpassword'),
        ))) {
            return redirect(route('session.edit'))
                ->withInput(request(array( // don't return the plaintext passwords to the view
                    'name',
                    'email',
                )))
                ->withErrors(array(
                    'currentpassword' => 'You did not enter your current password correctly',
                ));
        }

        $user = auth()->guard()->user();

        $user->name = request('name');
        $user->email = request('email');

        $passwordchanged = false;
        if (strlen(request('newpassword')) > 0) {
            $passwordchanged = true;

            $user->password = request('newpassword');
        }

        $user->save();

        if ($passwordchanged) {
            $newencryptionkey = Encryption::makeKey(request('newpassword'));
            foreach ($user->tokens as $token) {
                $token->secret = Encryption::encrypt(Encryption::decrypt($token->secret), $newencryptionkey);
                $token->save();
            }

            // don't need to log out others because we have already changed the hash
            // also the user model has a mutator for password and this will break it
            //Auth::logoutOtherDevices(request('newpassword'));

            session()->flush(); // remove encryption key
            auth()->guard()->logout();

            return redirect(route('session.create'));
        }

        return redirect(route('session.show'));
    }
}
