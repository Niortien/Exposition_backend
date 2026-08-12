<?php

namespace App\Http\Requests\Kyc;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModerateKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['valide', 'refuse'])],
            'rejection_reason' => [Rule::requiredIf(fn () => $this->input('status') === 'refuse'), 'nullable', 'string', 'max:500'],
        ];
    }
}
