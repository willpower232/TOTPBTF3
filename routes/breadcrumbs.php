<?php

use App\Models\Token;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

if (! function_exists('breadcrumbsFromAPath')) {
    function breadcrumbsFromAPath(BreadcrumbTrail $trail, $path = "/")
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
    function breadcrumbsFromTokenPath(BreadcrumbTrail $trail, Token $token)
    {
        breadcrumbsFromAPath($trail, $token->path);
    }
}

Breadcrumbs::for('tokens.code', 'breadcrumbsFromAPath');
Breadcrumbs::for('tokens.export', 'breadcrumbsFromAPath');

Breadcrumbs::for('tokens.create', function (BreadcrumbTrail $trail) {
    breadcrumbsFromAPath($trail);

    $trail->push('Import', route('tokens.create'));
});

Breadcrumbs::for('tokens.show', 'breadcrumbsFromTokenPath');
Breadcrumbs::for('tokens.edit', 'breadcrumbsFromTokenPath');
Breadcrumbs::for('tokens.delete', 'breadcrumbsFromTokenPath');
