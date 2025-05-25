<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\TokensController;
use App\Models\Token;
use Hashids\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes([
    'register' => false,
    'reset' => false,
    'confirm' => false,
    'verify' => false,
]);

Route::permanentRedirect('/', '/codes');

Route::bind('token', function (string $tokenidhash) {
    $decoded = app(Hashids::class)->decode($tokenidhash);

    if (count($decoded) != 1) {
        abort(404);
    }

    return Token::where('user_id', user()->id)
        ->findOrFail($decoded[0]);
});

Route::controller(TokensController::class)->name('tokens')->group(function () {
    Route::get('/codes{path?}', 'getCode')->where('path', '(.*)')->name('.code');
    Route::get('/export{path?}', 'export')->where('path', '(.*)')->name('.export');

    Route::get('/import', 'create')->name('.create');

    Route::prefix('tokens')->group(function () {
        Route::post('/', 'store')->name('.store');
        Route::get('{token}', 'show')->name('.show');
        Route::get('{token}/edit', 'edit')->name('.edit');
        Route::get('{token}/delete', 'delete')->name('.delete');
        Route::delete('{token}/delete', 'destroy')->name('.destroy');
        Route::post('{token}', 'update')->name('.update');
    });
});

Route::controller(SessionsController::class)->prefix('profile')->name('session')->group(function() {
    Route::get('/', 'show')->name('.show');
    Route::get('/edit', 'edit')->name('.edit');
    Route::post('/', 'update')->name('.update');
});

Route::post('/api/profile/setLightMode', [Api\SessionsController::class, 'setLightMode']);
