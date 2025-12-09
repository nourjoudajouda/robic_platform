@php
    $contact = getContent('contact_us.content', true);
    $footer = getContent('footer.content', true);
    $socials = getContent('social_icon.element', orderById: true);
    $policyPages = getContent('policy_pages.element', false, orderById: true);
@endphp
<footer class="{{ request()->routeIs('home') ? 'footer-area' : 'footer-area-two pt-60' }}">
    <div class="pb-60">
        <div class="container">
            <div class="row gy-4">
                <div class="col-xl-4 col-lg-5 col-sm-6 col-xsm-6 pe-xl-4">
                    <div class="footer-item">
                        <div class="footer-item__logo">
                            <a href="{{ route('home') }}"><img src="{{ siteLogo() }}" alt="image"></a>
                        </div>
                        <p class="footer-item__desc">{{ __(@$footer->data_values->short_description) }}</p>
                        <ul class="social-list">
                            @foreach ($socials as $social)
                                <li class="social-list__item">
                                    <a href="{{ @$social->data_values->url }}" class="social-list__link flex-center" target="_blank">
                                        @php
                                            echo @$social->data_values->social_icon;
                                        @endphp
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-sm-6 col-xsm-6">
                    <div class="footer-item">
                        <h5 class="footer-item__title">@lang('Company')</h5>
                        <ul class="footer-menu">
                            <li class="footer-menu__item"><a href="{{ route('home') }}" class="footer-menu__link">@lang('Home')</a></li>
                            <li class="footer-menu__item"><a href="{{ route('faq') }}" class="footer-menu__link">@lang('FAQ')</a></li>
                            <li class="footer-menu__item"><a href="{{ route('blogs') }}" class="footer-menu__link">@lang('Blog')</a></li>
                            <li class="footer-menu__item"><a href="{{ route('contact') }}" class="footer-menu__link">@lang('Contact')</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-sm-6 col-xsm-6">
                    <div class="footer-item">
                        <h5 class="footer-item__title">@lang('Useful Links')</h5>
                        <ul class="footer-menu">
                            <li class="footer-menu__item"><a href="{{ route('user.buy.form') }}" class="footer-menu__link">@lang('Buy Gold')</a></li>
                            <li class="footer-menu__item"><a href="{{ route('user.sell.form') }}" class="footer-menu__link">@lang('Sell Gold')</a></li>
                            @foreach ($policyPages as $policy)
                                <li class="footer-menu__item"><a href="{{ route('policy.pages', $policy->slug) }}" class="footer-menu__link">{{ __($policy->data_values->title) }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-3 col-sm-6 col-xsm-6">
                    <div class="footer-item">
                        <h5 class="footer-item__title">@lang('Company')</h5>
                        <div class="footer-contact">
                            <div class="footer-contact__item">
                                <h6 class="title">@lang('Address')</h6>
                                <p class="desc">{{ @$contact->data_values->address }}</p>
                            </div>
                            <div class="footer-contact__item">
                                <h6 class="title">@lang('Contact')</h6>
                                <p class="desc"><a href="tel:{{ @$contact->data_values->contact_number }}">{{ __(@$contact->data_values->contact_number) }}</a></p>
                                <p class="desc"><a href="mailto:{{ @$contact->data_values->email_address }}">{{ __(@$contact->data_values->email_address) }}</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bottom-footer py-3">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p class="bottom-footer-text text-center">&copy; {{ date('Y') }} <a href="{{ route('home') }}" class="text--base">{{ __(gs('site_name')) }}</a>. @lang('All Rights Reserved')</p>
                </div>
            </div>
        </div>
    </div>
</footer>
