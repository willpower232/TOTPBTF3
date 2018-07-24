<?php
namespace App\Http\Controllers;

use App\Helpers\Encryption;
use Illuminate\Support\Facades\Hash;

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
        $this->validate(request(), array(
            'email' => 'required|string|email',
            'password' => 'required|string',
        ));

        if (! auth()->attempt(request(array('email', 'password')))) {
            return back()->with('message', 'Unable to match credentials');
        }

        session()->put('encryptionkey', Encryption::makeKey(request('password')));

        return redirect('/codes');
    }

    // GET /logout
    public function destroy()
    {
        session()->flush(); // remove encryption key
        auth()->logout();

        return redirect('/login');
    }

    // GET /profile
    public function show()
    {
        return view('sessions/show')->with('user', auth()->user());
    }

    // GET /profile/edit
    public function edit()
    {
        return view('sessions/form')->with('user', auth()->user());
    }

    // POST /profile
    public function update()
    {
        $this->validate(request(), array(
            'currentpassword' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|string|email',
            'newpassword' => 'confirmed',
        ));

        if (! auth()->validate(array(
            'email' => auth()->user()->email,
            'password' => request('currentpassword'),
        ))) {
            return back()->withErrors(array(
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

            session()->flush(); // remove encryption key
            auth()->logout();

            return redirect('/login');
        }

        return redirect('/profile');
    }
}
