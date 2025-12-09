@extends($activeTemplate . 'layouts.frontend')

@section('content')
    @php
        $contact = getContent('contact_us.content', true);
    @endphp
    <section class="contact-section bg-img" data-background-image="{{ frontendImage('contact_us', @$contact->data_values->background_image, '1920x1024') }}">
        <div class="container">
            <div class="row gy-4">
                <div class="col-xl-6 col-lg-5">
                    <div class="contact-content">
                        <h1 class="contact-content__title">{{ __(@$contact->data_values->heading) }}</h1>
                        <p class="contact-content__desc">
                            {{ __(@$contact->data_values->subheading) }}
                        </p>
                        <ul class="contact-content__list">
                            <li class="contact-content__list-item"><i class="las la-phone"></i> <a href="tel:{{ @$contact->data_values->contact_number }}">{{ __(@$contact->data_values->contact_number) }}</a></li>
                            <li class="contact-content__list-item"><i class="las la-envelope"></i> <a href="mailto:{{ @$contact->data_values->email_address }}">{{ __(@$contact->data_values->email_address) }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-7">
                    <form method="POST" class="contact-form verify-gcaptcha">
                        @csrf
                        <h3 class="contact-form__title">{{ __(@$contact->data_values->title) }}</h3>
                        <p class="contact-form__desc">{{ __(@$contact->data_values->subtitle) }}</p>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <input name="name" type="text" class="form--control" value="{{ old('name', @$user->fullname) }}" @if ($user && $user->profile_complete) readonly @endif required placeholder="@lang('Name')">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group position-relative has-icon">
                                    <input name="email" type="email" class="form--control" placeholder="@lang('Email')" value="{{ old('email', @$user->email) }}" @if ($user) readonly @endif required>
                                    <span class="icon"><i class="las la-envelope"></i></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <input name="subject" type="text" class="form--control" value="{{ old('subject') }}" required placeholder="@lang('Subject')">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <textarea name="message" class="form--control" placeholder="@lang('Your message')" required>{{ old('message') }}</textarea>
                                </div>
                            </div>
                            <x-captcha :showLabel="false" />
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn--base btn--lg w-100">@lang('Submit')</button>
                                </div>
                            </div>
                            @if (gs('agree'))
                                @php
                                    $policyPages = getContent('policy_pages.element', false, orderById: true);
                                @endphp
                                <div class="col-12">
                                    <p class="contact-form__info mt-3">
                                        @lang('By contacting us, you agree to out ')
                                        @foreach ($policyPages as $policy)
                                            <a href="{{ route('policy.pages', $policy->slug) }}">{{ __($policy->data_values->title) }}</a>
                                            @if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    </p>
                                </div>
                            @endif

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-map-section section-bg py-120">
        <div class="container">
            <div class="row gy-4 align-items-center flex-wrap-reverse">
                <div class="col-xl-7 col-lg-6">
                    <div class="contact-map">
                        <iframe src="{{ $contact->data_values->iframe_url }}"></iframe>
                    </div>
                </div>
                <div class="col-xl-5 col-lg-6">
                    <div class="contact-map-content">
                        @php
                            echo @$contact->data_values->full_address;
                        @endphp
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if (@$sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif
@endsection
