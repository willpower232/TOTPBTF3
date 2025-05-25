@extends('global')

@section('main')
	<main>
		<h1 class="login-title">Login</h1>

		<form method="POST" action="{{ route('login') }}">
			@csrf

			<div class="fieldwrapper">
				<input id="f_email" type="email"name="email" value="{{ old('email') }}" required autocomplete="false" autofocus placeholder=" " @class(['is-invalid' => $errors->has('email')]) />

				<label for="f_email">Email Address</label>

				@error('email')
					<span class="invalid-feedback">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>

			<div class="fieldwrapper">
				<input id="f_password" type="password" name="password" required autocomplete="false" placeholder=" " @class(['is-invalid' => $errors->has('password')]) />

				<label for="f_password">Password</label>

				@error('password')
					<span class="invalid-feedback">
						<strong>{{ $message }}</strong>
					</span>
				@endif
			</div>

			<label>
				<input class="form-check-input" type="checkbox" name="remember" id="remember" @checked(old('remember')) />
					Remember Me
			</label>

			<button class="button-primary" type="submit">Login</button>
		</form>

		<x-login-link email="test@example.com" />
	</main>
@endsection
