<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    private const KYC_STATUS_MAP = [
        'en_attente' => 'pending',
        'valide' => 'verified',
        'refuse' => 'refused',
    ];

    public function toArray(Request $request): array
    {
        $latestKyc = $this->latestKycRequest;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phoneVerified' => $this->isPhoneVerified(),
            'roles' => $this->getRoleNames()->values(),
            'kycStatus' => $latestKyc ? (self::KYC_STATUS_MAP[$latestKyc->status] ?? 'none') : 'none',
            'kycRejectionReason' => $latestKyc?->rejection_reason,
        ];
    }
}
