<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderPlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageVideos', $this->route('playlist')) ?? false;
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array', 'min:1', 'max:1000'],
            'ids.*' => ['integer', 'min:1'],
        ];
    }
}