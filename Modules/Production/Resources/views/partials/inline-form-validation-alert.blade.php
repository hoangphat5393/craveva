@if ($errors->hasAny($fields ?? []))
    <div class="alert alert-danger f-13 mb-3" role="alert">
        <strong class="d-block mb-1">@lang('production::app.formValidationFailedTitle')</strong>
        <ul class="mb-0 pl-3">
            @foreach ($fields as $field)
                @foreach ($errors->get($field) as $message)
                    <li>{{ $message }}</li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endif
