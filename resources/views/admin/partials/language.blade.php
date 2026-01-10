@php
    use App\Constants\Status;
    // Get all languages, ordered by default first, then by name
    $languages = App\Models\Language::orderBy('is_default', 'desc')->orderBy('name', 'asc')->get();
    
    // Get current language from session or default
    $currentLangCode = session('lang');
    if (!$currentLangCode) {
        $defaultLang = $languages->where('is_default', Status::YES)->first();
        $currentLangCode = $defaultLang ? $defaultLang->code : 'en';
    }
    $currentLang = $languages->where('code', $currentLangCode)->first();
    if (!$currentLang) {
        $currentLang = $languages->where('is_default', Status::YES)->first() ?? $languages->first();
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
                   class="dropdown-menu__item d-flex align-items-center px-3 py-2 language-item {{ $currentLangCode == $language->code ? 'active' : '' }}">
                    <span class="me-2">
                        <img src="{{ getImage(getFilePath('language') . '/' . $language->image) }}" 
                             alt="{{ $language->name }}" 
                             style="width: 20px; height: 15px; object-fit: cover;">
                    </span>
                    <span class="dropdown-menu__caption">{{ __($language->name) }}</span>
                    @if($language->is_default == Status::YES)
                        <span class="ms-auto me-2"><small class="text-muted">(@lang('Default'))</small></span>
                    @endif
                    @if($currentLangCode == $language->code)
                        <span class="ms-auto"><i class="las la-check text--success"></i></span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</li>
