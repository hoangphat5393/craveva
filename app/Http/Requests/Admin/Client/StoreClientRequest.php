<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;

class StoreClientRequest extends CoreRequest
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
        \Illuminate\Support\Facades\Validator::extend('check_superadmin', function ($attribute, $value, $parameters, $validator) {
            return ! \App\Models\User::withoutGlobalScopes([\App\Scopes\ActiveScope::class, \App\Scopes\CompanyScope::class])
                ->where('email', $value)
                ->where('is_superadmin', 1)
                ->exists();
        });

        $rules = [
            'name' => 'required',
            'email' => [
                'nullable',
                'email:rfc,strict',
                Rule::requiredIf(function () {
                    return in_array((string) $this->login, ['enable', 'yes'], true);
                }),
                'unique:users,email,null,id,company_id,' . company()->id,
                'check_superadmin',
            ],
            'slack_username' => 'nullable',
            'website' => 'nullable|url',
            'country' => 'required_with:mobile',
            'client_code' => ['nullable', Rule::unique('client_details', 'client_code')->where('company_id', company()->id)],
            'mobile' => 'nullable|numeric',
            'default_warehouse_id' => 'nullable|integer|exists:warehouses,id',
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function messages()
    {
        return [
            'email.check_superadmin' => __('superadmin.emailAlreadyExist'),
            'website.url' => 'The website format is invalid. Add https:// or http to url',
            'name.required' => 'The client name field is required.',
            'email.required' => 'Email is required when client login is enabled.',
        ];
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }
}
