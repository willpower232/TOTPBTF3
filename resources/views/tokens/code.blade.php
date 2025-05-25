@extends('global')

@section('main')
	@include('partials/header')

	<main>
		{!! $breadcrumbs !!}

		<code-and-timer refresh-at="@json($refreshat)">
			<p class="text-center a-code">{{ $token->getTOTPCode() }}</p>
			<div class="a-timer"><div class="timer">{!! Vite::content('resources/img/fab/timer.svg') !!}</div></div>
		</code-and-timer>

		<div class="buttons">
			<button class="button-primary js-copy" data-copies=".a-code">Copy Code</button>

			<a class="button button-secondary" href="{{ route('tokens.show', $token->id_hash) }}">View Token</a>
		</div>
	</main>
@endsection
