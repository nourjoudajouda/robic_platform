<header class="header" id="header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="d-flex align-items-center">
                        <a class="navbar-brand logo" href="{{ route('home') }}"><img src="{{ siteLogo() }}" alt="image"></a>
                        <button class="theme-toggle-btn ms-3 d-lg-none d-block" type="button" aria-label="Toggle theme" style="font-size: 1.3rem;">
                            <i class="las la-moon theme-icon" id="themeIconMobile"></i>
                        </button>
                    </div>
                    <button class="navbar-toggler header-button" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span id="hiddenNav"><i class="las la-bars"></i></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav nav-menu ms-auto align-items-lg-center">
                            @if (gs('multi_language'))
                                <li class="nav-item d-block d-lg-none">
                                    <div class="language-dropdown">
                                        @include($activeTemplate . 'partials.language')
                                    </div>
                                </li>
                            @endif
                            <li class="nav-item {{ menuActive('home') }}">
                                <a class="nav-link" href="{{ route('home') }}">@lang('Home')</a>
                            </li>
                            @php
                                $pages = App\Models\Page::where('tempname', $activeTemplate)->where('is_default', 0)->get();
                            @endphp
                            @foreach ($pages as $page)
                                <li class="nav-item {{ menuActive('pages', param: $page->slug) }}">
                                    <a class="nav-link" href="{{ route('pages', [$page->slug]) }}">{{ __($page->name) }}</a>
                                </li>
                            @endforeach

                            <li class="nav-item {{ menuActive('faq') }}">
                                <a class="nav-link" href="{{ route('faq') }}">@lang('Faq')</a>
                            </li>
                            <li class="nav-item {{ menuActive('blogs') }}">
                                <a class="nav-link" href="{{ route('blogs') }}">@lang('Blog')</a>
                            </li>
                            <li class="nav-item {{ menuActive('contact') }}">
                                <a class="nav-link" href="{{ route('contact') }}">@lang('Contact')</a>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link theme-toggle-btn" type="button" aria-label="Toggle theme">
                                    <i class="las la-moon theme-icon" id="themeIcon"></i>
                                </button>
                            </li>
                            <li class="nav-item not-menu">
                                @if (gs('multi_language'))
                                    <div class="language-dropdown d-none d-lg-block">
                                        @include($activeTemplate . 'partials.language')
                                    </div>
                                @endif
                                @if (auth()->check())
                                    <a class="btn btn--base" href="{{ route('user.home') }}">@lang('Dashboard')</a>
                                @else
                                    <a class="btn btn--base" href="{{ route('user.login') }}">@lang('Login')</a>
                                @endif
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</header>


@push('script')
    <script>
        (function($) {
            "use strict";
            $('.langChange').on('click', function() {
                let selectedThumb = $(this).find('.thumb').html();
                let selectedText = $(this).find('.text').text();
                let lang = $(this).data('value');
                let route = "{{ route('lang') }}";

                $('.language-dropdown__selected .thumb').html(selectedThumb);
                $('.language-dropdown__selected .text').text(selectedText);

                window.location.href = route + '/' + lang;
            });

            // Theme Toggle
            function getTheme() {
                return $('body').attr('data-theme') || 'dark';
            }

            function setTheme(theme) {
                $('body').attr('data-theme', theme);
                document.cookie = `theme=${theme};path=/;max-age=31536000`; // سنة واحدة
                updateThemeIcon(theme);
            }

            function updateThemeIcon(theme) {
                const icons = $('#themeIcon, #themeIconMobile');
                if (theme === 'light') {
                    icons.removeClass('la-moon').addClass('la-sun');
                } else {
                    icons.removeClass('la-sun').addClass('la-moon');
                }
            }

            // تطبيق الوضع المحفوظ عند تحميل الصفحة
            $(document).ready(function() {
                const savedTheme = getTheme();
                updateThemeIcon(savedTheme);
            });

            // التبديل بين الوضعين
            $('.theme-toggle-btn').on('click', function() {
                const currentTheme = getTheme();
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                setTheme(newTheme);
            });
        })(jQuery);
    </script>
@endpush
