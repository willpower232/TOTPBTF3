let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// using scripts instead of js to avoid webpack bloating plain js - must specify full path to output file
mix.scripts('resources/assets/js/app.js', 'public/js/app.js');

mix.scripts('resources/assets/js/service-worker.js', 'public/js/service-worker.js'); 

// standard scss compile
mix.sass('resources/assets/sass/app.scss', 'public/css').options({
	autoprefixer: false
});

// auto cache bust
mix.version();

// sourcemaps only happen if minified i.e. prod
// mix.sourceMaps();

// disable notifications if building on server
// if (mix.inProduction()) {
//     mix.disableNotifications();
// }
