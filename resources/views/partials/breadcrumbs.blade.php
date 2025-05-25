@if (count($breadcrumbs) > 0)
	<nav class="breadcrumbs">
		@foreach ($breadcrumbs as $breadcrumb)
			@if ($breadcrumb->url and $loop->last === false)
				<a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a>
			@else
				<a>{{ $breadcrumb->title }}</a>
			@endif
		@endforeach
	</nav>
@endif
