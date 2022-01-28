@props([
    'title' => '',
])

<div {{ $attributes->merge(['class' => 'sm:mx-auto sm:w-full sm:max-w-md text-white']) }}>
    <a href="{{ route('home') }}">
        <x-logo-image :white="true" class="w-auto h-16 mx-auto text-indigo-600" />
    </a>

    <div class="mb-8 text-center">
        <h1 class="mt-0 mb-4 font-light leading-none tracking-wider font-quicksand text-4xl">
            {{ config('app.name') }}
        </h1>

        <p class="m-0 font-quicksand tracking-wider font-light text-xs font-serif">Kindergarten-Manager</p>
    </div>

    <h2 class="mt-6 text-xl font-bold text-center leading-9">
        {{ $title }}
    </h2>
</div>
