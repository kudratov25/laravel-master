<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\BaseRequest;

class ForgotPasswordRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }
}
