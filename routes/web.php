<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Models\Token;
use App\Helpers\Hashids;

// no homepage so put to codes
Route::permanentRedirect('/', '/codes');

// include slashes in the path
Route::get('/codes{path?}', 'TokensController@getCode')->where('path', '(.*)')->name('tokens.code');
Route::get('/export{path?}', 'TokensController@export')->where('path', '(.*)')->name('tokens.export');

Route::get('/import', 'TokensController@create')->name('tokens.create');

Route::bind('token', function($tokenidhash) {
	return Token::where('user_id', auth()->user()->id)
		->findOrFail(Hashids::decode($tokenidhash));
});

Route::prefix('tokens')->name('tokens')->group(function() {
	Route::post('/', 'TokensController@store')->name('.store');
	Route::get('{token}', 'TokensController@show')->name('.show');
	Route::get('{token}/edit', 'TokensController@edit')->name('.edit');
	Route::get('{token}/delete', 'TokensController@delete')->name('.delete');
	Route::delete('{token}/delete', 'TokensController@destroy')->name('.destroy');
	Route::post('{token}', 'TokensController@update')->name('.update');
});

Route::prefix('login')->name('session')->group(function() {
	Route::get('/', 'SessionsController@create')->name('.create');
	Route::post('/', 'SessionsController@store')->name('.store');
});
Route::get('/logout', 'SessionsController@destroy')->name('session.destroy');

Route::prefix('profile')->name('session')->group(function() {
	Route::get('/', 'SessionsController@show')->name('.show');
	Route::get('/edit', 'SessionsController@edit')->name('.edit');
	Route::post('/', 'SessionsController@update')->name('.update');
});

Route::post('/api/profile/setLightMode', 'Api\SessionsController@setLightMode');

Route::get('/offline', function () {
	return view('error', array(
		'errormessage' => 'offline'
	));
});
