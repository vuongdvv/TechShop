<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index(int $reviewId): JsonResponse
    {
        if ($reviewId <= 0) {
            return response()->json(['success' => false, 'message' => 'Du lieu khong hop le'], 422);
        }

        $comments = DB::table('comments as c')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->select(['c.*', 'u.full_name as user_name'])
            ->where('c.review_id', $reviewId)
            ->orderBy('c.created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'comments' => $comments,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'review_id' => ['required', 'integer', 'min:1'],
            'parent_id' => ['nullable', 'integer', 'min:0'],
            'content' => ['required', 'string'],
        ]);

        $userId = (int) $request->attributes->get('auth_user_id');
        $productId = (int) $validated['product_id'];
        $reviewId = (int) $validated['review_id'];
        $parentId = (int) ($validated['parent_id'] ?? 0);
        $content = trim((string) $validated['content']);

        if ($content === '') {
            return response()->json(['success' => false, 'message' => 'Noi dung binh luan khong duoc de trong'], 422);
        }

        $reviewExists = DB::table('reviews')
            ->where('id', $reviewId)
            ->where('product_id', $productId)
            ->exists();

        if (!$reviewExists) {
            return response()->json(['success' => false, 'message' => 'Danh gia khong ton tai'], 404);
        }

        if ($parentId > 0) {
            $parentExists = DB::table('comments')
                ->where('id', $parentId)
                ->where('review_id', $reviewId)
                ->exists();

            if (!$parentExists) {
                return response()->json(['success' => false, 'message' => 'Binh luan cha khong ton tai'], 404);
            }
        }

        $commentId = DB::table('comments')->insertGetId([
            'user_id' => $userId,
            'product_id' => $productId,
            'review_id' => $reviewId,
            'parent_id' => $parentId > 0 ? $parentId : null,
            'content' => $content,
            'created_at' => now(),
        ]);

        $comment = DB::table('comments as c')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->select(['c.id', 'c.review_id', 'c.parent_id', 'c.content', 'c.created_at', 'u.full_name as user_name'])
            ->where('c.id', $commentId)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Binh luan thanh cong',
            'data' => [
                'comment' => $comment,
            ],
        ], 201);
    }
}
