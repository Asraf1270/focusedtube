<?php

namespace App\Http\Requests\Admin;

use App\Models\Playlist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:191'],
            'slug'          => ['nullable', 'string', 'max:191', 'regex:/^[a-z0-9-]+$/', 'unique:playlists,slug'],
            'description'   => ['nullable', 'string', 'max:5000'],
            'thumbnail_url' => ['nullable', 'url', 'max:500'],
            'status'        => ['required', Rule::in([Playlist::STATUS_DRAFT, Playlist::STATUS_PUBLISHED])],
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