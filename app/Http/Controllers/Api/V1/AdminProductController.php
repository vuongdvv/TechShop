<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminProductController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        $slug = $this->slug($validated['name']);

        $productId = DB::table('products')->insertGetId([
            'name' => $validated['name'],
            'slug' => $slug,
            'brand_id' => (int) $validated['brand_id'],
            'category_id' => (int) $validated['category_id'],
            'description' => $validated['description'] ?? '',
            'price' => (float) $validated['price'],
            'sale_price' => (float) ($validated['sale_price'] ?? 0),
            'stock' => (int) $validated['stock'],
            'status' => (int) $validated['status'],
            'created_at' => now(),
        ]);

        $this->storeMainImage($request, $productId, $slug);
        $this->upsertVariant($productId, $validated);

        return response()->json(['success' => true, 'data' => ['product_id' => $productId]], 201);
    }

    public function update(Request $request, int $productId): JsonResponse
    {
        $validated = $this->validatePayload($request, true);

        $name = (string) ($validated['name'] ?? '');
        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($name !== '' && $slug === '') {
            $slug = $this->slug($name);
        }

        $payload = [];
        foreach (['name', 'brand_id', 'category_id', 'description', 'price', 'sale_price', 'stock', 'status'] as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = in_array($field, ['brand_id', 'category_id', 'stock', 'status'], true)
                    ? (int) $validated[$field]
                    : $validated[$field];
            }
        }
        if ($slug !== '') {
            $payload['slug'] = $slug;
        }

        if (!empty($payload)) {
            DB::table('products')->where('id', $productId)->update($payload);
        }

        $this->storeMainImage($request, $productId, $slug !== '' ? $slug : ('product-' . $productId), true);
        $this->upsertVariant($productId, $validated, true);

        return response()->json(['success' => true]);
    }

    public function destroy(int $productId): JsonResponse
    {
        $images = DB::table('product_images')
            ->where('product_id', $productId)
            ->pluck('image_url')
            ->all();

        DB::table('products')->where('id', $productId)->delete();

        $dir = base_path('../front-end/assets/images/products/');
        foreach ($images as $image) {
            $path = $dir . $image;
            if ($image && file_exists($path)) {
                @unlink($path);
            }
        }

        return response()->json(['success' => true]);
    }

    private function validatePayload(Request $request, bool $partial = false): array
    {
        $base = [
            'name' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'brand_id' => [$partial ? 'sometimes' : 'required', 'integer', 'min:1'],
            'category_id' => [$partial ? 'sometimes' : 'required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'price' => [$partial ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => [$partial ? 'sometimes' : 'required', 'integer', 'min:0'],
            'status' => [$partial ? 'sometimes' : 'required', 'integer', 'in:0,1'],
            'cpu' => ['nullable', 'string', 'max:255'],
            'ram' => ['nullable', 'string', 'max:255'],
            'ssd' => ['nullable', 'string', 'max:255'],
            'gpu' => ['nullable', 'string', 'max:255'],
            'screen' => ['nullable', 'string', 'max:255'],
            'pin' => ['nullable', 'string', 'max:255'],
            'he_dieu_hanh' => ['nullable', 'string', 'max:255'],
            'kich_thuoc' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ];

        return $request->validate($base);
    }

    private function slug(string $name): string
    {
        $slug = strtolower(trim(iconv('UTF-8', 'ASCII//TRANSLIT', $name) ?: $name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', (string) $slug);
        return trim((string) $slug, '-');
    }

    private function storeMainImage(Request $request, int $productId, string $slug, bool $update = false): void
    {
        if (!$request->hasFile('image')) {
            return;
        }

        $file = $request->file('image');
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $imageName = ($slug !== '' ? $slug : 'product') . '-' . time() . '.' . $ext;
        $targetDir = base_path('../front-end/assets/images/products');

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $file->move($targetDir, $imageName);

        $existing = DB::table('product_images')
            ->where('product_id', $productId)
            ->where('is_main', 1)
            ->first();

        if ($existing) {
            DB::table('product_images')
                ->where('product_id', $productId)
                ->where('is_main', 1)
                ->update(['image_url' => $imageName]);
            return;
        }

        DB::table('product_images')->insert([
            'product_id' => $productId,
            'image_url' => $imageName,
            'is_main' => 1,
        ]);
    }

    private function upsertVariant(int $productId, array $validated, bool $partial = false): void
    {
        $variantPayload = [
            'cpu' => $validated['cpu'] ?? '',
            'ram' => $validated['ram'] ?? '',
            'ssd' => $validated['ssd'] ?? '',
            'gpu' => $validated['gpu'] ?? '',
            'screen' => $validated['screen'] ?? '',
            'pin' => $validated['pin'] ?? '',
            'he_dieu_hanh' => $validated['he_dieu_hanh'] ?? '',
            'kich_thuoc' => $validated['kich_thuoc'] ?? '',
        ];

        $exists = DB::table('product_variants')->where('product_id', $productId)->exists();
        if ($exists) {
            DB::table('product_variants')->where('product_id', $productId)->update($variantPayload);
            return;
        }

        DB::table('product_variants')->insert(array_merge(['product_id' => $productId], $variantPayload));
    }
}
