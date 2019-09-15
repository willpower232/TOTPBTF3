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
                $file = public_path('img/timer.svg');
                if (! is_file($file)) {
                    return '';
                }

                return new Twig_Markup(file_get_contents(public_path('img/timer.svg')), 'UTF-8');
            })
        );
    }
}
