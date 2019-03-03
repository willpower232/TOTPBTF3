<?php

use App\Models\Token;

if (! function_exists('breadcrumbsFromAPath')) {
    function breadcrumbsFromAPath($trail, $path = "/")
    {
        $trail->push('Codes', route('tokens.code'));

        $carry = "";
        foreach (explode('/', $path) as $segment) {
            if ($segment == "") {
                continue;
            }
            $carry .= '/' . $segment;
            $trail->push($segment, route('tokens.code', [$carry]));
        }
    }
}

if (! function_exists('breadcrumbsFromTokenPath')) {
    function breadcrumbsFromTokenPath($trail, Token $token)
    {
        breadcrumbsFromAPath($trail, $token->path);
    }
}

Breadcrumbs::for('tokens.code', 'breadcrumbsFromAPath');
Breadcrumbs::for('tokens.export', 'breadcrumbsFromAPath');

Breadcrumbs::for('tokens.create', function ($trail) {
    breadcrumbsFromAPath($trail);

    $trail->push('Import', route('tokens.create'));
});

Breadcrumbs::for('tokens.show', 'breadcrumbsFromTokenPath');
Breadcrumbs::for('tokens.edit', 'breadcrumbsFromTokenPath');
Breadcrumbs::for('tokens.delete', 'breadcrumbsFromTokenPath');

// Breadcrumbs::for('session.show', function ($trail) {

// });
// Breadcrumbs::for('session.edit', function ($trail) {

// });
// Breadcrumbs::for('session.create', function ($trail) {

// });
