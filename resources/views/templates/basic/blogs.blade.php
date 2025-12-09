@extends($activeTemplate . 'layouts.frontend')

@section('content')

    <section class="section-bg py-60">
        <div class="container">
            <div class="row gy-4 justify-content-center">
                @forelse ($blogs as $blog)
                    <div class="col-lg-4 col-sm-6">
                        <div class="blog-item">
                            <div class="blog-item__thumb">
                                <a href="{{ route('blog.details', @$blog->slug) }}" class="blog-item__thumb-link">
                                    <img src="{{ frontendImage('blog', @$blog->data_values->image, '1020x450') }}" class="fit-image" alt="image">
                                </a>
                            </div>
                            <div class="blog-item__content">
                                <h4 class="blog-item__title">
                                    <a href="{{ route('blog.details', @$blog->slug) }}" class="blog-item__title-link border-effect">{{ __(@$blog->data_values->title) }}</a>
                                </h4>
                                <p class="blog-item__desc">
                                    @php
                                        echo strLimit($blog->data_values->description, 150);
                                    @endphp
                                </p>
                                <a href="{{ route('blog.details', @$blog->slug) }}" class="blog-item__link">
                                    @lang('Read More')
                                    <span class="blog-item__link-icon"><i class="las la-angle-right"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-lg-12">

                        <x-empty-card />

                    </div>
                @endforelse
            </div>
            {{ $blogs->links() }}
        </div>
    </section>


    @if (@$sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif
@endsection
