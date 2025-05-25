@extends('global')

@section('main')
	@include('partials/header')

	<main>
		<h1>Profile</h1>

		<dl>
			<dt>Full Name</dt>
			<dd>{{ $user->name }}</dd>

			<dt>Email</dt>
			<dd>{{ $user->email }}</dd>

			<dt>Light Mode</dt>
			<dd>
				<label class="toggle">
					<input type="checkbox" name="light_mode" disabled @checked($user->light_mode) />
					<span class="helper"></span>
				</label>
			</dd>
		</dl>

		<div class="buttons">
			@if (! $read_only)
				<a class="button button-secondary" href="{{ route('session.edit') }}">Edit</a>
			@endif

			<form method="POST" action="{{ route('logout') }}">
				@csrf
				<button class="button-bad">Logout</button>
			</form>
		</div>
	</main>
@endsection
