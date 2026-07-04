<div class="{{ $wrapperClass ?? 'col-md-6 col-sm-12 p-0 c-inv-note-terms' }}">
    <div class="form-group my-3">
        <x-forms.label fieldId="" :fieldLabel="$label">
        </x-forms.label>
        <p>
            {!! nl2br($termsText ?? '') !!}
        </p>
    </div>
</div>
