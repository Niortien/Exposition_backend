<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purpose' => ['required', Rule::in(['phone_verification', 'role_activation'])],
            'role' => [
                Rule::requiredIf(fn () => $this->input('purpose') === 'role_activation'),
                Rule::in(UserRole::monetizableValues()),
            ],
        ];
    }
}
