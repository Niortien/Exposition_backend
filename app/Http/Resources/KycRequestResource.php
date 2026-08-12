<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KycRequestResource extends JsonResource
{
    private const STATUS_MAP = [
        'en_attente' => 'pending',
        'valide' => 'verified',
        'refuse' => 'refused',
    ];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => self::STATUS_MAP[$this->status] ?? $this->status,
            'requestedRole' => $this->requested_role,
            'rejectionReason' => $this->rejection_reason,
            'submittedAt' => $this->created_at,
            'applicantName' => $this->user?->name,
            'applicantEmail' => $this->user?->email,
            'applicantPhone' => $this->user?->phone,
            // Pas encore de route de téléchargement signée pour les documents KYC — voir le plan.
            'idDocumentUrl' => null,
            'proofDocumentUrl' => null,
        ];
    }
}
