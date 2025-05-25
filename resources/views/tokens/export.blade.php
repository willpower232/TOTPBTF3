@extends('global')

@section('main')
	@include('partials/header')

	<main>
		{!! $breadcrumbs !!}

		<h1>{{ $token->title }}</h1>

		<div class="export">
			{!! $token->getQRCode() !!}
		</div>

		<div class="buttons">
			<a class="button button-primary" href="{{ route('tokens.show', $token->id_hash) }}">View Token</a>
		</div>
	</main>
@endsection
