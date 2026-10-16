<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTechshopUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $cookieName = (string) config('app.techshop_jwt_cookie', env('TECHSHOP_JWT_COOKIE', 'techshop_token'));
        $token = $request->cookie($cookieName);

        if (!$token) {
            return $this->unauthorized('Chua dang nhap');
        }

        $secret = (string) env('TECHSHOP_JWT_SECRET');
        $algorithm = (string) env('TECHSHOP_JWT_ALGORITHM', 'HS256');

        if ($secret === '') {
            return $this->unauthorized('JWT secret chua duoc cau hinh');
        }

        try {
            $payload = (array) JWT::decode($token, new Key($secret, $algorithm));
        } catch (ExpiredException) {
            return $this->unauthorized('Phien dang nhap da het han');
        } catch (\Throwable) {
            return $this->unauthorized('Token khong hop le');
        }

        if (!isset($payload['user_id'])) {
            return $this->unauthorized('Token thieu thong tin nguoi dung');
        }

        $request->attributes->set('auth_user_id', (int) $payload['user_id']);
        $request->attributes->set('auth_user_role', (string) ($payload['role'] ?? 'user'));
        $request->attributes->set('auth_user_name', (string) ($payload['user_name'] ?? ''));

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }
}
