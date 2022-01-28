<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@else
<img src="{{ asset('img/easychild.svg') }}" class="logo" style="display: block; margin: 0 auto 15px;" alt="{{ config('app.name') }}">
{{ $slot }}
@endif
</a>
</td>
</tr>
