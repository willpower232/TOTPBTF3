# TOTPBTF3

[![Build Status](https://travis-ci.com/willpower232/TOTPBTF3.svg?branch=master)](https://travis-ci.com/willpower232/TOTPBTF3)
[![CircleCI](https://circleci.com/gh/willpower232/TOTPBTF3.svg?style=svg)](https://circleci.com/gh/willpower232/TOTPBTF3)
[![codecov](https://codecov.io/gh/willpower232/TOTPBTF3/branch/master/graph/badge.svg)](https://codecov.io/gh/willpower232/TOTPBTF3)
[![Known Vulnerabilities](https://snyk.io/test/github/willpower232/TOTPBTF3/badge.svg?targetFile=package.json)](https://snyk.io/test/github/willpower232/TOTPBTF3?targetFile=package.json)
[![Maintainability](https://api.codeclimate.com/v1/badges/e789e0cf0eea4de7ad04/maintainability)](https://codeclimate.com/github/willpower232/TOTPBTF3/maintainability)

This is me learning Laravel with an app I've wanted to make for a while.

I like TOTP 2 factor but what I don't like is not having folders in Google Authenticator and I'm getting bored of scrolling.

My entire knowledge of Laravel is based on https://laracasts.com/series/laravel-from-scratch-2017 and random problem solving.

The best way of using this app is to host your own copy on a server you control and can guarantee the security of. You have the option of using SQLite as a database (so you don't need to run a full database server) and the PWA functionality requires your hosted version has an SSL certificate.

### Probably non-standard changes to default Laravel

- Models in own folder

- reduced dev dependencies in package.json

- committing compiled assets as node is a continual disappointment in my life

- changed public directory to public_html because I prefer that directory even if mix hates me forever

- integrated the api routes into the main web routes since the api is internal only and session authenticated

## Dependencies

This has been updated to PHP 7.4 and relies on your system having access to the `openssl_*` functions as well as the database server of your choice, thanks to Laravel.

I've included [nunomaduro/larastan](https://github.com/nunomaduro/larastan) for some code analysis. Unit tests coming one day soon.

The views are written in Twig using [rcrowe/twigbridge](https://github.com/rcrowe/twigbridge) and all the two factor auth generation is handled by [robthree/twofactorauth](https://github.com/robthree/twofactorauth).

Exporting the secrets via QR code uses [bacon/baconqrcode](https://github.com/bacon/baconqrcode) but obviously this is optional and shouldn't be difficult to remove.

I've used the `fetch()` api as the browsers this ends up being used with should have it by now but if you have problems with toggling Light Mode then you should add the [GitHub Fetch Polyfill](https://github.com/github/fetch).

[ivanakimov/hashids.php](https://github.com/ivanakimov/hashids.php) is used to mask the database id of tokens for the administrative functions to prevent enumeration. You can also specify a salt for it if you wish which is recommended but not required.

The secrets are encrypted by using [defuse/php-encryption](https://github.com/defuse/php-encryption) with [KeyProtectedByPassword](https://github.com/defuse/php-encryption/blob/master/docs/classes/KeyProtectedByPassword.md) so each user has a separate key locked by their user password but the encrypted secrets do not need updating when the user password changes.

## How to use

I would recommend that you copy this repository into your own private repository to allow you to add icons and manage an SQLite backup database (see below). You can also choose to commit your `.env` with the necessary secrets.

As mentioned above, I have already committed the compiled assets as I haven't quite gotten my head around node enough to do this as part of my build process. This means the only extra installation requirement on you is to run `composer install --no-dev` or alternatively, if you're cloning directly to your server, you can run `php artisan updatefromgit` to complete the installation for you.

After migrating with `php artisan migrate`, run `php artisan user:create` to create your user(s).

Obviously the security of your code and database is down to you but as the encryption key is locked with the users password, it should be pretty safe most of the time.

### A note on redundancy

With your two factor authentication kept on your server or cloud, this creates a single point of failure which can cut you off from your codes and most likely your ability to fix the failure that keeps you from your codes.

I've added a command to export your MySQL database to SQLite which means you can host this app on a redundant server or local computer so you're not completely cut off from your codes.

`php artisan makesqlitebackup`

I've changed `app/config/database.php` so it will detect your sqlite file and use it instead of the mysql connection. You can also enable read only mode to guarantee you don't inadvertently add a code or make a change to the wrong instance of this app.

## Barcodes

I came up with too many negatives whilst planning out how to handle importing from QR codes so at the minute I'm recommending using a separate app of your choice (I use [Barcode Scanner](https://play.google.com/store/apps/details?id=com.google.zxing.client.android)) and either manually entering the secret or copying and pasting the whole `otpauth://` URL into the app.

## Images

You can also replace the text on the folder names with SVG or PNG images (SVG preferred). For example, if you have a TOTP token for a Github account belonging to Contoso, you could upload `public/tokenicons/contoso.png` and `public/tokenicons/contoso/github.png` and they would appear automatically in the list.

Finding the right icons can be tricky but I've found most icons I've needed for this can be found at [edent/SuperTinyIcons](https://github.com/edent/SuperTinyIcons) and they come in small SVG files for optimum performance.

I've included a lazyloading setting in `.env` if you're finding that you have many images on one page and its slowing down your experience.

## Service Worker

I have replaced [Google Workbox](https://developers.google.com/web/tools/workbox/modules/workbox-cli) in this project with a dynamic service worker to reduce the effort required to build this project as you no longer need to run `npm run prod` twice.

This service worker is the missing step required to make this site installable on your Android or Apple device.

Using Chrome on Androids "Add To Home screen" feature that appears when you navigate to your hosted version of this repository, you can access your TOTP codes as if they were in an app on your device. Unfortunately this app cannot work offline but at least it can integrate with your mobile device.

For iOS (as of iOS 11.3 and above), you should be able to open your hosted version in Safari, click on the Share button, and choose "Add to Home Screen".

There is some desktop capability for PWAs so it may also be possible for you to easily access your codes on a desktop computer (obviously you can also access the website) but this isn't a good idea for your personal security.

I have included lots of comments in the source of the service worker to allow debugging if it doesn't work right. If I have made any errors or am missing potential improvements in using the service worker, please let me know.

## Version Tags

### 1
This is the basic setup I was using for a number of years, not perfect but worked daily for me.

### 2
I closed off version 1 with the ability to export and import tokens which means I could update the encryption methodology to something much more reliable and secure.

### 3 (currently untagged but whatever is the latest commit)
Version 2 concluded with long overdue composer and npm updates. Version 3 introduces a few minor changes to the appearance and the ability to have different themes.

I commissioned [Soka Studio](https://sokastudio.co.uk/project/fab-app) to provide the FAB theme which is now my theme of choice.
