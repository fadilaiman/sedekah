<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class InstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $institutionId = $this->route('institution')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('institutions', 'name')
                    ->ignore($institutionId)
                    ->whereNull('deleted_at'),
            ],
            'category' => [
                'required',
                'string',
                Rule::in(Cache::remember('category_values', 3600, fn () =>
                    Category::active()->pluck('value')->all()
                )),
            ],
            'is_verified' => ['boolean'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'external_campaign_url' => ['nullable', 'url', 'max:500'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'coords_source' => ['nullable', 'string', 'max:100'],
        ];
    }
}
