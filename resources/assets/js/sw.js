/**
 * With a bit of inspiration from
 * http://lea.verou.me/2017/10/different-remote-and-local-resource-urls-with-service-workers/
 * https://developers.google.com/web/ilt/pwa/lab-caching-files-with-service-worker
 * https://developers.google.com/web/fundamentals/primers/service-workers/
 * https://github.com/jakearchibald/simple-serviceworker-tutorial/blob/gh-pages/sw.js
 * https://googlechrome.github.io/samples/service-worker/selective-caching/service-worker.js
 */

(function() {
    if (! self.document) {
        // we are currently in a service worker

        const sitecachename = 'totpbtf3-v2';

        self.addEventListener('activate', function(ev) {
            ev.waitUntil(
                self.caches.keys().then(function(cachenames) {
                    return Promise.all(
                        cachenames.map(function(cachename) {
                            if (cachename != sitecachename) {
                                return caches.delete(cachename);
                            }
                        })
                    );
                })
            );
        });

        // act on all requests
        self.addEventListener('fetch', function(ev) {
            ev.respondWith(
                self.caches.match(ev.request)
                    .then(function(response) {
                        if (response) {
                            // 'Found ' + ev.request.url + ' in cache'
                            return response;
                        }

                        // 'Network request for ' + ev.request.url
                        return fetch(ev.request).then(function(response) {
                            // could return custom 404 here

                            /*
                                if response is not an error
                                    and response comes from same domain
                                    and response has content-type header
                                    and content-type says request was for image, css, or js
                            */
                            if (response.status < 400 &&
                                ev.request.url.match(location.hostname) &&
                                response.headers.has('Content-Type') &&
                                (
                                    response.headers.get('Content-Type').match(/^image\//i) ||
                                    response.headers.get('Content-Type').match(/\/css$/i) ||
                                    response.headers.get('Content-Type').match(/\/javascript$/i)
                                )
                            ) {
                                // 'Caching ' + ev.request.url

                                return self.caches.open(sitecachename).then(function(cache) {
                                    cache.put(ev.request.url, response.clone());
                                    return response;
                                });
                            }

                            // 'NOT caching ' + ev.request.url
                            // out = {};
                            // for (var pair of response.headers.entries()) {
                            //     out[pair[0]] = pair[1];
                            // }
                            // console.table(out);

                            return response;
                        });
                    })
                    /*.catch(function(error) {
                        console.log('offline');
                    })*/
            );
        });

        // don't continue this closure
        return;
    }

    if ("serviceWorker" in navigator) {
        // Register this script as a service worker
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js');
            /*.then(function(registration) {
                // 'ServiceWorker registration successful with scope: ' + registration.scope
            }, function(err) {
                // 'ServiceWorker registration failed: ' + err
            });*/
        });
    }
})();
