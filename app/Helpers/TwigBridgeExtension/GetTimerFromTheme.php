<?php
namespace App\Helpers\TwigBridgeExtension;

use Twig_Extension;
use Twig_SimpleFunction;
use Twig_Markup;

class GetTimerFromTheme extends Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('getTimerFromTheme', function () {
                return new Twig_Markup(file_get_contents(public_path('hollow_hourglass.svg')), 'UTF-8');
            })
        );
    }
}
