@extends('global')

@section('main')
	@include('partials/header')

	<main>
		{!! $breadcrumbs !!}

		<h1>{{ (isset($token)) ? 'Edit Token' : 'Import New Token' }}</h1>

		<form method="POST" action="{{ (isset($token)) ? route('tokens.update', $token->id_hash) : route('tokens.store') }}">
			@csrf

			<div class="fieldwrapper">
				<input id="f_path" type="text" name="path" value="{{ old('path', $token?->path ?? '') }}" required autocomplete="false" autofocus placeholder=" " @class(['is-invalid' => $errors->has('path')]) />

				<label for="f_path">Path</label>

				@error('path')
					<span class="invalid-feedback">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>

			<div class="fieldwrapper">
				<input id="f_title" type="text" name="title" value="{{ old('title', $token?->title ?? '') }}" required autocomplete="false" placeholder=" " @class(['is-invalid' => $errors->has('title')]) />

				<label for="f_title">Title</label>

				@error('title')
					<span class="invalid-feedback">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>

			@if (! isset($token))
				<div class="fieldwrapper">
					<input id="f_secret" type="text" name="secret" value="{{ old('secret') }}" required autocomplete="false" placeholder=" " @class(['is-invalid' => $errors->has('scret')]) />

					<label for="f_secret">Secret</label>

					@error('secret')
						<span class="invalid-feedback">
							<strong>{{ $message }}</strong>
						</span>
					@enderror

					<p>You can obtain a secret in a couple of ways. All spaces will be removed.</p>
					<ul>
						<li>Asking your provider for the code "to enter manually"</li>
						<li>Copying and pasting the "otpauth://" URL from a barcode reader into this form. The secret will be extracted and other values discarded.</li>
					</ul>
				</div>
			@endif

			<div class="buttons">
				<button class="button-primary" type="submit">Save</button>
			</div>
		</form>
	</main>
@endsection
