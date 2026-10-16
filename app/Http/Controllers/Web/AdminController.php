<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(Request $request): View|RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        $totalOrders = (int) DB::table('orders')->whereIn('status', ['paid', 'completed'])->count();
        $totalRevenue = (float) DB::table('orders')->whereIn('status', ['paid', 'completed'])->sum('total_price');
        $totalProducts = (int) DB::table('products')->count();
        $totalUsers = (int) DB::table('users')->count();

        $revenueByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $revenueByDay[] = (float) DB::table('orders')
                ->whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'completed'])
                ->sum('total_price');
        }

        $topProducts = DB::table('order_items as od')
            ->join('orders as o', 'od.order_id', '=', 'o.id')
            ->join('products as p', 'od.product_id', '=', 'p.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('product_images as pi', 'p.id', '=', 'pi.product_id')
            ->select([
                'p.id',
                'p.name',
                'c.name as category_name',
                DB::raw('MAX(CASE WHEN pi.is_main = 1 THEN pi.image_url END) AS image_url'),
                DB::raw('COUNT(od.product_id) AS total_sold'),
            ])
            ->whereIn('o.status', ['paid', 'completed'])
            ->groupBy('p.id', 'p.name', 'c.name')
            ->orderByDesc('total_sold')
            ->limit(4)
            ->get();

        $recentOrders = DB::table('orders as o')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->leftJoin('payments as p', 'p.order_id', '=', 'o.id')
            ->select([
                'o.id',
                'u.full_name as customer',
                'o.total_price',
                DB::raw("CASE WHEN o.status = 'pending' AND p.method = 'vnpay' AND p.status = 'cancel' THEN 'cancel' ELSE o.status END AS display_status"),
            ])
            ->selectSub(function ($query): void {
                $query->from('order_items as od')
                    ->join('products as p2', 'od.product_id', '=', 'p2.id')
                    ->whereColumn('od.order_id', 'o.id')
                    ->select('p2.name')
                    ->limit(1);
            }, 'product_name')
            ->orderByDesc('o.created_at')
            ->limit(4)
            ->get();

        return view('admin.dashboard', $this->baseViewData($request) + [
            'adminName' => $admin['name'],
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'totalProducts' => $totalProducts,
            'totalUsers' => $totalUsers,
            'revenueByDay' => $revenueByDay,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function products(Request $request): View|RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        $keyword = trim((string) $request->query('keyword', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $totalProduct = (int) DB::table('products')->count();
        $outOfStock = (int) DB::table('products')->where('stock', 0)->count();
        $lowStockThreshold = 5;
        $lowStock = (int) DB::table('products')->where('stock', '>', 0)->where('stock', '<=', $lowStockThreshold)->count();

        $query = DB::table('products as p')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('product_images as img', function ($join): void {
                $join->on('img.product_id', '=', 'p.id')->where('img.is_main', 1);
            })
            ->where('p.name', 'like', '%' . $keyword . '%');

        if ($request->has('low_stock')) {
            $query->where('p.stock', '>', 0)->where('p.stock', '<=', $lowStockThreshold);
        } elseif ($request->has('out_stock')) {
            $query->where('p.stock', 0);
        }

        $totalRows = (clone $query)->count('p.id');
        $totalPages = max(1, (int) ceil($totalRows / $limit));

        $products = $query
            ->select(['p.*', 'b.name as brand_name', 'c.name as category_name', 'img.image_url'])
            ->orderByDesc('p.created_at')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $start = $totalRows > 0 ? $offset + 1 : 0;
        $end = min($offset + $limit, $totalRows);

        return view('admin.products.index', $this->baseViewData($request) + [
            'adminName' => $admin['name'],
            'products' => $products,
            'keyword' => $keyword,
            'totalProduct' => $totalProduct,
            'outOfStock' => $outOfStock,
            'lowStock' => $lowStock,
            'totalRows' => $totalRows,
            'totalPages' => $totalPages,
            'page' => $page,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function addProductForm(Request $request): View|RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        return view('admin.products.add', $this->baseViewData($request) + [
            'adminName' => $admin['name'],
            'brands' => DB::table('brands')->orderBy('name')->get(),
            'categories' => DB::table('categories')->orderBy('name')->get(),
            'errorsList' => [],
        ]);
    }

    public function addProduct(Request $request): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_id' => ['required', 'integer', 'min:1'],
            'category_id' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:0,1'],
            'cpu' => ['nullable', 'string', 'max:255'],
            'ram' => ['nullable', 'string', 'max:255'],
            'ssd' => ['nullable', 'string', 'max:255'],
            'gpu' => ['nullable', 'string', 'max:255'],
            'screen' => ['nullable', 'string', 'max:255'],
            'pin' => ['nullable', 'string', 'max:255'],
            'he_dieu_hanh' => ['nullable', 'string', 'max:255'],
            'kich_thuoc' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $slug = $this->createSlug($validated['name']);

        DB::beginTransaction();
        try {
            $productId = DB::table('products')->insertGetId([
                'name' => $validated['name'],
                'slug' => $slug,
                'brand_id' => (int) $validated['brand_id'],
                'category_id' => (int) $validated['category_id'],
                'description' => (string) ($validated['description'] ?? ''),
                'price' => (float) $validated['price'],
                'sale_price' => (float) ($validated['sale_price'] ?? 0),
                'stock' => (int) $validated['stock'],
                'status' => (int) $validated['status'],
                'created_at' => now(),
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $slug . '-' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('assets/images/products'), $imageName);

                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'image_url' => $imageName,
                    'is_main' => 1,
                ]);
            }

            DB::table('product_variants')->insert([
                'product_id' => $productId,
                'cpu' => (string) ($validated['cpu'] ?? ''),
                'ram' => (string) ($validated['ram'] ?? ''),
                'ssd' => (string) ($validated['ssd'] ?? ''),
                'gpu' => (string) ($validated['gpu'] ?? ''),
                'screen' => (string) ($validated['screen'] ?? ''),
                'pin' => (string) ($validated['pin'] ?? ''),
                'he_dieu_hanh' => (string) ($validated['he_dieu_hanh'] ?? ''),
                'kich_thuoc' => (string) ($validated['kich_thuoc'] ?? ''),
            ]);

            DB::commit();
            return redirect($this->adminPath($request, '/products/addproduct.php?success=1'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function editProductForm(Request $request): View|RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return redirect($this->adminPath($request, '/products/product.php'));
        }

        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return redirect($this->adminPath($request, '/products/product.php'));
        }

        return view('admin.products.edit', $this->baseViewData($request) + [
            'adminName' => $admin['name'],
            'product' => $product,
            'variant' => DB::table('product_variants')->where('product_id', $id)->first(),
            'image' => DB::table('product_images')->where('product_id', $id)->where('is_main', 1)->first(),
            'brands' => DB::table('brands')->orderBy('name')->get(),
            'categories' => DB::table('categories')->orderBy('name')->get(),
            'errorsList' => [],
        ]);
    }

    public function editProduct(Request $request): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return redirect($this->adminPath($request, '/products/product.php'));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'brand_id' => ['required', 'integer', 'min:1'],
            'category_id' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:0,1'],
            'cpu' => ['nullable', 'string', 'max:255'],
            'ram' => ['nullable', 'string', 'max:255'],
            'ssd' => ['nullable', 'string', 'max:255'],
            'gpu' => ['nullable', 'string', 'max:255'],
            'screen' => ['nullable', 'string', 'max:255'],
            'pin' => ['nullable', 'string', 'max:255'],
            'he_dieu_hanh' => ['nullable', 'string', 'max:255'],
            'kich_thuoc' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->createSlug($validated['name']);
        }

        DB::beginTransaction();
        try {
            DB::table('products')->where('id', $id)->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'brand_id' => (int) $validated['brand_id'],
                'category_id' => (int) $validated['category_id'],
                'description' => (string) ($validated['description'] ?? ''),
                'price' => (float) $validated['price'],
                'sale_price' => (float) ($validated['sale_price'] ?? 0),
                'stock' => (int) $validated['stock'],
                'status' => (int) $validated['status'],
            ]);

            $variantExists = DB::table('product_variants')->where('product_id', $id)->exists();
            $variantData = [
                'cpu' => (string) ($validated['cpu'] ?? ''),
                'ram' => (string) ($validated['ram'] ?? ''),
                'ssd' => (string) ($validated['ssd'] ?? ''),
                'gpu' => (string) ($validated['gpu'] ?? ''),
                'screen' => (string) ($validated['screen'] ?? ''),
                'pin' => (string) ($validated['pin'] ?? ''),
                'he_dieu_hanh' => (string) ($validated['he_dieu_hanh'] ?? ''),
                'kich_thuoc' => (string) ($validated['kich_thuoc'] ?? ''),
            ];

            if ($variantExists) {
                DB::table('product_variants')->where('product_id', $id)->update($variantData);
            } else {
                DB::table('product_variants')->insert($variantData + ['product_id' => $id]);
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $slug . '-' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('assets/images/products'), $imageName);

                $imageExists = DB::table('product_images')->where('product_id', $id)->where('is_main', 1)->exists();
                if ($imageExists) {
                    DB::table('product_images')->where('product_id', $id)->where('is_main', 1)->update(['image_url' => $imageName]);
                } else {
                    DB::table('product_images')->insert([
                        'product_id' => $id,
                        'image_url' => $imageName,
                        'is_main' => 1,
                    ]);
                }
            }

            DB::commit();
            return redirect($this->adminPath($request, '/products/editproduct.php?id=' . $id . '&success=1'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteProduct(Request $request): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $productId = (int) $validated['id'];
        DB::table('products')->where('id', $productId)->delete();

        return redirect($this->adminPath($request, '/products/product.php'))->with('success', 'Xóa sản phẩm thành công!');
    }

    public function orders(Request $request): View|RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        $orders = DB::table('orders as o')
            ->leftJoin('payments as p', 'p.order_id', '=', 'o.id')
            ->leftJoin('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->select([
                'o.id',
                'o.customer_name',
                'o.customer_phone',
                'o.total_price',
                'o.created_at',
                DB::raw("CASE WHEN o.status = 'pending' AND MAX(p.method) = 'vnpay' AND MAX(p.status) = 'cancel' THEN 'cancel' ELSE o.status END AS display_status"),
                DB::raw('COUNT(oi.id) AS total_items'),
            ])
            ->groupBy('o.id', 'o.customer_name', 'o.customer_phone', 'o.total_price', 'o.status', 'o.created_at')
            ->orderByDesc('o.id')
            ->get();

        return view('admin.orders.index', $this->baseViewData($request) + [
            'adminName' => $admin['name'],
            'orders' => $orders,
        ]);
    }

    public function orderDetail(Request $request): View|RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        $orderId = (int) $request->query('id', 0);
        if ($orderId <= 0) {
            return redirect($this->adminPath($request, '/orders/index.php'));
        }

        $order = DB::table('orders as o')
            ->leftJoin('payments as p', 'p.order_id', '=', 'o.id')
            ->leftJoin('addresses as a', 'a.user_id', '=', 'o.user_id')
            ->select(['o.*', 'p.method as payment_method', 'p.status as payment_status', 'a.city'])
            ->where('o.id', $orderId)
            ->first();

        if (!$order) {
            return redirect($this->adminPath($request, '/orders/index.php'));
        }

        $items = DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->leftJoin('product_images as pi', function ($join): void {
                $join->on('pi.product_id', '=', 'p.id')->where('pi.is_main', 1);
            })
            ->select(['oi.quantity', 'oi.price', 'p.name', 'pi.image_url'])
            ->where('oi.order_id', $orderId)
            ->get();

        $allowedNextStatus = [
            'pending' => ['shipped', 'cancel'],
            'paid' => ['shipped'],
            'shipped' => ['completed'],
            'completed' => [],
            'cancel' => [],
        ];

        return view('admin.orders.detail', $this->baseViewData($request) + [
            'adminName' => $admin['name'],
            'order' => $order,
            'items' => $items,
            'allowedNextStatus' => $allowedNextStatus,
        ]);
    }

    public function updateOrderStatus(Request $request): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:pending,paid,shipped,completed,cancel'],
        ]);

        DB::table('orders')->where('id', (int) $validated['order_id'])->update(['status' => $validated['status']]);

        return redirect($this->adminPath($request, '/orders/detail.php?id=' . (int) $validated['order_id']));
    }

    public function customers(Request $request): View|RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        if ($request->query('delete')) {
            $deleteId = (int) $request->query('delete');
            if ($deleteId > 0) {
                DB::table('users')->where('id', $deleteId)->delete();
                return redirect($this->adminPath($request, '/customers/customer.php?success=1'));
            }
        }

        $totalCustomers = (int) DB::table('users')->count();
        $newCustomers = (int) DB::table('users')->whereMonth('created_at', now()->month)->count();
        $activeUsers = (int) DB::table('users')->where('status', 1)->count();

        $customers = DB::table('users as u')
            ->leftJoin('orders as o', 'u.id', '=', 'o.user_id')
            ->select([
                'u.id',
                'u.full_name',
                'u.email',
                'u.phone',
                'u.status',
                'u.role',
                DB::raw('COUNT(o.id) as total_orders'),
                DB::raw('COALESCE(SUM(o.total_price),0) as total_spent'),
            ])
            ->groupBy('u.id', 'u.full_name', 'u.email', 'u.phone', 'u.status', 'u.role')
            ->orderByDesc('u.id')
            ->limit(10)
            ->get();

        return view('admin.customers.index', $this->baseViewData($request) + [
            'adminName' => $admin['name'],
            'totalCustomers' => $totalCustomers,
            'newCustomers' => $newCustomers,
            'activeUsers' => $activeUsers,
            'customers' => $customers,
        ]);
    }

    public function updateCustomer(Request $request): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return response()->json(['success' => false], 401);
        }

        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:0,1'],
            'role' => ['required', 'in:user,admin'],
        ]);

        DB::table('users')->where('id', (int) $validated['id'])->update([
            'status' => (int) $validated['status'],
            'role' => $validated['role'],
        ]);

        $logout = false;
        if ((int) $validated['id'] === (int) ($admin['id'] ?? 0) && ($validated['role'] !== 'admin' || (int) $validated['status'] === 0)) {
            $request->session()->flush();
            $logout = true;
        }

        return response()->json([
            'success' => true,
            'logout' => $logout,
        ]);
    }

    public function categories(Request $request): View|RedirectResponse|JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        if ($request->isMethod('post')) {
            $type = (string) $request->input('type', '');
            if ($type === 'category') {
                $name = trim((string) $request->input('category_name', ''));
                $slug = trim((string) $request->input('slug', ''));
                if ($name !== '') {
                    DB::table('categories')->insert(['name' => $name, 'slug' => $slug]);
                }
                if ($request->expectsJson() || $request->header('X-Fetch')) {
                    return response()->json(['success' => true]);
                }
                return redirect($this->adminPath($request, '/categories/brand_category.php'));
            }
        }

        return view('admin.categories.index', $this->baseViewData($request) + [
            'adminName' => $admin['name'],
            'totalBrands' => (int) DB::table('brands')->count(),
            'totalCategories' => (int) DB::table('categories')->count(),
            'brands' => DB::table('brands')->orderBy('id')->get(),
            'categories' => DB::table('categories')->orderBy('id')->get(),
        ]);
    }

    public function addBrand(Request $request): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return response()->json(['success' => false], 401);
        }

        $name = trim((string) $request->input('name', ''));
        $slug = trim((string) $request->input('slug', ''));

        if ($name === '') {
            return response()->json(['success' => false, 'message' => 'Thiếu tên']);
        }

        $logoName = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . preg_replace('/\s+/', '-', $logo->getClientOriginalName());
            $logo->move(public_path('assets/images/brands'), $logoName);
        }

        DB::table('brands')->insert([
            'name' => $name,
            'logo_image' => $logoName,
            'slug' => $slug,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateBrandCategory(Request $request): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return response()->json(['success' => false], 401);
        }

        $type = (string) $request->input('type', 'category');

        if ($type === 'brand') {
            $id = (int) $request->input('id', 0);
            $name = trim((string) $request->input('name', ''));
            $slug = $this->createSlug($name);

            if ($id <= 0 || $name === '') {
                return response()->json(['success' => false, 'message' => 'Missing data']);
            }

            $update = ['name' => $name, 'slug' => $slug];
            $logoUpdated = false;
            $logoName = null;

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoName = strtolower(preg_replace('/[^a-z0-9_-]/', '', pathinfo($logo->getClientOriginalName(), PATHINFO_FILENAME))) . '_' . time() . '.' . $logo->getClientOriginalExtension();
                $logo->move(public_path('assets/images/brands'), $logoName);
                $update['logo_image'] = $logoName;
                $logoUpdated = true;
            } else {
                $current = DB::table('brands')->select(['logo_image'])->where('id', $id)->first();
                $logoName = $current->logo_image ?? null;
            }

            DB::table('brands')->where('id', $id)->update($update);

            return response()->json([
                'success' => true,
                'logo' => $logoName,
                'logo_updated' => $logoUpdated,
            ]);
        }

        $id = (int) $request->input('id', 0);
        $name = trim((string) $request->input('name', ''));
        $slug = trim((string) $request->input('slug', ''));

        if ($id <= 0 || $name === '') {
            return response()->json(['success' => false, 'message' => 'Missing data']);
        }

        DB::table('categories')->where('id', $id)->update([
            'name' => $name,
            'slug' => $slug,
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteBrandCategory(Request $request): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if (!$admin) {
            return redirect($this->loginPath($request));
        }

        $id = (int) $request->query('id', 0);
        $type = (string) $request->query('type', '');

        if ($id <= 0 || ($type !== 'brand' && $type !== 'category')) {
            return redirect($this->adminPath($request, '/categories/brand_category.php'));
        }

        if ($type === 'brand') {
            $total = (int) DB::table('products')->where('brand_id', $id)->count();
            if ($total > 0) {
                return redirect($this->adminPath($request, '/categories/brand_category.php'))->with('error', 'Không thể xoá thương hiệu vì đang có sản phẩm.');
            }
            DB::table('brands')->where('id', $id)->delete();
            return redirect($this->adminPath($request, '/categories/brand_category.php'))->with('success', 'Xoá thương hiệu thành công.');
        }

        $total = (int) DB::table('products')->where('category_id', $id)->count();
        if ($total > 0) {
            return redirect($this->adminPath($request, '/categories/brand_category.php'))->with('error', 'Không thể xoá danh mục vì đang có sản phẩm.');
        }

        DB::table('categories')->where('id', $id)->delete();
        return redirect($this->adminPath($request, '/categories/brand_category.php'))->with('success', 'Xoá danh mục thành công.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->flush();
        $cookieName = (string) env('TECHSHOP_JWT_COOKIE', 'techshop_token');
        return redirect($this->loginPath($request))->withoutCookie($cookieName);
    }

    private function baseViewData(Request $request): array
    {
        $frontUrl = rtrim($request->getSchemeAndHttpHost() . $request->getBasePath(), '/');

        return [
            'frontUrl' => $frontUrl,
            'baseUrl' => $frontUrl,
        ];
    }

    private function loginPath(Request $request): string
    {
        $uriPath = (string) (parse_url($request->getRequestUri(), PHP_URL_PATH) ?? '');
        $origin = rtrim($request->getSchemeAndHttpHost(), '/');
        return preg_match('#^/TechShop(?:/|$)#', $uriPath) === 1
            ? $origin . '/TechShop/auth/login.php'
            : $origin . '/auth/login.php';
    }

    private function adminPath(Request $request, string $path): string
    {
        $uriPath = (string) (parse_url($request->getRequestUri(), PHP_URL_PATH) ?? '');
        $origin = rtrim($request->getSchemeAndHttpHost(), '/');
        $prefix = preg_match('#^/TechShop(?:/|$)#', $uriPath) === 1 ? '/TechShop/admin' : '/admin';
        return $origin . $prefix . $path;
    }

    private function requireAdmin(Request $request): ?array
    {
        $adminId = (int) $request->session()->get('admin_id', 0);
        if ($adminId > 0) {
            $user = DB::table('users')->select(['id', 'full_name', 'role', 'status'])->where('id', $adminId)->first();
            if ($user && $user->role === 'admin' && (int) $user->status === 1) {
                return ['id' => (int) $user->id, 'name' => (string) $user->full_name];
            }
        }

        $sessionUserId = (int) $request->session()->get('user_id', 0);
        if ($sessionUserId > 0) {
            $user = DB::table('users')->select(['id', 'full_name', 'role', 'status'])->where('id', $sessionUserId)->first();
            if ($user && $user->role === 'admin' && (int) $user->status === 1) {
                $request->session()->put('admin_id', (int) $user->id);
                $request->session()->put('admin_name', (string) $user->full_name);
                return ['id' => (int) $user->id, 'name' => (string) $user->full_name];
            }
        }

        $cookieName = (string) env('TECHSHOP_JWT_COOKIE', 'techshop_token');
        $token = (string) $request->cookie($cookieName, '');
        $secret = (string) env('TECHSHOP_JWT_SECRET', '');
        $algorithm = (string) env('TECHSHOP_JWT_ALGORITHM', 'HS256');

        if ($token === '' || $secret === '') {
            return null;
        }

        try {
            $payload = (array) JWT::decode($token, new Key($secret, $algorithm));
            $userId = (int) ($payload['user_id'] ?? 0);
            if ($userId <= 0) {
                return null;
            }

            $user = DB::table('users')->select(['id', 'full_name', 'role', 'status'])->where('id', $userId)->first();
            if (!$user || $user->role !== 'admin' || (int) $user->status !== 1) {
                return null;
            }

            $request->session()->put('user_id', (int) $user->id);
            $request->session()->put('user_name', (string) $user->full_name);
            $request->session()->put('admin_id', (int) $user->id);
            $request->session()->put('admin_name', (string) $user->full_name);

            return ['id' => (int) $user->id, 'name' => (string) $user->full_name];
        } catch (\Throwable) {
            return null;
        }
    }

    private function createSlug(string $string): string
    {
        $string = strtolower(trim($string));
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        $ascii = $ascii !== false ? $ascii : $string;
        $slug = preg_replace('/[^a-z0-9-]/', '-', $ascii);
        $slug = preg_replace('/-+/', '-', (string) $slug);
        return trim((string) $slug, '-');
    }
}
