<!DOCTYPE html>
@php
    $currentLang = session('lang', config('app.locale'));
    $isRTL = in_array($currentLang, ['ar', 'he', 'fa', 'ur']);
@endphp
<html lang="{{ $currentLang }}" dir="{{ $isRTL ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - {{ gs()->siteName(__('Page not found')) }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ siteFavicon() }}">
    
    @if($isRTL)
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @else
        <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    @endif
    
    <link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background-image: url('{{ asset("assets/images/background.png") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            direction: {{ $isRTL ? 'rtl' : 'ltr' }};
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 0;
        }
        
        .error-container {
            text-align: center;
            padding: 40px 20px;
            max-width: 800px;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        
        .error-logo {
            margin-bottom: 30px;
        }
        
        .error-logo img {
            max-width: 200px;
            height: auto;
            filter: none;
        }
        
        .error-code {
            font-size: 150px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 10px 30px rgba(0,0,0,0.5);
            color: #fff;
        }
        
        .error-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
        }
        
        .error-description {
            font-size: 18px;
            margin-bottom: 40px;
            color: rgba(255,255,255,0.9);
            line-height: 1.6;
        }
        
        .error-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-404 {
            padding: 15px 35px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 2px solid transparent;
        }
        
        .btn-primary-404 {
            background: #81C104;
            color: #fff;
            border-color: #81C104;
            box-shadow: 0 5px 15px rgba(129, 193, 4, 0.4);
        }
        
        .btn-primary-404:hover {
            background: #6fa803;
            color: #fff;
            border-color: #6fa803;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(129, 193, 4, 0.6);
        }
        
        .btn-secondary-404 {
            background: transparent;
            color: #fff;
            border-color: #fff;
        }
        
        .btn-secondary-404:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 100px;
            }
            
            .error-title {
                font-size: 28px;
            }
            
            .error-description {
                font-size: 16px;
            }
            
            .btn-404 {
                padding: 12px 25px;
                font-size: 14px;
            }
        }
        
        .coffee-bean {
            position: absolute;
            opacity: 0.1;
            font-size: 200px;
            color: #fff;
            z-index: 0;
        }
        
        .coffee-bean-1 {
            top: 10%;
            {{ $isRTL ? 'left' : 'right' }}: 10%;
        }
        
        .coffee-bean-2 {
            bottom: 10%;
            {{ $isRTL ? 'right' : 'left' }}: 10%;
        }
    </style>
</head>
<body>
    <div class="coffee-bean coffee-bean-1">☕</div>
    <div class="coffee-bean coffee-bean-2">☕</div>
    
    <div class="error-container" style="position: relative; z-index: 1;">
        <div class="error-logo">
            <img src="{{ asset('assets/images/logo_icon/main-logo.png') }}" alt="ROBIC Logo">
        </div>
        
        <div class="error-code">404</div>
        
        <h1 class="error-title">@lang('الصفحة غير موجودة')</h1>
        
        <p class="error-description">
            @lang('عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها.')<br>
            @lang('يرجى التحقق من العنوان أو العودة إلى الصفحة الرئيسية.')
        </p>
        
        <div class="error-buttons">
            <a href="{{ route('home') }}" class="btn-404 btn-primary-404">
                <i class="fas fa-home"></i>
                <span>@lang('العودة إلى الرئيسية')</span>
            </a>
            <a href="javascript:history.back()" class="btn-404 btn-secondary-404">
                <i class="fas fa-arrow-{{ $isRTL ? 'right' : 'left' }}"></i>
                <span>@lang('العودة للخلف')</span>
            </a>
        </div>
    </div>
</body>
</html>
