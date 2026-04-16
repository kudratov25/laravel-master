<?php

namespace App\Http\Requests\Api\V1\ExamplePost;

use App\Http\Requests\Api\BaseRequest;

class ExamplePostUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'              => 'required|array',
            'title.uz'           => 'required|string|max:255',
            'title.oz'           => 'required|string|max:255',
            'title.ru'           => 'required|string|max:255',
            'title.en'           => 'nullable|string|max:255',
            'body'               => 'required|array',
            'body.uz'            => 'required|string',
            'body.oz'            => 'required|string',
            'body.ru'            => 'required|string',
            'body.en'            => 'nullable|string',
            'status'             => 'nullable|boolean',
            'cover'              => 'nullable|integer|exists:files,id',
            'attachments'        => 'nullable|array',
            'attachments.*'      => 'integer|exists:files,id',
        ];
    }
}
