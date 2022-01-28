@props([
    'title' => '',
])

<div class="sm:mx-auto sm:w-full sm:max-w-md text-white">
    <a href="{{ route('home') }}">
        <x-logo-image :white="true" class="w-auto h-24 mx-auto text-indigo-600" />
    </a>

    <div class="mb-8 text-center">
        <h1 class="mt-0 mb-4 font-light leading-none tracking-wider font-quicksand text-5xl sm:text-6xl">
            {{ config('app.name') }}
        </h1>
        <p class="m-0 font-quicksand tracking-wider font-light text-sm sm:text-base md:text-lg font-serif">Kindergarten-Manager</p>
    </div>

    <h2 class="mt-6 text-3xl font-extrabold text-center leading-9">
        {{ $title }}
    </h2>
</div>
