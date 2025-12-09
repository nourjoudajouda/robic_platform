@php
    $howItWorks = getContent('how_it_works.content', true);
    $howItWorksStepOne = getContent('how_it_works_step_one.content', true);
    $howItWorksStepTwo = getContent('how_it_works_step_two.content', true);
    $howItWorksStepThree = getContent('how_it_works_step_three.content', true);
@endphp
<section class="how-it-works-section section-bg py-60 bg-img" data-background-image="{{ frontendImage('how_it_works', @$howItWorks->data_values->background_image, '1920x1215') }}">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <h2 class="section-heading__title">{{ __(@$howItWorks->data_values->heading) }}</h2>
                    <p class="section-heading__desc">{{ __(@$howItWorks->data_values->subheading) }}</p>
                </div>
            </div>
        </div>
        <div class="row gy-4 justify-content-center work-process-row-one position-relative">
            <span class="how-it-work-shape-01"><img src="{{ $activeTemplateTrue.'images/shape-01.png' }}" alt="image"></span>
            <div class="col-lg-4 col-md-6">
                <div class="gradient-border">
                    <div class="work-process">
                        <span class="work-process__number">@lang('1')</span>
                        <span class="work-process__icon"><img src="{{ frontendImage('how_it_works_step_one', @$howItWorksStepOne->data_values->image_one, '65x65') }}" alt="image"></span>
                        <h4 class="work-process__title">{{ __(@$howItWorksStepOne->data_values->title_one) }}</h4>
                        <p class="work-process__desc">
                            {{ __(@$howItWorksStepOne->data_values->subtitle_one) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="gradient-border">
                    <div class="work-process">
                        <span class="work-process__number">@lang('2')</span>
                        <span class="work-process__icon"><img src="{{ frontendImage('how_it_works_step_one', @$howItWorksStepOne->data_values->image_two, '65x65') }}" alt="image"></span>
                        <h4 class="work-process__title">{{ __(@$howItWorksStepOne->data_values->title_two) }}</h4>
                        <p class="work-process__desc">{{ __(@$howItWorksStepOne->data_values->subtitle_two) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="gradient-border">
                    <div class="work-process">
                        <span class="work-process__number">@lang('3')</span>
                        <span class="work-process__icon"><img src="{{ frontendImage('how_it_works_step_one', @$howItWorksStepOne->data_values->image_three, '65x65') }}" alt="image"></span>
                        <h4 class="work-process__title">{{ __(@$howItWorksStepOne->data_values->title_three) }}</h4>
                        <p class="work-process__desc">
                            {{ __(@$howItWorksStepOne->data_values->subtitle_three) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row gy-4 justify-content-center work-process-row-two">
            <div class="col-lg-4 col-md-6">
                <div class="work-process two">
                    <span class="work-process__number">@lang('3.1')</span>
                    <span class="work-process__icon"><img src="{{ frontendImage('how_it_works_step_two', @$howItWorksStepTwo->data_values->image_one, '48x48') }}" alt="image"></span>
                    <h4 class="work-process__title">{{ __(@$howItWorksStepTwo->data_values->title_one) }}</h4>
                    <p class="work-process__desc">
                        {{ __(@$howItWorksStepTwo->data_values->subtitle_one) }}
                    </p>
                    <span class="how-it-work-shape-02"><img src="{{ $activeTemplateTrue.'images/shape-02.png' }}" alt="image"></span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="work-process two">
                    <span class="work-process__number">@lang('3.2')</span>
                    <span class="work-process__icon"><img src="{{ frontendImage('how_it_works_step_two', @$howItWorksStepTwo->data_values->image_two, '48x48') }}" alt="image"></span>
                    <h4 class="work-process__title">{{ __(@$howItWorksStepTwo->data_values->title_two) }}</h4>
                    <p class="work-process__desc">
                        {{ __(@$howItWorksStepTwo->data_values->subtitle_two) }}
                    </p>
                    <span class="how-it-work-shape-03"><img src="{{ $activeTemplateTrue.'images/shape-03.png' }}" alt="image"></span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="work-process two">
                    <span class="work-process__number">@lang('3.3')</span>
                    <span class="work-process__icon"><img src="{{ frontendImage('how_it_works_step_two', @$howItWorksStepTwo->data_values->image_three, '48x48') }}" alt="image"></span>
                    <h4 class="work-process__title">{{ __(@$howItWorksStepTwo->data_values->title_three) }}</h4>
                    <p class="work-process__desc">
                        {{ __(@$howItWorksStepTwo->data_values->subtitle_three) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="row gy-4 justify-content-center">
            <div class="col-xl-6 col-lg-4 col-md-6">
                <div class="work-process two">
                    <span class="work-process__number">@lang('3.1.1')</span>
                    <span class="work-process__icon"><img src="{{ frontendImage('how_it_works_step_three', @$howItWorksStepThree->data_values->image_one, '48x48') }}" alt="image"></span>
                    <h4 class="work-process__title">{{ __(@$howItWorksStepThree->data_values->title_one) }}</h4>
                    <p class="work-process__desc">
                        {{ __(@$howItWorksStepThree->data_values->subtitle_one) }}
                    </p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="work-process two">
                    <span class="work-process__number">@lang('3.2.1')</span>
                    <span class="work-process__icon"><img src="{{ frontendImage('how_it_works_step_three', @$howItWorksStepThree->data_values->image_two, '48x48') }}" alt="image"></span>
                    <h4 class="work-process__title">{{ __(@$howItWorksStepThree->data_values->title_two) }}</h4>
                    <p class="work-process__desc">
                        {{ __(@$howItWorksStepThree->data_values->subtitle_two) }}
                    </p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="work-process two">
                    <span class="work-process__number">@lang('3.2.2')</span>
                    <span class="work-process__icon"><img src="{{ frontendImage('how_it_works_step_three', @$howItWorksStepThree->data_values->image_three, '48x48') }}" alt="image"></span>
                        <h4 class="work-process__title">{{ __(@$howItWorksStepThree->data_values->title_three) }}</h4>
                    <p class="work-process__desc">
                        {{ __(@$howItWorksStepThree->data_values->subtitle_three) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
