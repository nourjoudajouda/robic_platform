@php
    $feature = getContent('feature.content', true);
    $featureElements = getContent('feature.element', false, orderById: true);
@endphp

<div class="feature-section section-bg py-60">
    <div class="container">
        <div class="row gy-4 align-items-center flex-wrap-reverse">
            <div class="col-lg-6">
                <div class="feature-thumb">
                    <div class="feature-thumb__item">
                        <span class="feature-thumb__icon">@php echo @$feature->data_values->icon @endphp</span>
                        <img src="{{ frontendImage('feature', @$feature->data_values->top_image, '670x300') }}" alt="@lang('image')">
                    </div>
                    <div class="feature-thumb__item">
                        <img src="{{ frontendImage('feature', @$feature->data_values->left_image, '325x300') }}" alt="@lang('image')">
                    </div>
                    <div class="feature-thumb__item">
                        <img src="{{ frontendImage('feature', @$feature->data_values->right_image, '325x300') }}" alt="@lang('image')">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="feature-content">
                    <h2 class="feature-content__title">{{ __(@$feature->data_values->heading) }}</h2>
                    <p class="feature-content__desc">{{ __(@$feature->data_values->subheading) }}</p>
                    @foreach ($featureElements as $featureElement)
                        <div class="feature-item">
                            <span class="feature-item__icon">@php echo @$featureElement->data_values->icon @endphp</span>
                            <div class="feature-item__content">
                                <h4 class="feature-item__title">{{ __(@$featureElement->data_values->title) }}</h4>
                                <p class="feature-item__desc">
                                    {{ __(@$featureElement->data_values->subtitle) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
