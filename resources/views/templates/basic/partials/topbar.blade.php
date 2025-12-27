<div class="dashboard-header">
    <div class="dashboard-header__left">
        <span class="sm-sidebar-btn d-inline-flex d-xl-none"><i class="las la-bars"></i></span>
        <span class="sm-search-btn d-inline-flex d-sm-none"><i class="las la-search"></i></span>
        <form action="#" method="#" class="position-relative">
            <div class="form-group mb-0 position-relative">
                <input type="text" class="form--control navbar-search-field" id="searchInput" placeholder="Search here...">
                <button type="submit"><i class="las la-search"></i></button>
            </div>
            <ul class="search-list"></ul>
        </form>
    </div>
    <div class="dashboard-header__right flex-align justify-content-end">
        @if (gs('multi_language'))
            <div class="language-dropdown me-3">
                @include($activeTemplate . 'partials.language')
            </div>
        @endif
        <div class="user-info">
            <button class="user-info__button flex-align">
                <span class="user-info__button-thumb">
                    <img src="{{ getImage(getFilePath('userProfile') . '/' . auth()->user()->image) }}" class="fit-image" alt="image">
                </span>
                <span class="user-info__button-content">
                    <span class="user-info__button-content-name d-block">{{ auth()->user()->fullname }}</span>
                    <span class="user-info__button-content-username d-block">{{ auth()->user()->username }}</span>
                </span>
                <span class="user-info__button-icon"><i class="las la-angle-down"></i></span>
            </button>
            <ul class="user-info-dropdown">
                <li class="user-info-dropdown__item">
                    <a class="user-info-dropdown__link {{ menuActive('user.profile.setting') }}" href="{{ route('user.profile.setting') }}">
                        <span class="icon"><i class="far fa-user-circle"></i></span>
                        <span class="text">@lang('My Profile')</span>
                    </a>
                </li>
                <li class="user-info-dropdown__item">
                    <a class="user-info-dropdown__link {{ menuActive('user.twofactor') }}" href="{{ route('user.twofactor') }}">
                        <span class="icon"><i class="fas
                             fa-cog"></i></span>
                        <span class="text">@lang('2fa Setting')</span>
                    </a>
                </li>
                <li class="user-info-dropdown__item">
                    <a class="user-info-dropdown__link {{ menuActive('user.change.password') }}" href="{{ route('user.change.password') }}">
                        <span class="icon"><i class="fas fa-key"></i></span>
                        <span class="text">@lang('Change Password')</span>
                    </a>
                </li>
                <li class="user-info-dropdown__item">
                    <a class="user-info-dropdown__link" href="{{ route('user.logout') }}">
                        <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                        <span class="text">@lang('Logout')</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>


