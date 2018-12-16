<?php

if (! function_exists('usingsqlite')) {
    function usingsqlite()
    {
        return (\DB::getDriverName() === 'sqlite');
    }
}
