<?php

namespace App\Providers;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->usePublicPath(base_path('public_html'));

        $this->app->bind(Hashids::class, function () {
            return new Hashids(config()->string('app.hashidssalt'));
        });

        $this->app->bind(TwoFactorAuth::class, function ($app, $args) {
            $issuer = $args['issuer'] ?? config()->string('app.name');

            // from the scss
            $text = '#d0d0d0';
            $mainbackground = '#333333';

            return new TwoFactorAuth(
                new BaconQrCodeProvider(
                    borderWidth: 1,
                    backgroundColour: $text,
                    foregroundColour: $mainbackground,
                    format: 'svg',
                ),
                $issuer,
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(app()->isLocal() || app()->runningUnitTests());

        Breadcrumbs::macro('title', function () {
            return Breadcrumbs::generateFromRoute()
                ->reverse()
                ->slice(0, -1)
                ->pluck('title')
                ->push(config()->string('app.name'))
                ->implode(' - ');
        });

        Breadcrumbs::macro('renderFromRoute', function () {
            /** @var Route $route */
            $route = request()->route();

            $crumbParams = array_values($route->parameters());
            return Breadcrumbs::render(null, ...$crumbParams);
        });

        Breadcrumbs::macro('generateFromRoute', function () {
            /** @var Route $route */
            $route = request()->route();

            $crumbParams = array_values($route->parameters());
            return Breadcrumbs::generate(null, ...$crumbParams);
        });

        Vite::macro('criticalCSS', function () {
            if (! session('hascriticalcss')) {
                session(['hascriticalcss' => true]);

                // include a timestamp so that when the content is changed in the future,
                // the cache is a bit better maybe
                // https://www.geeksforgeeks.org/how-to-set-an-expiration-date-for-items-in-a-service-worker-cache-using-javascript/

                $content = Vite::content('resources/sass/fab/critical.scss');
                $hash = Vite::manifestHash();
                $asset = Vite::asset('resources/sass/fab/critical.scss');

                return '
                    <style id="criticalcss">
                        ' . $content . '
                    </style>
                    <script>
                        if ("caches" in window) {
                            const css = document.getElementById("criticalcss").innerHTML.trim();
                            const timestamp = Date.now();
                            caches.open("totpbtf3-criticalcss-' . $hash . '").then(function(cache) {
                                cache.put("' . $asset . '", new Response(css, {
                                    headers: {
                                        "Content-Length": css.length,
                                        "Content-Type": "text/css",
                                        "X-Cache-Timestamp": timestamp,
                                    }
                                }));
                            });
                        }
                    </script>
                ';
            }

            return '<link rel="stylesheet" href="' . Vite::asset('resources/sass/fab/critical.scss') . '" />';
        });

        view()->composer(['global', 'tokens/*', 'sessions/*'], function ($view) {
            $with = $view->getData();

            try {
                $with['breadcrumbs'] = Breadcrumbs::renderFromRoute();
                $with['title'] = Breadcrumbs::title();
            } catch (\Diglactic\Breadcrumbs\Exceptions\InvalidBreadcrumbException) {
                $with['breadcrumbs'] = '';
                $with['title'] = config()->string('app.name');
            }

            $with['light_mode'] = (auth()->check()) ? user()->light_mode : false;
            $with['read_only'] = config()->boolean('app.readonly');
            $with['allow_export'] = config()->boolean('app.allowexport');

            $view->with($with);
        });
    }
}
