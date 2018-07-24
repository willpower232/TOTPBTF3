# TOTPBTF3

This is me learning Laravel with an app I've wanted to make for a while.

I like TOTP 2 factor but what I don't like is not having folders in Google Authenticator and I'm getting bored of scrolling.

My entire knowledge of Laravel is based on https://laracasts.com/series/laravel-from-scratch-2017 and random problem solving.

### Probably non-standard changes to default Laravel

- Models in own folder

- reduced dev dependencies in package.json

- committing compiled assets as node is a continual disappointment in my life

## How to use

After cloning and migrating, run this command to create your user.

`php artisan user:create`

Don't forget to set an `ENCRYPTION_SALT` in `.env` and never change it as it would ruin your ability to decrypt your tokens.

Obviously the security of your code and database is down to you but as the encryption key is based on the users password, it should be pretty safe most of the time.

## Images

You can also replace the text on the folder names with SVG or PNG images (SVG preferred). For example, if you have a TOTP token for a Github account belonging to Contoso, you could upload `public/img/contoso.png` and `public/img/contoso/github.png` and they would appear automatically in the list.
