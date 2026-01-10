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

@if($currentLang && $languages->count() > 0)
<div class="language-dropdown__selected">
    <span class="thumb"> <img class="flag" src="{{ getImage(getFilePath('language') . '/' . $currentLang->image) }}"></span>
    <span class="text">{{ __($currentLang->name) }}</span>
</div>
<ul class="language-dropdown__list">
    @foreach ($languages->where('code', '!=', $currentLang->code) as $language)
        <li class="language-dropdown__list-item langChange" data-value="{{ $language->code }}">
            <span class="thumb"> <img class="flag" src="{{ getImage(getFilePath('language') . '/' . $language->image) }}"></span>
            <span class="text">{{ __($language->name) }}</span>
        </li>
    @endforeach
</ul>
@endif
