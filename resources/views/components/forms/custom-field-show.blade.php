@if (isset($fields))
    @foreach ($fields as $field)
        @php
            $key = 'field_' . $field->id;
            $customFieldsData = $model->custom_fields_data;
            $value = is_array($customFieldsData) && isset($customFieldsData[$key]) ? $customFieldsData[$key] : null;
        @endphp

        @if (in_array($field->type, ['text', 'password', 'number']))
            <x-cards.data-row
                :label="__($field->label)"
                :value="$value ?? '--'">
            </x-cards.data-row>

        @elseif($field->type == 'textarea')
            <x-cards.data-row
                :label="__($field->label)"
                html="true"
                :value="$value ?? '--'">
            </x-cards.data-row>

        @elseif($field->type == 'radio')
            <x-cards.data-row :label="__($field->label)"
                              :value="$value ?? '--'">
            </x-cards.data-row>

        @elseif($field->type == 'checkbox')
            <x-cards.data-row :label="__($field->label)"
                              :value="$value ?? '--'">
            </x-cards.data-row>

        @elseif($field->type == 'select')
            <x-cards.data-row :label="__($field->label)"
                              :value="($value !== null && $value !== '' && isset($field->values[$value]) ? $field->values[$value] : '--')">
            </x-cards.data-row>

        @elseif($field->type == 'date')
            <x-cards.data-row :label="__($field->label)"
                              :value="($value !== null && $value !== '' ? \Carbon\Carbon::parse($value)->translatedFormat(company()->date_format) : '--')">
            </x-cards.data-row>
        @elseif($field->type == 'file')
            @php
                $fileValue = '--';
                if($value !== null && $value !== ''){
                    $fileValue = '<a href="'.asset_url_local_s3('custom_fields/' .$value).'" class="text-dark-grey" download>'.__('app.storageSetting.downloadFile').' <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" data-original-title="' . __('app.downloadableFile') .'" data-html="true" data-trigger="hover"></i></a>';
                }
            @endphp

            <x-cards.data-row
            :label="__($field->label)"
            :value="$fileValue">
            </x-cards.data-row>
        @endif
    @endforeach
@endif
