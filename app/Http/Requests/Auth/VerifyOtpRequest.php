<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:6'],
            'purpose' => ['required', Rule::in(['phone_verification', 'role_activation'])],
            'role' => [
                Rule::requiredIf(fn () => $this->input('purpose') === 'role_activation'),
                Rule::in(UserRole::monetizableValues()),
            ],
        ];
    }
}
