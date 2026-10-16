<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCategoryController extends Controller
{
    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        DB::table('categories')->insert([
            'name' => trim($validated['name']),
            'slug' => trim((string) ($validated['slug'] ?? '')),
        ]);

        return response()->json(['success' => true], 201);
    }

    public function updateCategory(Request $request, int $categoryId): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        DB::table('categories')
            ->where('id', $categoryId)
            ->update([
                'name' => trim($validated['name']),
                'slug' => trim((string) ($validated['slug'] ?? '')),
            ]);

        return response()->json(['success' => true]);
    }

    public function storeBrand(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $logoName = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $logoName = time() . '_' . $file->getClientOriginalName();
            $target = base_path('../front-end/assets/images/brands');
            if (!is_dir($target)) {
                mkdir($target, 0777, true);
            }
            $file->move($target, $logoName);
        }

        DB::table('brands')->insert([
            'name' => trim($validated['name']),
            'slug' => trim((string) ($validated['slug'] ?? '')),
            'logo_image' => $logoName,
        ]);

        return response()->json(['success' => true], 201);
    }

    public function updateBrand(Request $request, int $brandId): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'slug' => trim((string) ($validated['slug'] ?? '')),
        ];

        $logoUpdated = false;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $base = strtolower((string) pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $base = preg_replace('/[^a-z0-9_-]/', '', $base) ?: 'brand';
            $logoName = $base . '_' . time() . '.' . $ext;
            $target = base_path('../front-end/assets/images/brands');
            if (!is_dir($target)) {
                mkdir($target, 0777, true);
            }
            $file->move($target, $logoName);
            $payload['logo_image'] = $logoName;
            $logoUpdated = true;
        }

        DB::table('brands')->where('id', $brandId)->update($payload);

        $brand = DB::table('brands')->select(['logo_image'])->where('id', $brandId)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'logo' => $brand?->logo_image,
                'logo_updated' => $logoUpdated,
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:brand,category'],
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $id = (int) $validated['id'];

        if ($validated['type'] === 'brand') {
            $total = (int) DB::table('products')->where('brand_id', $id)->count();
            if ($total > 0) {
                return response()->json(['success' => false, 'message' => 'Khong the xoa thuong hieu vi dang co san pham'], 422);
            }
            DB::table('brands')->where('id', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Xoa thuong hieu thanh cong']);
        }

        $total = (int) DB::table('products')->where('category_id', $id)->count();
        if ($total > 0) {
            return response()->json(['success' => false, 'message' => 'Khong the xoa danh muc vi dang co san pham'], 422);
        }

        DB::table('categories')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Xoa danh muc thanh cong']);
    }
}
