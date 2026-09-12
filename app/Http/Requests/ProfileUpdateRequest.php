<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name'   => ['required', 'string', 'max:100'],
            'email'  => [
                'required', 'string', 'email', 'max:191',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'avatar' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'name'  => trim((string) $this->input('name')),
        ]);
    }
}