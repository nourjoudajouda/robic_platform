@extends($activeTemplate . 'layouts.app')

@section('panel')
    @php
        $login = getContent('login.content', true);
    @endphp

    <section class="account py-60 bg-img" data-background-image="{{ frontendImage('login', @$login->data_values->background_image, '1920x1024') }}">
        <div class="container">
            <div class="row justify-content-center justify-content-lg-start">
                <div class="col-xxl-5 col-lg-6 col-md-10">
                    <div class="account-form">
                        <h3 class="account-form__title">{{ __(@$login->data_values->heading) }}</h3>
                        <p class="account-form__desc">
                            {{ __(@$login->data_values->subheading) }}
                        </p>
                        <a href="{{ route('home') }}" class="account-form__back"><i class="fa-solid fa-xmark"></i></a>
                        <form action="{{ route('user.login') }}" method="POST" class="verify-gcaptcha">
                            @csrf
                            <div class="row">
                                @include($activeTemplate . 'partials.social_login')

                                <div class="col-12 form-group">
                                    <label class="form--label">@lang('Username or Email')</label>
                                    <input type="text" name="username" value="{{ old('username') }}" class="form--control">
                                </div>
                                <div class="col-12 form-group">
                                    <label class="form--label">@lang('Password')</label>
                                    <div class="position-relative">
                                        <input type="password" class="form--control" name="password" id="password" required>
                                        <span class="password-show-hide fas toggle-password fa-eye-slash" id="#password"></span>
                                    </div>
                                </div>
                                <x-captcha />
                                <div class="col-12 form-group">
                                    <div class="d-flex flex-wrap justify-content-between gy-3">
                                        <div class="form--check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="remember">@lang('Remember me')</label>
                                        </div>
                                        <a href="{{ route('user.password.request') }}" class="forgot-password text--base">@lang('Forgot your password?')</a>
                                    </div>
                                </div>
                                <div class="col-12 form-group">
                                    <button type="submit" class="btn btn--base btn--lg w-100">@lang('Login now')</button>
                                </div>
                                <div class="col-12">
                                    <div class="have-account text-center">
                                        <p class="have-account__text">@lang('Don\'t have an account?') <a href="{{ route('user.register') }}" class="have-account__link">@lang('Register now')</a></p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
