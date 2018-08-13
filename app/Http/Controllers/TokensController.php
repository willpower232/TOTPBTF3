<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Helpers\Encryption;

class TokensController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Abstracted query to return either the folder details or a token to show.
     *
     * @param string $path the folder of a token, expected to be formatted by the function from the Token model
     *
     * @return array|Token an array of folders or a single instance of Token, whichever is appropriate
     */
    private function getFoldersOrTokensFromPath($path = '/')
    {
        $folders = Token::selectRaw('SUBSTRING_INDEX(path, "/", ' . (substr_count($path, '/') + 1) . ') AS folder')
            ->distinct()
            ->where('user_id', auth()->user()->id)
            ->where('path', 'LIKE', $path . '%')
            ->orderBy('folder', 'ASC')
            ->get()->toArray();

        // if there is only one folder, make sure it matches the path
        // so this doesn't break it if theres only one token in the app
        if (count($folders) == 1 && $folders[0]['folder'] == $path) {
            return Token::where('user_id', auth()->user()->id)
                ->where('path', $path)
                ->first();
        }

        $folders = array_map(function($folder) {
            foreach (array('png', 'svg') as $ext) {
                $image = 'img' . strtolower($folder['folder']) . '.' . $ext;
                if (file_exists(public_path($image))) {
                    $folder['image'] = $image;
                }
            }

            return $folder;
        }, $folders);

        return $folders;
    }

    // GET /codes
    // display folders or 6-digit code
    public function getCode($path = '/')
    {
        // format the path here so the path that goes to the view looks good too
        $path = Token::formatPath($path);

        $result = $this->getFoldersOrTokensFromPath($path);

        if (is_array($result)) {
            // don't 404 if there are no codes on the homepage
            if (count($result) < 1 && $path != '/') {
                abort(404);
            }

            return view('tokens/list')->with(array(
                'folders' => $result,
                'path' => $path,
            ));
        }

        return view('tokens/code')->with(array(
            'refreshat' => ceil(time() / 30) * 30, // nearest 30 seconds in the future
            'token' => $result,
        ));
    }

    // GET /export
    // redirect to codes folders or show qr code
    public function export($path = '/')
    {
        // format the path here because of early formatting in getCode
        $path = Token::formatPath($path);

        $result = $this->getFoldersOrTokensFromPath($path);

        if (is_array($result)) {
            return redirect('/codes' . $path);
        }

        return view('tokens/export')->with(array(
            'token' => $result,
        ));
    }

    // GET /import
    // show import form
    public function create()
    {
        return view('tokens/form');
    }

    // POST /tokens
    // add new token
    public function store()
    {
        $this->validate(request(), array(
            'path' => 'required',
            'title' => 'required',
            'secret' => 'required',
        ));

        $token = Token::create(array(
            'user_id' => auth()->user()->id,
            'path' => Token::formatPath(request('path')),
            'title' => request('title'),
            'secret' => Encryption::encrypt(strtoupper(trim(str_replace(' ', '', request('secret'))))),
        ));

        return redirect('/codes' . $token->path);
    }

    // GET /tokens/{token}
    // show token details
    public function show(Token $token)
    {
        return view('tokens/show')->with(compact('token'));
    }

    // GET /tokens/{token}/edit
    // show token edit form
    public function edit(Token $token)
    {
        return view('tokens/form')->with(compact('token'));
    }

    // POST /tokens/{token}
    // update token
    public function update(Token $token)
    {
        $this->validate(request(), array(
            'path' => 'required',
            'title' => 'required',
        ));

        $token->update(request(array('path', 'title')));

        return redirect('/codes' . $token->path);
    }
}
