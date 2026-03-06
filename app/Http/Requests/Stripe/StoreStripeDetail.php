<?php

namespace App\Http\Requests\Stripe;

use App\Http\Requests\CoreRequest;

/**
 * Class StoreStickyNote
 */
class StoreStripeDetail extends CoreRequest
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
            'clientName' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'line1' => 'required',
        ];
    }
}
