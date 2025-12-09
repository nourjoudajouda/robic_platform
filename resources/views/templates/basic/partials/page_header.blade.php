<div class="dashboard-body__top justify-content-between">
    <h3 class="dashboard-body__title mb-0">
        <span class="dashboard-body__title-icon">
            @yield('pageTitleIcon')
        </span>
        <span class="dashboard-body__title-text">
            {{ __($pageTitle) }}
        </span>
    </h3>
    <div class="dashboard-buttons justify-content-md-end justify-content-start">
        @stack('pageHeaderButton')
    </div>
</div>
