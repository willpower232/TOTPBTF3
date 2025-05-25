<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

function user(): User
{
    // @codeCoverageIgnoreStart
    if (auth()->user() === null) {
        throw new Illuminate\Auth\Access\AuthorizationException();
    }
    // @codeCoverageIgnoreEnd

    return auth()->user();
}

if (! function_exists('usingsqlite')) {
    function usingsqlite(): bool
    {
        return (DB::getDriverName() === 'sqlite');
    }
}

if (! function_exists('array_merge_by_reference')) {
    /**
     * Shortcut because I was about to write a lot of array merges
     *
     * @param array<mixed> $initialarray
     * @param array<array<mixed>> $arrays
     */
    function array_merge_by_reference(&$initialarray, ...$arrays): void
    {
        array_unshift($arrays, $initialarray);
        $initialarray = call_user_func_array('array_merge', $arrays);
    }
}
