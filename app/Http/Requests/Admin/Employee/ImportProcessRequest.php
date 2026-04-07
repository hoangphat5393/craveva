<?php

namespace App\Http\Requests\Admin\Employee;

use Illuminate\Foundation\Http\FormRequest;

class ImportProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required',
            'has_heading' => 'nullable|boolean',
            'has_skip_footer' => 'nullable|boolean',
            'columns' => ['required', 'array', 'min:1'],
            'default_unit_id' => 'nullable|integer|exists:unit_types,id',
            'original_filename' => 'nullable|string|max:500',
            'chunk_size' => 'nullable|integer|min:20|max:500',
        ];
    }

    public function attributes()
    {
        return [
            'columns.*' => 'column',
        ];
    }
}
