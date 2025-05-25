@extends('global')

@section('main')
	@include('partials/header')

	<main>
		{!! $breadcrumbs !!}

		<h1>Token</h1>

		<dl>
			<dt>Path</dt>
			<dd>{{ $token->path }}</dd>

			<dt>Title</dt>
			<dd>{{ $token->title }}</dd>
		</dl>

		<div class="buttons">
			@if (! $read_only)
				<a class="button" href="{{ route('tokens.edit', $token->id_hash) }}">Edit</a>
			@endif

			<a class="button button-secondary" href="{{ route('tokens.code', $token->path) }}">Code</a>

			@if ($allow_export)
				<a class="button button-secondary" href="{{ route('tokens.export', $token->path) }}">Export</a>
			@endif

			@if (! $read_only)
				<a class="button button-bad" href="{{ route('tokens.delete', $token->id_hash) }}" onclick="return confirm('Are you sure you want to delete this token?')">Delete</a>
			@endif
		</div>
	</main>
@endsection
