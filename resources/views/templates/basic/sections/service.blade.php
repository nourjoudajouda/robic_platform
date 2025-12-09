@php
    $service = getContent('service.content',true);
    $serviceElements = getContent('service.element', orderById : true);
@endphp

<div class="service-section section-bg py-60">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <h2 class="section-heading__title">{{ __($service->data_values->heading) }}</h2>
                    <p class="section-heading__desc">
                        {{ __($service->data_values->subheading) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="service-content-wrapper">
                    <ul class="custom--tab nav nav-tabs" id="myTab" role="tablist">
                        @foreach ($serviceElements as $serviceElement)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="nav-{{ $loop->iteration }}" data-bs-toggle="tab" data-bs-target="#nav-pane-{{ $loop->iteration }}" type="button" role="tab" aria-controls="nav-pane-{{ $loop->iteration }}" aria-selected="true"><span>{{ __($serviceElement->data_values->title) }}</span></button>
                        </li>
                        @endforeach
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        @foreach ($serviceElements as $serviceElement)

                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="nav-pane-{{ $loop->iteration }}" role="tabpanel" aria-labelledby="nav-{{ $loop->iteration }}" tabindex="0">
                            <div class="service-content">
                               @php
                                   echo $serviceElement->data_values->description;
                               @endphp
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="service-thumb">
                    <img class="service-thumb__image" src="{{ frontendImage('service', $service->data_values->image, '620x400') }}" alt="image">
                    <img class="service-thumb__shape" src="{{ getImage($activeTemplateTrue.'images/service-thumb-shape.png') }}" alt="image">
                </div>
            </div>
        </div>
    </div>
</div>
