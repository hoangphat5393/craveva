<?php

namespace App\Http\Requests\Payments;

use App\Http\Requests\CoreRequest;

class UpdatePayments extends CoreRequest
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
        $rules = [
            'amount' => 'required|numeric|min:1',
            'paid_on' => 'required',
            'offline_methods' => 'required_if:gateway,==,Offline',
        ];

        if ($this->transaction_id) {
            // It need to be unique for all the company
            $rules['transaction_id'] = 'unique:payments,transaction_id,' . $this->route('payment') . ',id,company_id,' . company()->id;
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'invoice_id.required' => 'Select the invoice you want to add payment for.',
            'offline_methods.required_if' => __('messages.selectOfflineMethod'),
        ];
    }

    public function attributes()
    {
        return [
            'offline_methods' => __('modules.payments.offlinePaymentMethod'),
            'paid_on' => __('modules.payments.paidOn'),
            'amount' => __('app.amount'),
            'gateway' => __('modules.payments.paymentGateway'),
            'transaction_id' => __('modules.payments.transactionId'),
        ];
    }
}
