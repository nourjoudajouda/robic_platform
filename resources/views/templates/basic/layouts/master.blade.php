@extends($activeTemplate . 'layouts.app')

@section('panel')
    <div class="dashboard position-relative bg-img" data-background-image="{{ asset($activeTemplateTrue . 'images/dashboard-bg.png') }}">
        <div class="dashboard__inner flex-wrap">
            <!-- ====================== Sidebar Menu Start ========================= -->
            @include($activeTemplate . 'partials.sidebar')
            <!-- ====================== Sidebar Menu End ========================= -->
            <div class="dashboard__right">
                @include($activeTemplate . 'partials.topbar')
                <div class="dashboard-body">

                    @if (!request()->routeIs('user.home'))
                        @include($activeTemplate . 'partials.page_header')
                    @endif

                    @yield('content')
                  
                </div>
            </div>
        </div>
    </div>
@endsection
