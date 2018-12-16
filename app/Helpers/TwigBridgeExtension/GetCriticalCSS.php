<?php
namespace App\Helpers\TwigBridgeExtension;

use Twig_Extension;
use Twig_SimpleFunction;
use Twig_Markup;

class GetCriticalCSS extends Twig_Extension
{
    public function getFunctions()
    {
        return array(
            /**
             * Based on https://www.filamentgroup.com/lab/inlining-cache.html
             * - Output the critical CSS to the page on first load in session to attempt to cache it
             * - Second load gets link tag but hopefully no network hit due to service worker
             * - Note that a separate cache name is used but must not be deleted
             *      by service worker cleanup in activate event listener
             */
            new Twig_SimpleFunction('getCriticalCSS', function () {
                if (! session('hascriticalcss')) {
                    session(array(
                        'hascriticalcss' => true,
                    ));
                    return new Twig_Markup('
                        <style id="criticalcss">
                            ' . file_get_contents(public_path('css/critical.css')) . '
                        </style>
                        <script>
                            if ("caches" in window) {
                                var css = document.getElementById("criticalcss").innerHTML.trim();
                                caches.open("totpbtf3-criticalcss-v1").then(function(cache) {
                                    cache.put("' . mix('css/critical.css') . '", new Response(css, {
                                        headers: {
                                            "Content-Length": css.length,
                                            "Content-Type": "text/css"
                                        }
                                    }));
                                });
                            }
                        </script>
                    ', 'UTF-8');
                }

                return new Twig_Markup('<link rel="stylesheet" href="' . mix('css/critical.css') . '" />', 'UTF-8');
            })
        );
    }
}
