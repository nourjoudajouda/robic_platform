@php
    $subscribe = getContent('subscribe.content', true);
@endphp

<div class="cta-section section-bg">
    <div class="container">
        <div class="cta-wrapper bg-img" data-background-image="{{ frontendImage('subscribe', @$subscribe->data_values->background_image, '1395x485') }}">
            <div class="row justify-content-center">
                <div class="col-xxl-8 col-lg-10">
                    <div class="cta-content text-center">
                        <h1 class="cta-content__title">@php echo highLightedString(@$subscribe->data_values->heading) @endphp</h1>
                        <p class="cta-content__desc">{{ __(@$subscribe->data_values->subheading) }}</p>
                        <div class="cta-content__form">
                            <form class="subscribe-form" method="POST">
                                @csrf
                                <div class="form-group">
                                    <input type="text" class="form--control" name="email" placeholder="@lang('Enter your email...')">
                                    <button type="submit" class="btn btn--base pill">@lang('Subscribe')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@push('script')
    <script>
        (function($) {
            "use strict";
            $('.subscribe-form').on('submit', function(e) {
                e.preventDefault();
                var data = $('.subscribe-form').serialize();
                $.ajax({
                    type: "POST",
                    url: "{{ route('subscribe') }}",
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            notify('success', response.message);
                            $('#email').val('');
                        } else {
                            notify('error', response.message);
                        }
                    }
                });
            });
        })(jQuery);
    </script>
@endpush

