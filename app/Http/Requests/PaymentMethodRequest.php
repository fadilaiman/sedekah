<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $paymentMethodId = $this->route('paymentMethod')?->id;

        // Code is only required on create (POST), not on update (PUT)
        $codeRules = $this->isMethod('POST')
            ? ['required', 'string', 'alpha_dash']
            : ['string', 'alpha_dash'];

        return [
            'code' => [
                ...$codeRules,
                Rule::unique('payment_methods', 'code')
                    ->ignore($paymentMethodId),
            ],
            'name' => ['required', 'string', 'max:100'],
            'active' => ['boolean'],
        ];
    }

    /**
     * Strip 'code' from PUT requests (code is immutable).
     */
    protected function prepareForValidation(): void
    {
        if ($this->isMethod('PUT')) {
            $this->request->remove('code');
        }
    }
}
