<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class AddEncryptionKeyToSession
{
    public function handle(Login $event): void
    {
        // @codeCoverageIgnoreStart
        if (! $event->user instanceof User) {
            throw new \RuntimeException('who are you?');
        }

        if (! request()->has('password')) {
            throw new \RuntimeException('we need the password in the request to decode the encryption key');
        }
        // @codeCoverageIgnoreEnd

        $event->user->putEncryptionKeyInSession(request()->string('password'));
    }
}
