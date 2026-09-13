<?php

namespace App\Http\Requests\Admin;

use App\Models\Video;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('video')) ?? false;
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:20000'],
            'category_id'    => ['nullable', 'integer', 'exists:categories,id'],
            'visibility'     => ['required', Rule::in([
                Video::VISIBILITY_PUBLIC,
                Video::VISIBILITY_PRIVATE,
            ])],
            'is_featured'    => ['sometimes', 'boolean'],
            'is_daily_focus' => ['sometimes', 'boolean'],
            'display_order'  => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured'    => $this->boolean('is_featured'),
            'is_daily_focus' => $this->boolean('is_daily_focus'),
        ]);
    }
}