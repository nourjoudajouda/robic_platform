@php
    $languages = App\Models\Language::all();
    $currentLang = session('lang') ? $languages->where('code', session('lang'))->first() : $languages->where('is_default', Status::YES)->first();
@endphp

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
