@extends($activeTemplate . 'layouts.app')

@section('panel')
    @include($activeTemplate . 'partials.header')

    @if (!request()->routeIs('home') && !request()->routeIs('contact'))
        @include($activeTemplate . 'partials.breadcrumb')
    @endif

    @yield('content')

    @include($activeTemplate . 'partials.footer')
@endsection


@push('style')
    <style>
        /* Customize bootstrap container */
        @media (min-width: 1400px) {
            .container,
            .container-lg,
            .container-md,
            .container-sm,
            .container-xl,
            .container-xxl {
                max-width: 1392px;
            }
        }
    </style>
@endpush
