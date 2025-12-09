@php
    $breadcrumb = getContent('breadcrumb.content', true);
@endphp
<section class="breadcrumb mb-0 py-60 bg-img" data-background-image="{{ frontendImage('breadcrumb', @$breadcrumb->data_values->breadcrumb_image, '1920x300') }}">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="breadcrumb__wrapper">
                    <h3 class="breadcrumb__title">{{ __($pageTitle) }}</h3>
                </div>
            </div>
        </div>
    </div>
</section>