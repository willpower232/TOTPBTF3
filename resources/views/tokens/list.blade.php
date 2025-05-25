@extends('global')

@section('main')
	@include('partials/header')

	<main>
		{!! $breadcrumbs !!}

		@empty ($folders)
			<p class="text-center">no tokens to see here</p>
		@else
			<ul class="folderlist">
				@foreach ($folders as $folder)
					@php $alt = @end(explode('/', $folder['folder'])); @endphp
					<li>
						<a href="{{ route('tokens.code', $folder['folder']) }}">
							@if ($folder['image'])
								<img @if (config()->boolean('app.lazyloading')) loading="lazy" @endif src="/{{ $folder['image'] }}" alt="{{ $alt }}" />
							@else
								<span class="replaced-image">{{ $alt }}</span>
							@endif
						</a>
					</li>
				@endforeach
			</ul>
		@endempty

		@if (! $read_only)
			<div class="buttons">
				<a class="button" href="{{ route('tokens.create') }}">Add New Token</a>
			</div>
		@endif
	</main>
@endsection
