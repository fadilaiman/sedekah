<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        // Value is only required on create (POST), not on update (PUT)
        $valueRules = $this->isMethod('POST')
            ? ['required', 'string', 'alpha_dash']
            : ['string', 'alpha_dash'];

        return [
            'value' => [
                ...$valueRules,
                Rule::unique('institution_categories', 'value')
                    ->ignore($categoryId),
            ],
            'label' => ['required', 'string', 'max:100'],
            'icon' => ['required', 'string', 'max:100'],
            'color' => [
                'required',
                Rule::in(['green', 'blue', 'amber', 'pink', 'red', 'purple', 'orange', 'gray']),
            ],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     * Strip 'value' from PUT requests (value is immutable)
     */
    protected function prepareForValidation(): void
    {
        if ($this->isMethod('PUT')) {
            $this->request->remove('value');
        }
    }
}
