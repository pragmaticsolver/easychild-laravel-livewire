<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="HandheldFriendly" content="true" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="translucent black" />

{{-- favicon start --}}
<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
<link rel="apple-touch-icon" sizes="192x192" href="/apple-icon.png">

<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">

<link rel="icon" type="image/png" sizes="192x192" href="/android-icon-192x192.png">

<link rel="manifest" href="/manifest.json">
<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="msapplication-TileImage" content="/ms-tile-144x144.png">
<meta name="theme-color" content="#F2445F">
{{-- favicon end --}}
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">

@hasSection('title')
    <title>@yield('title') - {{ config('app.name') }}</title>
@else
    <title>{{ config('app.name') }}</title>
@endif

<!-- Fonts -->

<!-- Styles -->
<style>
    [x-cloak] { display: none; }
</style>
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
@livewireStyles

<script>
    window.onload = function() {
        if('serviceWorker' in navigator) {
            navigator.serviceWorker
                .register('/sw.js')
                .then(reg => {
                    console.log('service worker registered in scope: ', reg.scope)
                })
                .catch(function(err) {
                    console.log('service worker not registered', err)
                })
        }
    };
</script>

@livewireScripts
<script src="{{ mix('js/app.js') }}" defer></script>
