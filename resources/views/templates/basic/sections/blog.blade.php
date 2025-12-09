@php
    $blog = getContent('blog.content', true);
    $blogElements = getContent('blog.element', limit: 3, orderById: true);
@endphp

<section class="blog-section section-bg py-60">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xl-9 col-lg-10">
                <div class="section-heading">
                    <h2 class="section-heading__title">{{ __(@$blog->data_values->heading) }}</h2>
                    <p class="section-heading__desc">
                        {{ __(@$blog->data_values->subheading) }}
                    </p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-2 d-lg-block d-none text-end">
                <a href="{{ route('blogs') }}" class="btn btn-outline--base">@lang('View All')</a>
            </div>
        </div>
        <div class="row gy-4 justify-content-center">
            @foreach ($blogElements as $blogElement)
                <div class="col-lg-4 col-sm-6">
                    <div class="blog-item">
                        <div class="blog-item__thumb">
                            <a href="{{ route('blog.details', @$blogElement->slug) }}" class="blog-item__thumb-link">
                                <img src="{{ frontendImage('blog', @$blogElement->data_values->image, '1020x450') }}" class="fit-image" alt="image">
                            </a>
                        </div>
                        <div class="blog-item__content">
                            <h4 class="blog-item__title">
                                <a href="{{ route('blog.details', @$blogElement->slug) }}" class="blog-item__title-link border-effect">{{ __(@$blogElement->data_values->title) }}</a>
                            </h4>
                            <p class="blog-item__desc">
                                @php
                                    echo strLimit($blogElement->data_values->description, 150);
                                @endphp
                            </p>
                            <a href="{{ route('blog.details', @$blogElement->slug) }}" class="blog-item__link">
                                @lang('Read More')
                                <span class="blog-item__link-icon"><i class="las la-angle-right"></i></span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
