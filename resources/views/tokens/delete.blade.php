@extends('global')

@section('main')
	@include('partials/header')

	<main>
		{!! $breadcrumbs !!}

		<hgroup class="a-code-header">
			<h1>{{ $token->title }}</h1>
		</hgroup>

		<p>Are you absolutely sure you wish to delete this token?</p>
		<p>You will not be able to get it back and may lose access to the system it is for.</p>

		<div class="buttons">
			<form method="POST" action="{{ route('tokens.delete', $token->id_hash) }}">
				@csrf
				@method('delete')

				<button type="submit" class="button-bad">Yes, delete this token</button>

				<a class="button button-good" href="{{ route('tokens.show', $token->id_hash) }}">No, do not delete this token</a>
			</form>
		</div>
	</main>
@endsection
