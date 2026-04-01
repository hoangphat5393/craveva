<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

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
        $rules = [
            'slack_username' => 'nullable|unique',
            'name' => 'required',
            'email' => [
                'nullable',
                'email:rfc,strict',
                Rule::requiredIf(function () {
                    return in_array((string) $this->login, ['enable', 'yes'], true);
                }),
                'unique:users,email,' . $this->route('client') . ',id,company_id,' . company()->id,
            ],
            'website' => 'nullable|url',
            'country' => 'required_with:mobile',
            'password' => 'nullable|min:8',
            'client_code' => ['nullable', Rule::unique('client_details', 'client_code')->where('company_id', company()->id)->ignore($this->route('client'), 'user_id')],
            'mobile' => 'nullable|numeric',
            'default_warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'payment_terms' => 'nullable|string|max:255',
            'customer_grade' => 'nullable|string|max:255',
            'channel_type' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'business_closure_date' => 'nullable|date',
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'The client name field is required.',
            'email.required' => 'Email is required when client login is enabled.',
            'website.url' => 'The website format is invalid. Add https:// or http to url',
        ];
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }
}
