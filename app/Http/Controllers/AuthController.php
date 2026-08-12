<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateMeRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\Auth\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService,
        private readonly AuditLogService $auditLog,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone'),
            'password' => Hash::make($request->string('password')),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        $this->auditLog->log($user, 'user.registered');

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->findByLogin($request->string('login'));

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Identifiants incorrects.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        $this->auditLog->log($user, 'user.login');

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => new UserResource($request->user())]);
    }

    public function updateMe(UpdateMeRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return response()->json(['user' => new UserResource($request->user()->fresh())]);
    }

    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        $this->otpService->request($request->user(), $request->string('purpose'), $request->input('role') ?: null);

        return response()->json(['message' => 'Code envoyé.']);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $this->otpService->verify(
            $request->user(),
            $request->string('code'),
            $request->string('purpose'),
            $request->input('role') ?: null,
        );

        return response()->json(['user' => new UserResource($request->user()->fresh())]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = $this->findByLogin($request->string('login'));

        if ($user) {
            $this->otpService->request($user, 'password_reset');
        }

        return response()->json(['message' => 'Si un compte existe, un code a été envoyé.']);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = $this->findByLogin($request->string('login'));

        if (! $user) {
            // Message générique volontairement identique à celui d'un code OTP invalide,
            // pour ne pas laisser un attaquant déduire l'existence d'un compte.
            throw ValidationException::withMessages([
                'code' => ['Code invalide ou expiré.'],
            ]);
        }

        $this->otpService->verify($user, $request->string('code'), 'password_reset');

        $user->update(['password' => Hash::make($request->string('password'))]);

        $this->auditLog->log($user, 'user.password_reset');

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    private function findByLogin(string $login): ?User
    {
        return User::where('email', $login)->orWhere('phone', $login)->first();
    }
}
