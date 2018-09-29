<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // if your database type doesn't match https://laravel-news.com/laravel-5-4-key-too-long-error
        // \Illuminate\Support\Facades\Schema::defaultStringLength(255);

        view()->composer('global', function($view) {
            $view->with('light_mode', ($user = auth()->user()) ? $user->light_mode : false);
            $view->with('read_only', config('app.readonly'));
        });

        view()->composer('partials/header', function($view) {
            $view->with('is_home', (request()->segment(1) == 'codes' && count(request()->segments()) == 1));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // https://stackoverflow.com/a/30198781
        $this->app->bind('path.public', function() {
            return base_path('public_html');
        });
    }
}
