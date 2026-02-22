<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class SubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'submitter_email' => ['required', 'email', 'max:255'],
            'submitter_name' => ['nullable', 'string', 'max:255'],

            // Institution fields
            'institution_name' => ['required', 'string', 'max:255'],
            'institution_category' => [
                'required',
                Rule::in(Cache::remember('category_values', 3600, fn () =>
                    Category::active()->pluck('value')->all()
                )),
            ],
            'institution_state' => ['required', 'string', 'max:100'],
            'institution_city' => ['required', 'string', 'max:100'],
            'institution_address' => ['nullable', 'string', 'max:500'],
            'institution_description' => ['nullable', 'string'],
            'institution_website_url' => ['nullable', 'url', 'max:500'],
            'institution_contact_email' => ['nullable', 'email', 'max:255'],
            'institution_contact_phone' => ['nullable', 'string', 'max:20'],
            'institution_maps_url' => ['nullable', 'url', 'max:500'],

            // QR image (required)
            'qr_image' => ['required', 'image', 'mimes:jpeg,png', 'max:100'], // 100 KB
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'qr_image.max' => 'QR image must not exceed 100KB.',
            'qr_image.mimes' => 'QR image must be a JPEG or PNG file.',
        ];
    }
}
