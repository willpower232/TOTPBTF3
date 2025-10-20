<header>
	@if (isset($image) && $image !== false)
		<img src="/{{ $image }}" />
	@elseif (isset($imageTitle))
		<span class="titlereplacement">{{ $imageTitle }}</span>
	@else
		<span>{{ config('app.name') }}</span>
	@endif
</header>
