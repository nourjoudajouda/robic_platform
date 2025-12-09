@php
    $paymentMethods = getContent('payment_methods.element', orderById: true);
@endphp

<div class="payment-methods-section section-bg py-60">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="payment-methods-slider">
                    @foreach ($paymentMethods as $paymentMethod)
                        <div class="payment-methods-item">
                            <div class="payment-methods-item__thumb">
                                <img src="{{ frontendImage('payment_methods', @$paymentMethod->data_values->image, '140x55') }}" alt="image">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
