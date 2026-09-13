<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('category')) ?? false;
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name'        => ['required', 'string', 'max:100'],
            'slug'        => [
                'nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('categories', 'slug')->ignore($category->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon'        => ['nullable', 'string', 'max:32'],
            'image'       => ['nullable', 'url', 'max:500'],
            'status'      => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'slug' => $this->input('slug') ? strtolower(trim((string) $this->input('slug'))) : null,
        ]);
    }
}