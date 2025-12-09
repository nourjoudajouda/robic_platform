@php
    $banner = getContent('banner.content', true);
@endphp

<section class="banner-section bg-img" data-background-image="{{ frontendImage('banner', @$banner->data_values->background_image, '1920x1024') }}">
    <div class="container">
        <div class="row">
            <div class="col-xxl-7 col-xl-8 col-lg-9">
                <div class="banner-content">
                    <h1 class="banner-content__title">@php echo highLightedString(@$banner->data_values->heading); @endphp</h1>
                    <p class="banner-content__desc">{{ __(@$banner->data_values->subheading) }}</p>
                    <div class="banner-content__buttons">
                        <a href="{{ @$banner->data_values->button_one_url }}" class="btn btn--base btn--lg">{{ __(@$banner->data_values->button_one) }}</a>
                        <a href="{{ @$banner->data_values->button_two_url }}" class="btn btn-outline--base btn--lg">{{ __(@$banner->data_values->button_two) }}</a>
                    </div>
                    @php
                        $lastPrice = cache('last_price');
                    @endphp
                    @if ($lastPrice) 
                        <div class="banner-content__price">
                            <span class="banner-content__price-title">@lang('Last Price'):</span>
                            <h3 class="banner-content__price-price">{{ showAmount($lastPrice) }}@lang('/g')</h3>
                        </div>
                    @endif
                </div>
            </div>
            
        </div>
    </div>
</section>