# TOTPBTF3

[![Build Status](https://travis-ci.com/willpower232/TOTPBTF3.svg?branch=master)](https://travis-ci.com/willpower232/TOTPBTF3)

This is me learning Laravel with an app I've wanted to make for a while.

I like TOTP 2 factor but what I don't like is not having folders in Google Authenticator and I'm getting bored of scrolling.

My entire knowledge of Laravel is based on https://laracasts.com/series/laravel-from-scratch-2017 and random problem solving.

### Probably non-standard changes to default Laravel

- Models in own folder

- reduced dev dependencies in package.json

- committing compiled assets as node is a continual disappointment in my life

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

## Images

You can also replace the text on the folder names with SVG or PNG images (SVG preferred). For example, if you have a TOTP token for a Github account belonging to Contoso, you could upload `public/img/contoso.png` and `public/img/contoso/github.png` and they would appear automatically in the list.

Finding the right icons can be tricky but I've found most icons I've needed for this can be found at [edent/SuperTinyIcons](https://github.com/edent/SuperTinyIcons) and they come in small SVG files for optimum performance.
