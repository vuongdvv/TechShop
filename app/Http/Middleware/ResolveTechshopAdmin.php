<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ResolveTechshopAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = (int) $request->attributes->get('auth_user_id', 0);

        if ($userId <= 0) {
            return $this->forbidden('Chua dang nhap');
        }

        $user = DB::table('users')
            ->select(['id', 'role', 'status'])
            ->where('id', $userId)
            ->first();

        if (!$user || $user->role !== 'admin' || (int) $user->status !== 1) {
            return $this->forbidden('Khong co quyen truy cap');
        }

        return $next($request);
    }

    private function forbidden(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }
}
