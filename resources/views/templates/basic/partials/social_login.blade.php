@php
    $text = request()->routeIs('user.register') ? 'Register' : 'Login';
@endphp
<div class="col-12">
    @if (@gs('socialite_credentials')->google->status == Status::ENABLE)
        <a href="{{ route('user.social.login', 'google') }}" class="btn btn-outline--light btn--lg login-with w-100">
            <span class="icon"><img src="{{ getImage($activeTemplateTrue . 'images/google.svg') }}" alt="image"></span>
            <span class="text">@lang("$text with Google")</span>
        </a>
    @endif

    @if (@gs('socialite_credentials')->facebook->status == Status::ENABLE)
        <a href="{{ route('user.social.login', 'facebook') }}" class="btn btn-outline--light btn--lg login-with w-100">
            <span class="icon"><img src="{{ getImage($activeTemplateTrue . 'images/facebook.svg') }}" alt="image"></span>
            <span class="text">@lang("$text with Facebook")</span>
        </a>
    @endif

    @if (@gs('socialite_credentials')->linkedin->status == Status::ENABLE)
        <a href="{{ route('user.social.login', 'linkedin') }}" class="btn btn-outline--light btn--lg login-with w-100">
            <span class="icon"><img src="{{ getImage($activeTemplateTrue . 'images/linkdin.svg') }}" alt="image"></span>
            <span class="text">@lang("$text with Linkedin")</span>
        </a>
    @endif

    @if (@gs('socialite_credentials')->linkedin->status || @gs('socialite_credentials')->facebook->status == Status::ENABLE || @gs('socialite_credentials')->google->status == Status::ENABLE)
        <div class="other-option">
            <span class="other-option__text">@lang("Or $text with")</span>
        </div>
    @endif
</div>

@push('style')
    <style>
        .login-with:not(:last-of-type) {
            margin-bottom: 10px;
        }
    </style>
@endpush
