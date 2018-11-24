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
mix.scripts('resources/assets/js/app.js', 'public_html/js/app.js');

mix.scripts('resources/assets/js/sw.js', 'public_html/sw.js');

// set the root here for sass, scripts don't follow it
mix.setPublicPath('./public_html/');

// standard scss compile
mix.sass('resources/assets/sass/app.scss', 'css').options({
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
