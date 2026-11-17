<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user login and issue API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::with('branch')->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kredensial email atau password salah.',
                'errors' => [
                    'email' => ['Kredensial email atau password salah.'],
                ],
            ], 422);
        }

        if (! $user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda dinonaktifkan. Silakan hubungi Super Admin.',
            ], 403);
        }

        // Hapus token lama jika ada (opsional single device) atau langsung buat token baru
        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'branch' => $user->branch ? [
                        'id' => $user->branch->id,
                        'code' => $user->branch->code,
                        'name' => $user->branch->name,
                        'is_central_warehouse' => $user->branch->is_central_warehouse,
                    ] : null,
                ],
            ],
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('branch');

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'branch' => $user->branch ? [
                        'id' => $user->branch->id,
                        'code' => $user->branch->code,
                        'name' => $user->branch->name,
                        'is_central_warehouse' => $user->branch->is_central_warehouse,
                    ] : null,
                ],
            ],
        ]);
    }

    /**
     * Handle user logout and revoke API token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil. Token telah dicabut.',
        ]);
    }
}
