<?php

if (! function_exists('usingsqlite')) {
    function usingsqlite()
    {
        return (\DB::getDriverName() === 'sqlite');
    }
}

if (! function_exists('see_db_queries')) {
    /**
     * Shortcut to expose queries used by one or more commands
     *
     * Usage:
     *
     * see_db_queries(function() use ($object, $query) {
     *     $object->save();
     *     $test = $query->get();
     * });
     *
     * // it dd's so can't really test it right?
     * @codeCoverageIgnore
     */
    function see_db_queries(callable $callable)
    {
        \DB::enableQueryLog();
        $callable();

        // if you're using chrome, it can't render dd output in the inspector so you want this
        // if (request()->expectsJson()) {
        //     var_dump(\DB::getQueryLog());
        //     die();
        // }

        dd(\DB::getQueryLog());
    }
}

if (! function_exists('array_merge_by_reference')) {

    /**
     * Shortcut because I was about to write a lot of array merges
     */
    function array_merge_by_reference(&$initialarray, ...$arrays)
    {
        array_unshift($arrays, $initialarray);
        $initialarray = call_user_func_array('array_merge', $arrays);
    }
}
