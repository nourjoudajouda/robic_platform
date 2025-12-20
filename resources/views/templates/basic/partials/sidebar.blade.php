<div class="sidebar-menu flex-between">
    <div class="sidebar-menu__inner">
        <span class="sidebar-menu__close d-xl-none d-inline-flex"><i class="fas fa-times"></i></span>
        <div class="sidebar-logo">
            <a href="{{ route('home') }}" class="sidebar-logo__link"><img src="{{ siteLogo() }}" alt="image"></a>
        </div>
        <ul class="sidebar-menu-list">
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.home') }}" class="sidebar-menu-list__link {{ menuActive('user.home') }}">
                    <span class="icon"><i class="fa-solid fa-table-cells-large"></i></span>
                    <span class="text">@lang('Dashboard')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.price.tracker') }}" class="sidebar-menu-list__link {{ menuActive('user.price.tracker') }}">
                    <span class="icon"><i class="fa-solid fa-chart-simple"></i></span>
                    <span class="text">@lang('Price Tracker')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.portfolio') }}" class="sidebar-menu-list__link {{ menuActive(['user.portfolio', 'user.asset.logs']) }}">
                    <span class="icon"><i class="fa-solid fa-chart-pie"></i></span>
                    <span class="text">@lang('My Portfolio')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.deposit.index') }}" class="sidebar-menu-list__link {{ menuActive('user.deposit*') }}">
                    <span class="icon"><i class="fa-sharp fa-solid fa-sack-dollar"></i></span>
                    <span class="text">@lang('Deposit')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.buy.form') }}" class="sidebar-menu-list__link {{ menuActive('user.buy*') }}">
                    <span class="icon"><i class="fa-solid fa-circle-dollar-to-slot"></i></span>
                    <span class="text">@lang('Buy Green Coffee')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.sell.form') }}" class="sidebar-menu-list__link {{ menuActive('user.sell*') }}">
                    <span class="icon"><i class="fa-solid fa-money-bill-trend-up"></i></span>
                    <span class="text">@lang('Sell Green Coffee')</span>
                </a>
            </li>
            @if (gs('redeem_option'))
                <li class="sidebar-menu-list__item">
                    <a href="{{ route('user.redeem.form') }}" class="sidebar-menu-list__link {{ menuActive('user.redeem*') }}">
                        <span class="icon"><i class="fa-solid fa-truck"></i></span>
                        <span class="text">@lang('Redeem')</span>
                    </a>
                </li>
            @endif
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.gift.form') }}" class="sidebar-menu-list__link {{ menuActive('user.gift*') }}">
                    <span class="icon"><i class="fa-solid fa-gift"></i></span>
                    <span class="text">@lang('Gift Green Coffee')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.withdraw') }}" class="sidebar-menu-list__link {{ menuActive('user.withdraw*') }}">
                    <span class="icon"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                    <span class="text">@lang('Withdraw')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.transactions') }}" class="sidebar-menu-list__link {{ menuActive('user.transactions') }}">
                    <span class="icon"><i class="fa-solid fa-clipboard-list"></i></span>
                    <span class="text">@lang('Transaction')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('ticket.index') }}" class="sidebar-menu-list__link {{ menuActive('ticket*') }}">
                    <span class="icon"><i class="fa-solid fa-envelope"></i></span>
                    <span class="text">@lang('Support')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.logout') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                    <span class="text">@lang('Log Out')</span>
                </a>
            </li>
        </ul>
    </div>
</div>
