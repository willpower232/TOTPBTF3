# TOTPBTF3

[![Build Status](https://travis-ci.com/willpower232/TOTPBTF3.svg?branch=master)](https://travis-ci.com/willpower232/TOTPBTF3)

This is me learning Laravel with an app I've wanted to make for a while.

I like TOTP 2 factor but what I don't like is not having folders in Google Authenticator and I'm getting bored of scrolling.

My entire knowledge of Laravel is based on https://laracasts.com/series/laravel-from-scratch-2017 and random problem solving.

### Probably non-standard changes to default Laravel

- Models in own folder

- reduced dev dependencies in package.json

- committing compiled assets as node is a continual disappointment in my life

- changed public directory to public_html because I prefer that directory even if mix hates me forever

## Dependencies

This has only been tested on PHP 7.1 so far and relies on your system having access to the `openssl_*` functions as well as the database server of your choice, thanks to Laravel.

I've included [nunomaduro/larastan](https://github.com/nunomaduro/larastan) for some code analysis. Unit tests coming one day soon.

The views are written in Twig using [rcrowe/twigbridge](https://github.com/rcrowe/twigbridge) and all the two factor auth generation is handled by [robthree/twofactorauth](https://github.com/robthree/twofactorauth).

Exporting the secrets via QR code uses [bacon/baconqrcode](https://github.com/bacon/baconqrcode) but obviously this is optional and shouldn't be difficult to remove.

## How to use

After cloning and migrating, run this command to create your user.

`php artisan user:create`

Don't forget to set an `ENCRYPTION_SALT` in `.env` and never change it as it would ruin your ability to decrypt your tokens.

Obviously the security of your code and database is down to you but as the encryption key is based on the users password, it should be pretty safe most of the time.

### A note on redundancy

With your two factor authentication kept on your server or cloud, this creates a single point of failure which can cut you off from your codes and most likely your ability to fix the failure that keeps you from your codes.

I've added a command to export your MySQL database to SQLite which means you can host this app on a redundant server or local computer so you're not completely cut off from your codes.

`php artisan makesqlitebackup`

I've changed `app/config/database.php` so it will detect your sqlite file and use it instead of the mysql connection. You can also enable read only mode to guarantee you don't inadvertently add a code or make a change to the wrong instance of this app.

## Barcodes

I came up with too many negatives whilst planning out how to handle importing from QR codes so at the minute I'm recommending using a separate app of your choice (I use [Barcode Scanner](https://play.google.com/store/apps/details?id=com.google.zxing.client.android)) and either manually entering the secret or copying and pasting the whole `otpauth://` URL into the app.

## Images

You can also replace the text on the folder names with SVG or PNG images (SVG preferred). For example, if you have a TOTP token for a Github account belonging to Contoso, you could upload `public/img/contoso.png` and `public/img/contoso/github.png` and they would appear automatically in the list.

Finding the right icons can be tricky but I've found most icons I've needed for this can be found at [edent/SuperTinyIcons](https://github.com/edent/SuperTinyIcons) and they come in small SVG files for optimum performance.

## Service Worker

Using [Google Workbox](https://developers.google.com/web/tools/workbox/modules/workbox-cli) there is a small service worker which is the missing step required to make this site installable on Android using Chromes "Add To Home screen" feature. Unfortunately this app cannot work offline but at least it can integrate with your Android mobile device.

For efficiency, you may wish the service worker to cache any additional images you have uploaded so you will need to run the following command: `node node_modules/workbox-cli/build/bin.js generateSW workbox-config.js`
