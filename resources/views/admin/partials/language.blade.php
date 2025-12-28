@php
    use App\Constants\Status;
    $languages = App\Models\Language::all();
    $currentLang = session('lang') ? $languages->where('code', session('lang'))->first() : $languages->where('is_default', Status::ENABLE)->first();
    if (!$currentLang) {
        $currentLang = $languages->first();
    }
@endphp

<li class="dropdown">
    <button type="button" class="primary--layer" data-bs-toggle="dropdown" data-display="static"
        aria-haspopup="true" aria-expanded="false" title="@lang('Language')">
        <i class="las la-comments"></i>
    </button>
    <div class="dropdown-menu dropdown-menu--sm p-0 border-0 box--shadow1 dropdown-menu-right">
        <div class="dropdown-menu__header px-3 py-2">
            <span class="caption">@lang('Select Language')</span>
        </div>
        <div class="dropdown-menu__body p-0">
            @foreach($languages as $language)
                <a href="{{ route('lang', $language->code) }}" 
                   class="dropdown-menu__item d-flex align-items-center px-3 py-2 language-item {{ session('lang', config('app.locale')) == $language->code ? 'active' : '' }}">
                    <span class="me-2">
                        <img src="{{ getImage(getFilePath('language') . '/' . $language->image) }}" 
                             alt="{{ $language->name }}" 
                             style="width: 20px; height: 15px; object-fit: cover;">
                    </span>
                    <span class="dropdown-menu__caption">{{ __($language->name) }}</span>
                    @if(session('lang', config('app.locale')) == $language->code)
                        <span class="ms-auto"><i class="las la-check text--success"></i></span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</li>
