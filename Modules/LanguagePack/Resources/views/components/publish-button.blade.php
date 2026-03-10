@php
    $republishText = __('languagepack::app.republish');
    $publishText = __('languagepack::app.publish');
    $label = $isLanguagePublished
        ? (str_contains($republishText, '::') ? 'Republish' : $republishText)
        : (str_contains($publishText, '::') ? 'Publish' : $publishText);
@endphp
<button type="button"
    class="btn @if ($isLanguagePublished) btn-outline-danger @else btn-outline-primary @endif rounded f-14 p-2 languagePackPublish text-nowrap"
    title="{{ $label }}"
    data-language-code="{{ $languageCode }}" data-republish="{{ $isLanguagePublished ? 'true' : 'false' }}"
    data-toggle="popover" data-placement="top" data-content="@lang($isLanguagePublished ? 'languagepack::app.republishButtonPopover' : 'languagepack::app.publishButtonPopover', ['language' => $language->language_name])" data-html="true" data-trigger="hover">
    <i class="fa @if ($isLanguagePublished) fa-redo @else fa-language @endif mr-2"></i>
    {{ $label }}
</button>
