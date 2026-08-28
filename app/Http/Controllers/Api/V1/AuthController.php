<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login(): JsonResponse
    {
        request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', request()->email)->first();

        if (! $user || ! Hash::check(request()->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas'],
            ]);
        }

        if (! $user->active) {
            throw ValidationException::withMessages([
                'email' => ['El usuario no está activo.'],
            ]);
        }

        return $this->tokenResponse($user);
    }

    public function googleRedirect(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function googleCallback(): JsonResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user && ! $user->active) {
            return response()->json(['message' => 'El usuario no está activo.'], Response::HTTP_FORBIDDEN);
        }

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Usuario',
                'email' => $googleUser->getEmail(),
                'password' => Str::random(40),
                'google_id' => $googleUser->getId(),
            ]);
        } elseif (! $user->google_id) {
            $user->forceFill(['google_id' => $googleUser->getId()])->save();
        }

        return $this->tokenResponse($user);
    }

    private function tokenResponse(User $user): JsonResponse
    {
        $token = $user->createToken('authToken')->plainTextToken;
        $expiresAt = Carbon::now()->addDays(5);
        $user->tokens()->where('name', 'authToken')->update(['expires_at' => $expiresAt]);

        return response()->json([
            'token' => $token,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);
    }

    public function user(): JsonResponse
    {
        $user = auth()->user();
        if ($user && $user->active) {
            return response()->json($user);
        } else {
            auth()->logout();

            return response()->json(null, 401);
        }
    }

    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión Cerrada']);
    }
}
