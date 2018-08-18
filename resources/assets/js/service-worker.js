/**
 * Welcome to your Workbox-powered service worker!
 *
 * You'll need to register this file in your web app and you should
 * disable HTTP caching for this file too.
 * See https://goo.gl/nhQhGp
 *
 * The rest of the code is auto-generated. Please don't update this file
 * directly; instead, make changes to your Workbox build configuration
 * and re-run your build process.
 * See https://goo.gl/2aRDsh
 */

importScripts("https://storage.googleapis.com/workbox-cdn/releases/3.4.1/workbox-sw.js");

/**
 * The workboxSW.precacheAndRoute() method efficiently caches and responds to
 * requests for URLs in the manifest.
 * See https://goo.gl/S9QRab
 */
self.__precacheManifest = [
  {
    "url": "css/app.css",
    "revision": "336c39af745d9776376edeff9911d68d"
  },
  {
    "url": "favicon-192.png",
    "revision": "9f071262efdc963720d71d528f45081d"
  },
  {
    "url": "favicon-32.png",
    "revision": "7c0134b053f032e9653e2b685d27fc5d"
  },
  {
    "url": "favicon-48.png",
    "revision": "9353f24e57c4778a13db8b6afe6fc7ac"
  },
  {
    "url": "favicon-512.png",
    "revision": "11a151ebfed7865d9db886a66f169eec"
  },
  {
    "url": "favicon-62.png",
    "revision": "671bd80a31db9d7c05933a70859f8bf2"
  },
  {
    "url": "favicon.ico",
    "revision": "8ae26dc54beea95c05a5c37e7e244166"
  },
  {
    "url": "hollow_hourglass.svg",
    "revision": "51ee870db8be9d06801b9e052db7978f"
  },
  {
    "url": "js/app.js",
    "revision": "d96bebf65f1e277a2f25930442e47141"
  }
].concat(self.__precacheManifest || []);
workbox.precaching.suppressWarnings();
workbox.precaching.precacheAndRoute(self.__precacheManifest, {});
