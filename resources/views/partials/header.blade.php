<header>
	@if (isset($image))
		<img src="/{{ $image }}" />
	@elseif (isset($imageTitle))
		<span class="titlereplacement">{{ $imageTitle }}</span>
	@else
		<span>TOTPBTF3</span>
	@endif
</header>
