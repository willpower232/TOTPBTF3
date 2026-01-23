<?php

namespace App\Http\Controllers;

use App\Models\Token;
use Closure;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class TokensController implements HasMiddleware
{
    use ValidatesRequests;

    public static function middleware(): array
    {
        return [
            'auth',
            function (Request $request, Closure $next) {
                // ensure that the session has an encryption key during auth check in controller
                if (auth()->guard()->check() && strlen(session('encryptionkey')) < 1) {
                    auth()->guard()->logout();
                    return redirect()->to(route('login'))->with(
                        'message',
                        'Your session has expired because your encryption key is missing'
                    );
                }

                return $next($request);
            },
        ];
    }

    /**
     * Abstracted function to find the icon for a given path.
     *
     * @param string|null $path the path from the uri or the token
     *
     * @return string|false the path to the image or false for no image
     */
    private function getImageForFolderOrToken(?string $path = ''): string|false
    {
        //@codeCoverageIgnoreStart
        if ($path === null || $path == '') {
            return false;
        }
        //@codeCoverageIgnoreEnd

        // paths might have a trailing slash which wouldn't work well with an extension
        $path = rtrim($path, '/');

        foreach (['png', 'svg'] as $ext) {
            $image = 'tokenicons' . strtolower($path) . '.' . $ext;
            if (File::exists(public_path($image))) {
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
     * @return array<array<string>>|Token an array of folders or a single instance of Token, whichever is appropriate
     */
    private function getFoldersOrTokensFromPath($path = '/'): array|Token
    {
        // guarantee input into the function
        $path = Token::formatPath($path);

        $index = substr_count($path, '/') + 1;

        $folders = (usingsqlite()) ?
            Token::select('path AS folder') :
            Token::selectRaw('SUBSTRING_INDEX(path, "/", ?) AS folder', [$index]);

        $concat = (usingsqlite()) ? '? || "%"' : 'CONCAT(? ,"%")';

        $folders = $folders
            ->distinct()
            ->where('user_id', user()->id)
            ->whereRaw('path LIKE ' . $concat, [$path])
            ->orderBy('folder', 'ASC')
            ->get()
            ->map->setAppends([]) // don't append id_hash to partial selects, no base query?
            ->toArray();

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
            return Token::where('user_id', user()->id)
                ->where('path', $path)
                ->firstOrFail();
        }

        $folders = array_map(function ($folder) {
            $folder['image'] = $this->getImageForFolderOrToken($folder['folder']);

            return $folder;
        }, $folders);

        return $folders;
    }

    // GET /codes
    // display folders or 6-digit code
    public function getCode(string $path = '/'): View|JsonResponse
    {
        $result = $this->getFoldersOrTokensFromPath($path);

        if (is_array($result)) {
            // don't 404 if there are no codes on the homepage
            if (count($result) < 1 && $path != '/') {
                abort(RedirectResponse::HTTP_NOT_FOUND);
            }

            return view('tokens/list')->with([
                'folders' => $result,
                'path' => $path,
            ]);
        }

        $refreshAt = ceil(time() / 30) * 30; // nearest 30 seconds in the future

        if (request()->wantsJson()) {
            return response()->json([
                'code' => $result->getTOTPCode(),
                'refreshat' => $refreshAt,
            ]);
        }

        return view('tokens/code')->with([
            'imageTitle' => $result->title,
            'image' => $this->getImageForFolderOrToken($result->path),
            'refreshat' => $refreshAt,
            'token' => $result,
        ]);
    }

    // GET /export
    // redirect to codes folders or show qr code
    public function export(string $path = '/'): View|RedirectResponse
    {
        if (config()->boolean('app.allowexport') !== true) {
            abort(RedirectResponse::HTTP_NOT_FOUND);
        }

        $result = $this->getFoldersOrTokensFromPath($path);

        if (is_array($result)) {
            // be more blunt about the 404
            if (count($result) < 1) {
                abort(RedirectResponse::HTTP_NOT_FOUND);
            }

            // if we got here the path has to start with a slash right?
            return redirect(route('tokens.code', [$path]));
        }

        return view('tokens/export')->with([
            'token' => $result,
        ]);
    }

    // GET /import
    // show import form
    public function create(): View
    {
        if (config()->boolean('app.readonly')) {
            abort(RedirectResponse::HTTP_NOT_FOUND);
        }

        return view('tokens/form');
    }

    // POST /tokens
    // add new token
    public function store(): RedirectResponse
    {
        if (config()->boolean('app.readonly')) {
            abort(RedirectResponse::HTTP_NOT_FOUND);
        }

        try {
            $this->validate(request(), Token::getValidationRules('create'));
        } catch (ValidationException) {
            return redirect(route('tokens.create'))
                ->withInput(request()->all())
                ->with('message', 'Check your input and try again');
        }

        $secret = str_replace(' ', '', request()->string('secret'));

        $secret = strtoupper(trim($secret));

        // if the secret starts with the expected TOTP URL scheme and has a query string,
        // we'll try to extract the actual secret
        if (strpos($secret, 'OTPAUTH://') === 0 && $querystring = parse_url($secret, PHP_URL_QUERY)) {
            parse_str($querystring, $explodedquerystring);
            if (array_key_exists('SECRET', $explodedquerystring) && is_string($explodedquerystring['SECRET'])) {
                $secret = $explodedquerystring['SECRET'];
            }
        }

        $token = new Token([
            'user_id' => user()->id,
            'path' => request()->string('path'),
            'title' => request()->string('title'),
        ]);

        // encrypt it
        $token->setSecret($secret);

        try {
            $test = $token->getTOTPCode();
        } catch (\Exception) {
            return back()->withInput(request()->all)->withErrors([
                'secret' => 'Invalid secret was entered',
            ]);
        }

        $token->save();

        return redirect(route('tokens.code', [$token->path]));
    }

    // GET /tokens/{token}
    // show token details
    public function show(Token $token): View
    {
        return view('tokens/show')->with(compact('token'));
    }

    // GET /tokens/{token}/edit
    // show token edit form
    public function edit(Token $token): View
    {
        if (config()->boolean('app.readonly')) {
            abort(RedirectResponse::HTTP_NOT_FOUND);
        }

        return view('tokens/form')->with(compact('token'));
    }

    // POST /tokens/{token}
    // update token
    public function update(Token $token): RedirectResponse
    {
        if (config()->boolean('app.readonly')) {
            abort(RedirectResponse::HTTP_NOT_FOUND);
        }

        try {
            $this->validate(request(), Token::getValidationRules('update'));
        } catch (ValidationException $ex) {
            session()->put('message', 'Check your input and try again');
            throw $ex; //carry out a redirect from laravel now that we have set a message
        }

        $token->update(request()->only(['path', 'title']));

        return redirect(route('tokens.code', [$token->path]));
    }

    // GET /tokens/{token}/delete
    // delete token form
    public function delete(Token $token): View
    {
        if (config()->boolean('app.readonly')) {
            abort(RedirectResponse::HTTP_NOT_FOUND);
        }

        return view('tokens/delete')->with([
            'image' => $this->getImageForFolderOrToken($token->path),
            'token' => $token,
        ]);
    }

    // DELETE /tokens/{token}/delete
    // remove token from database
    public function destroy(Token $token): RedirectResponse
    {
        if (config()->boolean('app.readonly')) {
            abort(RedirectResponse::HTTP_NOT_FOUND);
        }

        $token->delete();

        return redirect(route('tokens.code'));
    }
}
