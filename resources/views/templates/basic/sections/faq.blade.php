@php
    $faq = getContent('faq.content', true);
    $faqElements = getContent('faq.element', orderById: false);
@endphp

<div class="faq-section section-bg py-60">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-lg-6">
                <div class="section-heading">
                    <h2 class="section-heading__title">{{ __(@$faq->data_values->heading) }}</h2>
                    <p class="section-heading__desc">
                        {{ __(@$faq->data_values->subheading) }}
                    </p>
                </div>
                <div class="custom--accordion accordion accordion-flush" id="accordionFlushExample">
                    @foreach ($faqElements as $faqElement)
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqAccordion{{ $faqElement->id }}" aria-expanded="false" aria-controls="faqAccordion{{ $faqElement->id }}">{{ __(@$faqElement->data_values->question) }}</button>
                            </h6>
                            <div id="faqAccordion{{ $faqElement->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                <div class="accordion-body">
                                    {{ __(@$faqElement->data_values->answer) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6">
                <div class="faq-right">
                    <div class="faq-right__thumb">
                        <img src="{{ frontendImage('faq', @$faq->data_values->image, '670x555') }}" alt="image">
                        <div class="faq-right__content">
                            <h3 class="faq-right__content-title">{{ __(@$faq->data_values->title) }}</h3>
                            <p class="faq-right__content-desc">{{ __(@$faq->data_values->subtitle) }}</p>
                            <a href="{{ @$faq->data_values->button_url }}" class="btn btn--base">{{ __(@$faq->data_values->button_text) }}</a>
                        </div>    
                    </div>
                    <img class="faq-right__shape" src="{{ $activeTemplateTrue.'images/faq-thumb-shape.png' }}" alt="image">
                </div>
            </div>
        </div>
    </div>
</div>