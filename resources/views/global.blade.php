<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['inverted' => $light_mode])>

<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

@if (! isset($errormessage))
	<meta name="csrf-token" content="{{ csrf_token() }}">
@endif

<title>{{ $title }}</title>
<meta name="robots" content="noindex, nofollow" />

{!! Vite::criticalCSS() !!}

@if (! isset($errormessage))
	@vite(['resources/sass/fab/app.scss', 'resources/js/app.js'])
@endif

<link rel="icon" href="/favicon-32.png" sizes="32x32" />
<link rel="icon" href="/favicon-48.png" sizes="48x48" />
<link rel="icon" href="/favicon-62.png" sizes="62x62" />
<link rel="icon" href="/favicon-192.png" sizes="192x192" />

<link rel="manifest" href="/manifest.webmanifest" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<meta name="theme-color" content="#515151" />

<link rel="apple-touch-icon" href="/favicon-180.png" />

@if (! isset($errormessage))
	<noscript>
		<style>
			body {
				padding-top:3em;
			}
			body:before {
				content: 'This website requires javascript for optimum use, please enable.';
			}
		</style>
	</noscript>
@endif

<body>
    @env('local')
        <span class="using-database">{{ usingsqlite() ? 'sqlite' : 'mysql' }}</span>
    @endenv

	@section('wholepage')
		@yield('main')

		@include('partials.footer')
	@show
</body></html>

<!--
needless to say, the reverse dutch classic
overarm was now out of the question
-->
