<?php

namespace App\Http\Requests\Admin;

use App\Models\Playlist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('playlist')) ?? false;
    }

    public function rules(): array
    {
        $playlist = $this->route('playlist');

        return [
            'title'         => ['required', 'string', 'max:191'],
            'slug'          => [
                'nullable', 'string', 'max:191', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('playlists', 'slug')->ignore($playlist->id),
            ],
            'description'   => ['nullable', 'string', 'max:5000'],
            'thumbnail_url' => ['nullable', 'url', 'max:500'],
            'status'        => ['required', Rule::in([
                Playlist::STATUS_DRAFT,
                Playlist::STATUS_PUBLISHED,
                Playlist::STATUS_ARCHIVED,
            ])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'slug'  => $this->input('slug') ? strtolower(trim((string) $this->input('slug'))) : null,
        ]);
    }
}