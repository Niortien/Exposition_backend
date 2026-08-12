<?php

namespace App\Http\Requests\Kyc;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'proof_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'requested_role' => ['nullable', Rule::in(UserRole::monetizableValues())],
        ];
    }
}
