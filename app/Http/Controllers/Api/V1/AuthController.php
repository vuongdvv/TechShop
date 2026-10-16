<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'input' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $input = trim($validated['input']);
        $password = $validated['password'];

        $user = DB::table('users')
            ->select(['id', 'full_name', 'email', 'password', 'status', 'role'])
            ->where('email', $input)
            ->orWhere('full_name', $input)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan cua ban khong ton tai. Vui long dang ky de tiep tuc',
            ], 404);
        }

        if ((int) $user->status === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan cua ban da bi khoa',
            ], 403);
        }

        if (!password_verify($password, (string) $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mat khau khong dung',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dang nhap thanh cong',
            'data' => [
                'token' => $this->makeToken((int) $user->id, (string) $user->full_name, (string) $user->role),
                'user' => [
                    'id' => (int) $user->id,
                    'name' => (string) $user->full_name,
                    'role' => (string) $user->role,
                ],
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'confirm' => ['required', 'string', 'min:6'],
        ]);

        if ($validated['password'] !== $validated['confirm']) {
            return response()->json([
                'success' => false,
                'message' => 'Mat khau xac nhan khong khop',
            ], 422);
        }

        if (DB::table('users')->where('full_name', trim($validated['full_name']))->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ten nguoi dung da ton tai',
            ], 409);
        }

        if (DB::table('users')->where('email', trim($validated['email']))->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email da duoc su dung',
            ], 409);
        }

        if (DB::table('users')->where('phone', trim($validated['phone']))->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'So dien thoai da duoc su dung',
            ], 409);
        }

        $userId = DB::table('users')->insertGetId([
            'full_name' => trim($validated['full_name']),
            'email' => trim($validated['email']),
            'phone' => trim($validated['phone']),
            'password' => password_hash($validated['password'], PASSWORD_BCRYPT),
            'role' => 'user',
            'status' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dang ky thanh cong',
            'data' => [
                'token' => $this->makeToken($userId, trim($validated['full_name']), 'user'),
                'user' => [
                    'id' => $userId,
                    'name' => trim($validated['full_name']),
                    'role' => 'user',
                ],
            ],
        ], 201);
    }

    public function logout(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Dang xuat thanh cong',
        ]);
    }

    private function makeToken(int $userId, string $userName, string $role): string
    {
        $secret = (string) env('TECHSHOP_JWT_SECRET');
        $algorithm = (string) env('TECHSHOP_JWT_ALGORITHM', 'HS256');
        $expires = 86400;

        $issuedAt = time();

        return JWT::encode([
            'iat' => $issuedAt,
            'exp' => $issuedAt + $expires,
            'user_id' => $userId,
            'user_name' => $userName,
            'role' => $role,
        ], $secret, $algorithm);
    }
}
