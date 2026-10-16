<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'address_detail' => ['required', 'string', 'max:1000'],
        ]);

        $userId = (int) $request->attributes->get('auth_user_id');

        $address = DB::table('addresses')->where('user_id', $userId)->first();

        if ($address) {
            DB::table('addresses')->where('user_id', $userId)->update([
                'full_name' => trim($validated['full_name']),
                'phone' => trim($validated['phone']),
                'city' => trim($validated['city']),
                'address_detail' => trim($validated['address_detail']),
            ]);
        } else {
            DB::table('addresses')->insert([
                'user_id' => $userId,
                'full_name' => trim($validated['full_name']),
                'phone' => trim($validated['phone']),
                'city' => trim($validated['city']),
                'address_detail' => trim($validated['address_detail']),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat thong tin thanh cong',
        ]);
    }
}
