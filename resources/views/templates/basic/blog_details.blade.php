@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="blog-detials section-bg py-60">
        <div class="container">
            <div class="row gy-5 justify-content-center">
                <div class="col-xl-9 col-lg-8">
                    <div class="blog-details">
                        <div class="blog-details__thumb">
                            <img src="{{ frontendImage('blog', $blog->data_values->image, '1020x450') }}" class="fit-image" alt="image">
                        </div>
                        <div class="blog-details__content">
                            <span class="blog-details__date mb-3"><span class="blog-item__date-icon"><i class="las la-clock"></i></span> {{ showDateTime($blog->created_at, 'd M, Y') }}</span>
                            <h3 class="blog-details__title">{{ __($blog->data_values->title) }}</h3>
                            @php
                                echo $blog->data_values->description;
                            @endphp
                            <div class="blog-details__share mt-4 d-flex align-items-center flex-wrap">
                                <h6 class="social-share__title mb-0 me-3 d-inline-block">@lang('Share This')</h6>
                                <ul class="social-list">
                                    <li class="social-list__item"><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="social-list__link flex-center"><i class="fab fa-facebook-f"></i></a>
                                    </li>
                                    <li class="social-list__item"><a href="https://twitter.com/intent/tweet?text={{ __(@$blog->data_values->title) }}&amp;url={{ urlencode(url()->current()) }}" target="_blank" class="social-list__link flex-center"><i class="fab fa-twitter"></i></a>
                                    </li>
                                    <li class="social-list__item"><a href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{ urlencode(url()->current()) }}&amp;title={{ __(@$blog->data_values->title) }}&amp;" target="_blank" class="social-list__link flex-center"><i class="fab fa-linkedin-in"></i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4">
                    <!-- ============================= Blog Details Sidebar Start ======================== -->
                    <div class="blog-sidebar-wrapper">
                        <div class="blog-sidebar">
                            <h5 class="blog-sidebar__title">@lang('Latest Blog')</h5>
                            @foreach ($blogs as $blog)
                                <div class="latest-blog">
                                    <a href="{{ route('blog.details', $blog->slug) }}" class="latest-blog__thumb"><img src="{{ frontendImage('blog', $blog->data_values->image, '1020x450') }}" class="fit-image" alt="image"></a>
                                    <div class="latest-blog__content">
                                        <h6 class="latest-blog__title"><a href="{{ route('blog.details', $blog->slug) }}">{{ __($blog->data_values->title) }}</a></h6>
                                        <span class="latest-blog__date">{{ showDateTime($blog->created_at, 'd M, Y') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- ============================= Blog Details Sidebar End ======================== -->
                </div>
            </div>
        </div>
    </section>
@endsection

@push('fbComment')
    @php echo loadExtension('fb-comment') @endphp
@endpush
