<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCustomerController extends Controller
{
    public function update(Request $request, int $userId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'integer', 'in:0,1'],
            'role' => ['required', 'string', 'in:user,admin'],
        ]);

        $adminId = (int) $request->attributes->get('auth_user_id');

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'status' => (int) $validated['status'],
                'role' => $validated['role'],
            ]);

        $logout = $userId === $adminId && ($validated['role'] !== 'admin' || (int) $validated['status'] === 0);

        return response()->json([
            'success' => true,
            'data' => ['logout' => $logout],
        ]);
    }
}
