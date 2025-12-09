@extends($activeTemplate . 'layouts.app')

@section('panel')
    @php
        $register = getContent('register.content', true);
    @endphp

    <section class="account py-60 bg-img" data-background-image="{{ frontendImage('register', @$register->data_values->background_image, '1920x1024') }}">
        <div class="container">
            <div class="row justify-content-center justify-content-lg-start">
                <div class="col-xxl-6 col-xl-7 col-lg-8 col-md-10">
                    <div class="account-form">
                        <h3 class="account-form__title">{{ __(@$register->data_values->heading) }}</h3>
                        <p class="account-form__desc">
                            {{ __(@$register->data_values->subheading) }}
                        </p>
                        <a href="{{ route('home') }}" class="account-form__back"><i class="fa-solid fa-xmark"></i></a>
                        <form action="{{ route('user.register') }}" method="POST" class="verify-gcaptcha disableSubmission">
                            @csrf
                            <div class="row">
                                @include($activeTemplate . 'partials.social_login')

                                @if (session()->get('reference') != null)
                                    <div class="col-12 form-group">
                                        <label class="form--label">@lang('Reference by')</label>
                                        <input type="text" name="referBy" id="referenceBy" class="form-control form--control" value="{{ session()->get('reference') }}" readonly>
                                    </div>
                                @endif

                                <div class="form-group col-sm-6">
                                    <label class="form--label">@lang('First Name')</label>
                                    <input type="text" class="form--control" name="firstname" value="{{ old('firstname') }}" required>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label class="form--label">@lang('Last Name')</label>
                                    <input type="text" class="form--control" name="lastname" value="{{ old('lastname') }}" required>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form--label">@lang('E-Mail Address')</label>
                                        <input type="email" class="form--control checkUser" name="email" value="{{ old('email') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label class="form--label">@lang('Password')</label>
                                    <div class="position-relative">
                                        <input type="password" class="form--control" name="password" id="password" required>
                                        <span class="password-show-hide fas toggle-password fa-eye-slash" id="#password"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label class="form--label">@lang('Confirm Password')</label>
                                    <div class="position-relative">
                                        <input type="password" class="form--control" name="password_confirmation" id="password_confirmation" required>
                                        <span class="password-show-hide fas toggle-password fa-eye-slash" id="#password_confirmation"></span>
                                    </div>
                                </div>
                                <x-captcha />

                                @if (gs('agree'))
                                    @php
                                        $policyPages = getContent('policy_pages.element', false, orderById: true);
                                    @endphp
                                    <div class="form-group">
                                        <div class="form--check">
                                            <input type="checkbox" id="agree" @checked(old('agree')) name="agree" class="form-check-input" required>
                                            <label for="agree" class="ps-2 pe-1 fs-14">@lang('I agree with')</label> 
                                           
                                                @foreach ($policyPages as $policy)
                                                    <a href="{{ route('policy.pages', $policy->slug) }}" class="fs-14 ms-1" target="_blank">{{ __($policy->data_values->title) }}</a>
                                                    @if (!$loop->last)
                                                        ,
                                                    @endif
                                                @endforeach
                                           
                                        </div>
                                    </div>
                                @endif
                                <div class="col-12 form-group">
                                    <button type="submit" class="btn btn--base btn--lg w-100">@lang('Register')</button>
                                </div>
                                <div class="col-12">
                                    <div class="have-account text-center">
                                        <p class="have-account__text">@lang('Already have an account?') <a href="{{ route('user.login') }}" class="have-account__link">@lang('Login now')</a></p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="modal custom--modal fade" id="existModalCenter" tabindex="-1" role="dialog" aria-labelledby="existModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title " id="existModalLongTitle">@lang('You are with us')</h5>
                    <span type="button" class="close-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <p class="text text-center ">@lang('You already have an account please Login ')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--danger btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                    <a href="{{ route('user.login') }}" class="btn btn--base btn--sm">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>

@endsection



@push('script')
    <script>
        "use strict";
        (function($) {

            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';

                var data = {
                    email: value,
                    _token: token
                }

                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $('#existModalCenter').modal('show');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
