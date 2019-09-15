let mix = require('laravel-mix');

// read theme from .env
require('dotenv').config();
var theme = process.env.MIX_THEME || 'grey';

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

mix.scripts('resources/assets/js/lazyload.js', 'public_html/js/lazyload.js');

mix.copy('resources/assets/js/intersectionobserver.min.js', 'public_html/js/intersectionobserver.min.js');

mix.scripts('resources/assets/js/sw.js', 'public_html/sw.js');

// set the root here for sass, scripts don't follow it
mix.setPublicPath('./public_html/');

// stop mix from messing with the images because we're doing that ourselves now
mix.options({
	processCssUrls: false,
	imgLoaderOptions: {
		enabled: false,
	}
});

// standard scss compile
mix.sass('resources/assets/sass/' + theme + '/critical.scss', 'css').options({
	autoprefixer: false
});
mix.sass('resources/assets/sass/' + theme + '/app.scss', 'css').options({
	autoprefixer: false
});

// auto cache bust
mix.version();

// allow themes to have an optional image directory
let fs = require('fs');
fs.stat('resources/assets/img/' + theme, function(err) {
	if (err === null) {
		mix.copyDirectory('resources/assets/img/' + theme, 'public_html/img');
	} else {
		// clean up theme changes
		const del = require('del');
		del('public_html/img');
	}
});

// sourcemaps only happen if minified i.e. prod
// mix.sourceMaps();

// disable notifications if building on server
// if (mix.inProduction()) {
//     mix.disableNotifications();
// }
