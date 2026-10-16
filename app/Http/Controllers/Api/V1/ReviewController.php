<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request, int $productId): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $productExists = DB::table('products')
            ->where('id', $productId)
            ->where('status', 1)
            ->exists();

        if (!$productExists) {
            return response()->json([
                'success' => false,
                'message' => 'San pham khong ton tai',
            ], 404);
        }

        $total = (int) DB::table('reviews')->where('product_id', $productId)->count();

        $reviews = DB::table('reviews as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->select([
                'r.id',
                'r.user_id',
                'r.product_id',
                'r.rating',
                'r.content',
                'r.created_at',
                'u.full_name as user_name',
            ])
            ->where('r.product_id', $productId)
            ->orderByDesc('r.created_at')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'total_pages' => (int) ceil($total / $limit),
                    'per_page' => $limit,
                ],
            ],
        ]);
    }

    public function store(Request $request, int $productId): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string', 'max:2000'],
        ]);

        $userId = (int) $request->attributes->get('auth_user_id');

        $productExists = DB::table('products')
            ->where('id', $productId)
            ->where('status', 1)
            ->exists();

        if (!$productExists) {
            return response()->json([
                'success' => false,
                'message' => 'San pham khong ton tai',
            ], 404);
        }

        $alreadyReviewed = DB::table('reviews')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'success' => false,
                'message' => 'Ban da danh gia san pham nay roi',
            ], 409);
        }

        $reviewId = DB::table('reviews')->insertGetId([
            'user_id' => $userId,
            'product_id' => $productId,
            'rating' => (int) $validated['rating'],
            'content' => trim((string) ($validated['content'] ?? '')),
            'created_at' => now(),
        ]);

        $review = DB::table('reviews as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->select([
                'r.id',
                'r.product_id',
                'r.rating',
                'r.content',
                'r.created_at',
                'u.full_name as user_name',
            ])
            ->where('r.id', $reviewId)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Danh gia thanh cong',
            'data' => [
                'review' => $review,
            ],
        ], 201);
    }
}
