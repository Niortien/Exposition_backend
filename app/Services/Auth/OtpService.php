<?php

namespace App\Services\Auth;

use App\Jobs\SendOtpSmsJob;
use App\Models\Otp;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpService
{
    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function request(User $user, string $purpose, ?string $role = null): Otp
    {
        $code = (string) random_int(100000, 999999);

        $otp = $user->otps()->create([
            'phone' => $user->phone,
            'code_hash' => Hash::make($code),
            'purpose' => $purpose,
            'role' => $role,
            'expires_at' => now()->addMinutes((int) config('services.otp_sms.ttl_minutes', 5)),
        ]);

        SendOtpSmsJob::dispatch($user->phone, $code);

        return $otp;
    }

    public function verify(User $user, string $code, string $purpose, ?string $role = null): Otp
    {
        return DB::transaction(function () use ($user, $code, $purpose, $role) {
            $otp = $user->otps()
                ->where('purpose', $purpose)
                ->when($role, fn ($query) => $query->where('role', $role))
                ->whereNull('consumed_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $otp || $otp->isExpired()) {
                throw ValidationException::withMessages(['code' => ["Code invalide ou expiré."]]);
            }

            if ($otp->attempts >= self::MAX_ATTEMPTS) {
                throw ValidationException::withMessages(['code' => ['Nombre de tentatives dépassé, redemandez un code.']]);
            }

            if (! Hash::check($code, $otp->code_hash)) {
                $otp->increment('attempts');
                throw ValidationException::withMessages(['code' => ['Code invalide ou expiré.']]);
            }

            $otp->update(['consumed_at' => now()]);

            if ($purpose === 'phone_verification') {
                // phone_verified_at n'est pas dans #[Fillable] du modèle User (protection contre
                // le mass-assignment via des endpoints publics) — forceFill() est volontaire ici
                // car cette écriture vient de logique interne de confiance, pas d'input utilisateur.
                $user->forceFill(['phone_verified_at' => now()])->save();
            }

            $this->auditLog->log($user, 'otp.verified', $otp, ['purpose' => $purpose, 'role' => $role]);

            return $otp;
        });
    }

    public function hasConsumedRoleActivationOtp(User $user, string $role): bool
    {
        return $user->otps()
            ->where('purpose', 'role_activation')
            ->where('role', $role)
            ->whereNotNull('consumed_at')
            ->exists();
    }
}
