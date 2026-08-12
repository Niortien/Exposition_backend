<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Kyc\ModerateKycRequest;
use App\Http\Requests\Kyc\SubmitKycRequest;
use App\Http\Resources\KycRequestResource;
use App\Models\KycRequest;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\JsonResponse;

class KycController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function submit(SubmitKycRequest $request): JsonResponse
    {
        $user = $request->user();

        $idDocumentPath = $request->file('id_document')->store("kyc/{$user->id}", 'local');
        $proofDocumentPath = $request->file('proof_document')->store("kyc/{$user->id}", 'local');

        $kycRequest = $user->kycRequests()->create([
            'id_document_path' => $idDocumentPath,
            'proof_document_path' => $proofDocumentPath,
            'requested_role' => $request->input('requested_role'),
            'status' => 'en_attente',
        ]);

        $this->auditLog->log($user, 'kyc.submitted', $kycRequest, ['requested_role' => $kycRequest->requested_role]);

        return response()->json([
            'id' => $kycRequest->id,
            'status' => 'pending',
        ], 201);
    }

    public function index(): JsonResponse
    {
        $kycRequests = KycRequest::query()
            ->with('user:id,name,email,phone')
            ->latest()
            ->get();

        return response()->json(KycRequestResource::collection($kycRequests));
    }

    public function moderate(ModerateKycRequest $request, KycRequest $kycRequest): JsonResponse
    {
        $kycRequest->update([
            'status' => $request->string('status'),
            'rejection_reason' => $request->input('rejection_reason'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($kycRequest->status === 'valide'
            && $kycRequest->requested_role
            && in_array($kycRequest->requested_role, UserRole::monetizableValues(), true)
        ) {
            $kycRequest->user->assignRole($kycRequest->requested_role);
        }

        $this->auditLog->log($request->user(), 'kyc.moderated', $kycRequest, [
            'status' => $kycRequest->status,
            'requested_role' => $kycRequest->requested_role,
        ]);

        return response()->json(new KycRequestResource($kycRequest->fresh(['user'])));
    }
}
