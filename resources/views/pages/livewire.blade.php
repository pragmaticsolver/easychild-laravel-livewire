@extends(isset($pageLayout) ? $pageLayout : 'layouts.page')

@section('pageTitle')
    @if (isset($title))
        <x-h1title
            :page-title="$title"
            :navLinks="isset($navLinks) ? $navLinks : []"
            :show-child-switcher="isset($showChildSwitcher) && $showChildSwitcher"
        />
    @endif
@endsection

@section(isset($pageSection) && $pageSection ? $pageSection : 'content')
    @livewire($livewire, isset($data) ? $data : [])

    @if (isset($back))
        @php
            $routeLink = null;

            if (isset($isLinkAbsolute) && $isLinkAbsolute) {
                $routeLink = $back;
            } else {
                $routeLink = route($back);
            }
        @endphp

        <x-fab-btn
            :is-single="true"
            :single-route="$routeLink"
        />
    @endif
@endsection
