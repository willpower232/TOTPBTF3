<?php
namespace App\Http\Controllers;

use App\Helpers\Encryption;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Validation\ValidationException;

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
            $this->validateRequest(array(
                'email' => 'required|string|email',
                'password' => 'required|string',
            ));
        } catch (ValidationException $ex) {
            return back()
                ->withInput(request(array( // don't return the plaintext password to the view
                    'email',
                )))
                ->with('message', 'Please complete all fields');
        }

        if (! auth()->attempt(request(array('email', 'password')))) {
            return back()
                ->withInput(request(array( // don't return the plaintext password to the view
                    'email',
                )))
                ->with('message', 'Unable to match credentials');
        }

        session()->put('encryptionkey', Encryption::makeKey(request('password')));

        return redirect('/codes');
    }

    // GET /logout
    public function destroy()
    {
        session()->flush(); // remove encryption key
        auth()->logout();

        return redirect(route('session.create'));
    }

    // GET /profile
    public function show()
    {
        return view('sessions/show')->with('user', auth()->user());
    }

    // GET /profile/edit
    public function edit()
    {
        if (config('app.readonly')) {
            abort(404);
        }

        return view('sessions/form')->with('user', auth()->user());
    }

    // POST /profile
    public function update()
    {
        if (config('app.readonly')) {
            abort(404);
        }

        try {
            $this->validateRequest(array(
                'currentpassword' => 'required|string',
                'name' => 'required|string',
                'email' => 'required|string|email',
                'newpassword' => 'confirmed',
            ));
        } catch (ValidationException $ex) {
            return back()
                ->withInput(request(array( // don't return the plaintext passwords to the view
                    'name',
                    'email',
                )))
                ->with('message', 'Please check your input');
        }

        if (! auth()->validate(array(
            'email' => auth()->user()->email,
            'password' => request('currentpassword'),
        ))) {
            return back()
                ->withInput(request(array( // don't return the plaintext passwords to the view
                    'name',
                    'email',
                )))
                ->withErrors(array(
                    'currentpassword' => 'You did not enter your current password correctly',
                ));
        }

        $user = auth()->user();

        $user->name = request('name');
        $user->email = request('email');

        $passwordchanged = false;
        if (strlen(request('newpassword')) > 0) {
            $passwordchanged = true;

            $user->password = Hash::make(request('newpassword'));
        }

        $user->save();

        if ($passwordchanged) {
            $newencryptionkey = Encryption::makeKey(request('newpassword'));
            foreach ($user->tokens as $token) {
                $token->secret = Encryption::encrypt(Encryption::decrypt($token->secret), $newencryptionkey);
                $token->save();
            }

            // just in case there were sessions from before you had this extra security enabled
            Auth::logoutOtherDevices(request('newpassword'));

            session()->flush(); // remove encryption key
            auth()->logout();

            return redirect(route('session.create'));
        }

        return redirect(route('session.show'));
    }
}
