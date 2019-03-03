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

        \Breadcrumbs::macro('title', function () {
            return \Breadcrumbs::generateFromRoute()
                ->reverse()
                ->slice(0, -1)
                ->pluck('title')
                ->push(config('app.name'))
                ->implode(' - ');
        });

        \Breadcrumbs::macro('renderFromRoute', function () {
            $crumbParams = array_values(request()->route()->parameters());
            return \Breadcrumbs::render(null, ...$crumbParams);
        });

        \Breadcrumbs::macro('generateFromRoute', function () {
            $crumbParams = array_values(request()->route()->parameters());
            return \Breadcrumbs::generate(null, ...$crumbParams);
        });

        view()->composer('global', function ($view) {
            $with = $view->getData();

            try {
                $with['breadcrumbs'] = \Breadcrumbs::renderFromRoute();
                $with['title'] = \Breadcrumbs::title();
            } catch (\Throwable $e) {
                $with['breadcrumbs'] = '';
                $with['title'] = config('app.name');
            }

            $with['light_mode'] = ($user = auth()->user()) ? $user->light_mode : false;
            $with['read_only'] = config('app.readonly');
            $with['allow_export'] = config('app.allowexport');

            $view->with($with);
        });

        view()->composer('partials/header', function ($view) {
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
        $this->app->bind('path.public', function () {
            return base_path('public_html');
        });
    }
}
