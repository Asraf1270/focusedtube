<?php

namespace App\Http\Requests\Admin;

use App\Services\Video\VideoService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkVideoActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                VideoService::BULK_PUBLISH,
                VideoService::BULK_UNPUBLISH,
                VideoService::BULK_ARCHIVE,
                VideoService::BULK_DELETE,
                VideoService::BULK_FEATURE,
                VideoService::BULK_UNFEATURE,
            ])],
            'ids'   => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer', 'min:1'],
        ];
    }
}