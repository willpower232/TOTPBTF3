<?php

namespace App\Http\Controllers;

use App\Models\Token;
use \Illuminate\Validation\ValidationException;

class TokensController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Abstracted function to find the icon for a given path.
     *
     * @param string $path the path from the uri or the token
     *
     * @return string|boolean the path to the image or false for no image
     */
    private function getImageForFolderOrToken($path = '')
    {
        // paths might have a trailing slash which wouldn't work well with an extension
        $path = rtrim($path, '/');

        foreach (array('png', 'svg') as $ext) {
            $image = 'tokenicons' . strtolower($path) . '.' . $ext;
            if (file_exists(public_path($image))) {
                return $image;
            }
        }

        return false;
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
        // guarantee input into the function
        $path = Token::formatPath($path);

        $index = substr_count($path, '/') + 1;

        $folders = (usingsqlite()) ?
            Token::select('path AS folder') :
            Token::selectRaw('SUBSTRING_INDEX(path, "/", ?) AS folder', array($index));

        $concat = (usingsqlite()) ? '? || "%"' : 'CONCAT(? ,"%")';

        $folders = $folders
            ->distinct()
            ->where('user_id', auth()->guard()->user()->id)
            ->whereRaw('path LIKE ' . $concat, array($path))
            ->orderBy('folder', 'ASC')
            ->get()->toArray();

        // sqlite has no equivalent of SUBSTRING_INDEX so we have to do this bit manually
        if (usingsqlite()) {
            // shorten all the folders to the desired sections
            $folders = array_map(function ($folder) use ($index) {
                $folder['folder'] = implode('/', array_slice(explode('/', $folder['folder']), 0, $index));

                return $folder;
            }, $folders);

            // filter out duplicate folders
            $folders = array_intersect_key($folders, array_unique(array_map('serialize', $folders)));
        }

        // if there is only one folder, make sure it matches the path
        // so this doesn't break it if theres only one token in the app
        if (count($folders) == 1 && $folders[0]['folder'] == $path) {
            return Token::where('user_id', auth()->guard()->user()->id)
                ->where('path', $path)
                ->first();
        }

        $folders = array_map(function ($folder) {
            $folder['image'] = $this->getImageForFolderOrToken($folder['folder']);

            return $folder;
        }, $folders);

        return $folders;
    }

    // GET /codes
    // display folders or 6-digit code
    public function getCode($path = '/')
    {
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
            'imageTitle' => $result->title,
            'image' => $this->getImageForFolderOrToken($result->path),
            'refreshat' => ceil(time() / 30) * 30, // nearest 30 seconds in the future
            'token' => $result,
        ));
    }

    // GET /export
    // redirect to codes folders or show qr code
    public function export($path = '/')
    {
        if (config('app.allowexport') !== true) {
            abort(404);
        }

        $result = $this->getFoldersOrTokensFromPath($path);

        if (is_array($result)) {
            // if we got here the path has to start with a slash right?
            return redirect(route('tokens.code', [$path]));
        }

        return view('tokens/export')->with(array(
            'token' => $result,
        ));
    }

    // GET /import
    // show import form
    public function create()
    {
        if (config('app.readonly')) {
            abort(404);
        }

        return view('tokens/form');
    }

    // POST /tokens
    // add new token
    public function store()
    {
        if (config('app.readonly')) {
            abort(404);
        }

        try {
            $this->validateRequest(Token::getValidationRules('create'));
        } catch (ValidationException $ex) {
            return redirect(route('tokens.create'))
                ->withInput(request()->all())
                ->with('message', 'Check your input and try again');
        }

        $secret = strtoupper(trim(str_replace(' ', '', request('secret'))));

        // if the secret starts with the expected TOTP URL scheme and has a query string,
        // we'll try to extract the actual secret
        if (strpos($secret, 'OTPAUTH://') === 0 && $querystring = parse_url($secret, PHP_URL_QUERY)) {
            parse_str($querystring, $explodedquerystring);
            if (array_key_exists('SECRET', $explodedquerystring)) {
                $secret = $explodedquerystring['SECRET'];
            }
        }

        $token = new Token(array(
            'user_id' => auth()->guard()->user()->id,
            'path' => request('path'),
            'title' => request('title'),
        ));

        // encrypt it
        $token->setSecret($secret);

        try {
            $test = $token->getTOTPCode();
        } catch (\Exception $e) {
            return back()->withInput(request()->all)->withErrors(array(
                'secret' => 'Invalid secret was entered',
            ));
        }

        $token->save();

        return redirect(route('tokens.code', [$token->path]));
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
        if (config('app.readonly')) {
            abort(404);
        }

        return view('tokens/form')->with(compact('token'));
    }

    // POST /tokens/{token}
    // update token
    public function update(Token $token)
    {
        if (config('app.readonly')) {
            abort(404);
        }

        try {
            $this->validateRequest(Token::getValidationRules('update'));
        } catch (ValidationException $ex) {
            session()->put('message', 'Check your input and try again');
            throw $ex; //carry out a redirect from laravel now that we have set a message
        }

        $token->update(request(array('path', 'title')));

        return redirect(route('tokens.code', [$token->path]));
    }

    // GET /tokens/{token}/delete
    // delete token form
    public function delete(Token $token)
    {
        if (config('app.readonly')) {
            abort(404);
        }

        return view('tokens/delete')->with(array(
            'image' => $this->getImageForFolderOrToken($token->path),
            'token' => $token,
        ));
    }

    // DELETE /tokens/{token}/delete
    // remove token from database
    public function destroy(Token $token)
    {
        if (config('app.readonly')) {
            abort(404);
        }

        $token->delete();

        return redirect(route('tokens.code'));
    }
}
