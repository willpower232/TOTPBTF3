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

// no homepage so put to codes
Route::permanentRedirect('/', '/codes');

// include slashes in the path
Route::get('/codes/{path?}', 'TokensController@getCode')->where('path', '(.*)');
Route::get('/export/{path?}', 'TokensController@export')->where('path', '(.*)');

Route::get('/import', 'TokensController@create');
Route::post('/tokens', 'TokensController@store');

Route::bind('token', function($tokenid) {
	return App\Models\Token::where('user_id', auth()->user()->id)
		->findOrFail($tokenid);
});

Route::get('/tokens/{token}', 'TokensController@show');
Route::get('/tokens/{token}/edit', 'TokensController@edit');
Route::get('/tokens/{token}/delete', 'TokensController@delete');
Route::delete('/tokens/{token}/delete', 'TokensController@destroy');
Route::post('/tokens/{token}', 'TokensController@update');

Route::get('/login', 'SessionsController@create')->name('login');
Route::post('/login', 'SessionsController@store');
Route::get('/logout', 'SessionsController@destroy');

Route::get('/profile', 'SessionsController@show');
Route::get('/profile/edit', 'SessionsController@edit');
Route::post('/profile', 'SessionsController@update');

Route::post('/api/profile/setLightMode', 'Api\SessionsController@setLightMode');
