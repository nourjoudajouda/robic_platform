<!-- meta tags and other links -->
@php
    $currentLang = session('lang', config('app.locale'));
    $isRTL = in_array($currentLang, ['ar', 'he', 'fa', 'ur']);
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $isRTL ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ gs() ? gs()->siteName($pageTitle ?? '') : ($pageTitle ?? 'Admin Panel') }}</title>

    <link rel="shortcut icon" type="image/png" href="{{siteFavicon()}}">
    {{-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"> --}}
    @if($isRTL)
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{asset('assets/admin/css/vendor/bootstrap-toggle.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/css/all.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/css/line-awesome.min.css')}}">

    @stack('style-lib')

    <link rel="stylesheet" href="{{asset('assets/global/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/app.css')}}">

    @if($isRTL)
    <style>
        body, 
        body p, body span, body div, body a, body button, body input, body textarea, body select,
        body h1, body h2, body h3, body h4, body h5, body h6,
        body li, body td, body th, body label, body strong, body b, body em,
        body .form-control, body .btn, body .card, body .table {
            font-family: 'Cairo', sans-serif !important;
        }
        /* Keep icon fonts as they are */
        body [class^="las"], body [class^="fa"], body [class*=" las"], body [class*=" fa"],
        body .line-awesome, body .fontawesome-iconpicker {
            font-family: "Line Awesome Free", "Font Awesome 5 Free", "Font Awesome 6 Free" !important;
        }
    </style>
    @endif

    @stack('style')
</head>
<body>
@yield('content')


<script src="{{asset('assets/global/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/global/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/admin/js/vendor/bootstrap-toggle.min.js')}}"></script>


@include('partials.notify')
@stack('script-lib')

<script src="{{ asset('assets/global/js/nicEdit.js') }}"></script>

<script src="{{asset('assets/global/js/select2.min.js')}}"></script>
<script src="{{asset('assets/admin/js/app.js')}}"></script>

{{-- LOAD NIC EDIT --}}
<script>
    "use strict";
    bkLib.onDomLoaded(function() {
        $( ".nicEdit" ).each(function( index ) {
            $(this).attr("id","nicEditor"+index);
            new nicEditor({fullPanel : true}).panelInstance('nicEditor'+index,{hasPanel : true});
        });
    });
    (function($){
        $( document ).on('mouseover ', '.nicEdit-main,.nicEdit-panelContain',function(){
            $('.nicEdit-main').focus();
        });

        $('.breadcrumb-nav-open').on('click', function() {
            $(this).toggleClass('active');
            $('.breadcrumb-nav').toggleClass('active');
        });

        $('.breadcrumb-nav-close').on('click', function() {
            $('.breadcrumb-nav').removeClass('active');
        });

        if($('.topTap').length){
            $('.breadcrumb-nav-open').removeClass('d-none');
        }
    })(jQuery);
</script>

@stack('script')


</body>
</html>
