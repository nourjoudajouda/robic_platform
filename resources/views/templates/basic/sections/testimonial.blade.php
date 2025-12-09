@php
    $testimonial = getContent('testimonial.content', true);
    $testimonialElements = getContent('testimonial.element', orderById: false);
@endphp

<section class="testimonial-section section-bg py-60">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <h2 class="section-heading__title">{{ __(@$testimonial->data_values->heading) }}</h2>
                    <p class="section-heading__desc">{{ __(@$testimonial->data_values->subheading) }}</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="testimonial-slider">
                    <div class="swiper">
                        <div class="swiper-wrapper">
                            @foreach ($testimonialElements as $testimonialElement)
                                <div class="swiper-slide">
                                    <div class="testimonial-item">
                                        <div class="testimonial-item__thumb">
                                            <img src="{{ frontendImage('testimonial', @$testimonialElement->data_values->author_image, '320x395') }}" alt="image">
                                        </div>
                                        <div class="testimonial-item__content">
                                            <h4 class="testimonial-item__title">{{ __(@$testimonialElement->data_values->title) }}</h4>
                                            <p class="testimonial-item__desc">
                                                {{ __(@$testimonialElement->data_values->subtitle) }}
                                            </p>
                                            <div class="testimonial-item__author">
                                                <h5 class="testimonial-item__author-name fw-semibold mb-1">{{ __(@$testimonialElement->data_values->author) }}</h5>
                                                <span class="testimonial-item__author-designation">{{ __(@$testimonialElement->data_values->designation) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
    </div>
</section>
