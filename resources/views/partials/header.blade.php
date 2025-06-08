<header>
	@if (isset($image) && $image !== false)
		<img src="/{{ $image }}" />
	@elseif (isset($imageTitle))
		<span class="titlereplacement">{{ $imageTitle }}</span>
	@else
		<span>TOTPBTF3</span>
	@endif
</header>
