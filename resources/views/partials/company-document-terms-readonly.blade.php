<div class="{{ $wrapperClass ?? 'col-md-6 col-sm-12 p-0 c-inv-note-terms' }}">
    <x-forms.label fieldId="" :fieldLabel="$label">
    </x-forms.label>
    <p>
        {!! nl2br($termsText ?? '') !!}
    </p>
</div>