@push('style')
    <style>
        .search-list {
            position: absolute;
            top: 100%;
            background-color: hsl(var(--card-bg));
            width: 100%;
            z-index: 99;
            max-height: 310px;
            overflow: auto;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .search-list::-webkit-scrollbar {
            width: 2px
        }

        .search-list::-webkit-scrollbar-track {
            box-shadow: inset 0 0 2px rgba(0, 0, 0, 0.3)
        }

        .search-list::-webkit-scrollbar-thumb {
            background-color: darkgrey;
            outline: 1px solid slategrey
        }

        .search-list li {
            border-bottom: 1px solid hsl(var(--border-color));
        }
        .search-list li:last-child {
            border-bottom: 0;
        }
        .search-list li a {
            color: hsl(var(--heading-color));
            padding: 10px;
            padding-left: 25px;
            font-size: 13px;
        }

        .search-list li a:hover {
            color: hsl(var(--base-two));
            background: hsl(var(--dark));
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";


            $('.navbar-search-field').on('input', function() {
                var search = $(this).val().toLowerCase();
                var search_result_pane = $('.search-list');
                $(search_result_pane).html('');
                if (search.length == 0) {
                    $('.search-list').addClass('d-none');
                    return;
                }
                $('.search-list').removeClass('d-none');

                // search
                var match = $('.sidebar-menu-list__item .text, .user-info-dropdown__item .text').filter(function(idx, elem) {
                    return $(elem).text().trim().toLowerCase().indexOf(search) >= 0 ? elem : null;
                }).sort();


                // search not found
                if (match.length == 0) {
                    $(search_result_pane).append('<li class="text-muted pl-5">No search result found.</li>');
                    return;
                }

                // search found
                match.each(function(idx, elem) {
                    var item_url = $(elem).closest('a').attr('href');
                    var item_text = $(elem).text().trim();
                    $(search_result_pane).append(`
                            <li>
                                <a href="${item_url}" class="fw-bold text-color--3 d-block">${item_text}</a>
                            </li>
                        `);
                });

            });

            // color

            var len = 0;
            var clickLink = 0;
            var search = null;
            var process = false;
            $('#searchInput').on('keydown', function(e) {
                var length = $('.search-list li').length;
                                
                if (search != $(this).val() && process) {
                    len = 0;
                    clickLink = 0;
                    $(`.search-list li:eq(${len}) a`).focus();
                    $(`#searchInput`).focus();
                }
                //Down
                if (e.keyCode == 40 && length) {
                    process = true;
                    var contra = false;
                    if (len < clickLink && clickLink < length) {
                        len += 2;
                    }
                    $(`.search-list li[class="bg--dark"]`).removeClass('bg--dark');
                    $(`.search-list li a[class="text--white"]`).removeClass('text--white');
                    $(`.search-list li:eq(${len}) a`).focus().addClass('text--white');
                    $(`.search-list li:eq(${len})`).addClass('bg--dark');
                    $(`#searchInput`).focus();
                    clickLink = len;
                    if (!$(`.search-list li:eq(${clickLink}) a`).length) {
                        $(`.search-list li:eq(${len})`).addClass('text--white');
                    }
                    len += 1;
                    if (length == Math.abs(clickLink)) {
                        len = 0;
                    }
                }
                //Up
                else if (e.keyCode == 38 && length) {
                    process = true;
                    if (len > clickLink && len != 0) {
                        len -= 2;
                    }
                    $(`.search-list li[class="bg--dark"]`).removeClass('bg--dark');
                    $(`.search-list li a[class="text--white"]`).removeClass('text--white');
                    $(`.search-list li:eq(${len}) a`).focus().addClass('text--white');
                    $(`.search-list li:eq(${len})`).addClass('bg--dark');
                    $(`#searchInput`).focus();
                    clickLink = len;
                    if (!$(`.search-list li:eq(${clickLink}) a`).length) {
                        $(`.search-list li:eq(${len})`).addClass('text--white');
                    }
                    len -= 1;
                    if (length == Math.abs(clickLink)) {
                        len = 0;
                    }
                }
                //Enter
                else if (e.keyCode == 13) {
                    e.preventDefault();
                    if ($(`.search-list li:eq(${clickLink}) a`).length && process) {
                        $(`.search-list li:eq(${clickLink}) a`)[0].click();
                    }
                }
                //Retry
                else if (e.keyCode == 8) {
                    len = 0;
                    clickLink = 0;
                    $(`.search-list li:eq(${len}) a`).focus();
                    $(`#searchInput`).focus();
                }
                search = $(this).val();
            });

            // Language Change Handler
            $('.langChange').on('click', function() {
                let selectedThumb = $(this).find('.thumb').html();
                let selectedText = $(this).find('.text').text();
                let lang = $(this).data('value');
                let route = "{{ route('lang') }}";

                $('.language-dropdown__selected .thumb').html(selectedThumb);
                $('.language-dropdown__selected .text').text(selectedText);

                window.location.href = route + '/' + lang;
            });

            // Close language dropdown when clicking outside
            $(document).on('click', function(event) {
                var target = $(event.target);
                if (!target.closest('.language-dropdown').length) {
                    $('.language-dropdown').removeClass('open');
                }
            });

        })(jQuery);
    </script>
@endpush
