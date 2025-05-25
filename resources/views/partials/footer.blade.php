@auth
	<footer>
		<a class="footer-back" href="javascript:history.back()">Back</a>

		<a class="footer-home" href="{{ route('tokens.code') }}">Home</a>

		<a class="footer-profile" href="{{ route('session.show') }}">Profile</a>
	</footer>
@endauth
