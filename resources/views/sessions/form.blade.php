@extends('global')

@section('main')
	@include('partials/header')

	<main>
		<h1>Edit Profile</h1>

		<form method="POST" action="{{ route('session.update') }}">
			@csrf

			<p>Enter your current password to confirm these changes.</p>

			<div class="fieldwrapper">
				<input id="f_currentpassword" type="password" name="currentpassword" required autocomplete="false" placeholder=" " @class(['is-invalid' => $errors->has('currentpassword')]) />

				<label for="f_currentpassword">Current Password</label>

				@error('currentpassword')
					<span class="invalid-feedback">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>

			<div class="fieldwrapper">
				<input id="f_name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="false" placeholder=" " @class(['is-invalid' => $errors->has('name')]) />

				<label for="f_name">Name</label>

				@error('name')
					<span class="invalid-feedback">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>

			<div class="fieldwrapper">
				<input id="f_email" type="text" name="email" value="{{ old('email', $user->email) }}" required autocomplete="false" placeholder=" " @class(['is-invalid' => $errors->has('email')]) />

				<label for="f_email">Email</label>

				@error('email')
					<span class="invalid-feedback">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>

			<p>If you enter a new password you will be logged out. If you do not remember your password, it cannot be recovered and your tokens will be unreadable.</p>

			<div class="fieldwrapper">
				<input id="f_newpassword" type="password" name="newpassword" autocomplete="false" placeholder=" " @class(['is-invalid' => $errors->has('newpassword')]) />

				<label for="f_newpassword">New Password</label>

				@error('newpassword')
					<span class="invalid-feedback">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>

			<div class="fieldwrapper">
				<input id="f_newpassword_confirmation" type="password" name="newpassword_confirmation" autocomplete="false" placeholder=" " @class(['is-invalid' => $errors->has('newpassword_confirmation')]) />

				<label for="f_newpassword_confirmation">Confirm New Password</label>

				@error('newpassword_confirmation')
					<span class="invalid-feedback">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>

			<div class="buttons">
				<button class="button-primary" type="submit">Save</button>
			</div>
		</form>
	</main>
@endsection
