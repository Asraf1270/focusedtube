<?php

namespace App\Http\Requests\Admin;

use App\Support\YouTube\YouTubeUrlParser;
use Illuminate\Foundation\Http\FormRequest;

class FetchVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! YouTubeUrlParser::isValid((string) $this->input('url'))) {
                $validator->errors()->add('url', 'Please provide a valid YouTube video URL.');
            }
        });
    }
}