<?php

namespace App\Http\Requests\Admin;

use App\Models\Video;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            // Only a consistency check — the controller uses the session DTO
            // for title, description, thumbnail, channel, duration, etc.
            'youtube_video_id' => ['required', 'string', 'size:11'],

            'category_id'    => ['nullable', 'integer', 'exists:categories,id'],
            'status'         => ['required', Rule::in([
                Video::STATUS_DRAFT,
                Video::STATUS_PUBLISHED,
            ])],
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