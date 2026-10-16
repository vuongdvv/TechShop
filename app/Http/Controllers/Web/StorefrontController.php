<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(Request $request): View
    {
        $brandFilter = trim((string) $request->query('brand', ''));

        $productsQuery = DB::table('products as p')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
            ->leftJoin('product_images as img', function ($join): void {
                $join->on('img.product_id', '=', 'p.id')->where('img.is_main', '=', 1);
            })
            ->leftJoin('product_variants as v', 'v.product_id', '=', 'p.id')
            ->select([
                'p.id',
                'p.name',
                'p.slug',
                'p.price',
                'p.sale_price',
                'b.name as brand_name',
                'img.image_url',
                'v.cpu',
                'v.ram',
                'v.ssd',
            ])
            ->selectRaw('COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.id), 0) AS rating')
            ->where('p.stock', '>', 0);

        if ($brandFilter !== '') {
            $productsQuery->where('b.slug', $brandFilter);
        }

        if ($brandFilter !== '') {
            $productsQuery->orderByDesc('p.id');
        } else {
            $productsQuery->orderByDesc('p.price')->limit(4);
        }

        $brands = DB::table('brands')->select(['id', 'name', 'logo_image', 'slug'])->get();

        return view('storefront.home', $this->baseViewData($request) + [
            'products' => $productsQuery->get(),
            'brands' => $brands,
            'brandFilter' => $brandFilter,
        ]);
    }

    public function showLogin(Request $request): View
    {
        return view('storefront.auth.login', $this->baseViewData($request) + [
            'error' => (string) $request->query('error', ''),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $input = trim($validated['email']);
        $password = $validated['password'];

        $user = DB::table('users')
            ->select(['id', 'full_name', 'email', 'password', 'status', 'role'])
            ->where('email', $input)
            ->orWhere('full_name', $input)
            ->first();

        if (!$user) {
            return back()->withInput()->with('error', 'Tai khoan khong ton tai');
        }

        if ((int) $user->status === 0) {
            return back()->withInput()->with('error', 'Tai khoan da bi khoa');
        }

        if (!password_verify($password, (string) $user->password)) {
            return back()->withInput()->with('error', 'Mat khau khong dung');
        }

        $request->session()->put('user_id', (int) $user->id);
        $request->session()->put('user_name', (string) $user->full_name);

        $uriPath = (string) (parse_url($request->getRequestUri(), PHP_URL_PATH) ?? '');
        $isTechShopPath = preg_match('#^/TechShop(?:/|$)#', $uriPath) === 1;

        if ((string) $user->role === 'admin') {
            $request->session()->put('admin_id', (int) $user->id);
            $request->session()->put('admin_name', (string) $user->full_name);
        } else {
            $request->session()->forget(['admin_id', 'admin_name']);
        }

        $token = $this->makeToken((int) $user->id, (string) $user->full_name, (string) $user->role);
        $cookieName = (string) env('TECHSHOP_JWT_COOKIE', 'techshop_token');

        $target = ((string) $user->role === 'admin')
            ? ($isTechShopPath ? '/TechShop/admin/dashboard.php' : '/admin/dashboard.php')
            : ($isTechShopPath ? '/TechShop/home.php' : '/home.php');

        return redirect($target)->cookie($cookieName, $token, 60 * 24, '/');
    }

    public function showRegister(Request $request): View
    {
        return view('storefront.auth.register', $this->baseViewData($request) + [
            'error' => '',
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'confirm' => ['required', 'string', 'min:6'],
        ]);

        if ($validated['password'] !== $validated['confirm']) {
            return back()->withInput()->with('error', 'Mat khau xac nhan khong khop');
        }

        if (DB::table('users')->where('full_name', trim($validated['full_name']))->exists()) {
            return back()->withInput()->with('error', 'Ten nguoi dung da ton tai');
        }

        if (DB::table('users')->where('email', trim($validated['email']))->exists()) {
            return back()->withInput()->with('error', 'Email da duoc su dung');
        }

        if (DB::table('users')->where('phone', trim($validated['phone']))->exists()) {
            return back()->withInput()->with('error', 'So dien thoai da duoc su dung');
        }

        $userId = DB::table('users')->insertGetId([
            'full_name' => trim($validated['full_name']),
            'email' => trim($validated['email']),
            'phone' => trim($validated['phone']),
            'password' => password_hash($validated['password'], PASSWORD_BCRYPT),
            'role' => 'user',
            'status' => 1,
        ]);

        $request->session()->put('user_id', $userId);
        $request->session()->put('user_name', trim($validated['full_name']));

        $token = $this->makeToken($userId, trim($validated['full_name']), 'user');
        $cookieName = (string) env('TECHSHOP_JWT_COOKIE', 'techshop_token');

        return redirect()->route('store.home')->cookie($cookieName, $token, 60 * 24, '/');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['user_id', 'user_name', 'checkout_item_ids']);
        $cookieName = (string) env('TECHSHOP_JWT_COOKIE', 'techshop_token');

        return redirect()->route('store.auth.login')->withoutCookie($cookieName);
    }

    public function productList(Request $request): View
    {
        $limit = 8;
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $limit;

        $brandSlug = trim((string) $request->query('brand', ''));
        $categorySlug = trim((string) $request->query('category', ''));
        $keyword = trim((string) $request->query('keyword', ''));

        $query = DB::table('products as p')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('product_variants as pv', 'p.id', '=', 'pv.product_id')
            ->leftJoin('product_images as pi', function ($join): void {
                $join->on('p.id', '=', 'pi.product_id')->where('pi.is_main', '=', 1);
            })
            ->where('p.status', 1);

        if ($categorySlug !== '') {
            $query->where('c.slug', $categorySlug);
        }

        if ($brandSlug !== '') {
            $query->where('b.slug', $brandSlug);
        }

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword): void {
                $q->where('p.name', 'like', "%{$keyword}%")
                    ->orWhere('b.name', 'like', "%{$keyword}%")
                    ->orWhereExists(function ($sq) use ($keyword): void {
                        $sq->select(DB::raw(1))
                            ->from('product_variants as pv2')
                            ->whereColumn('pv2.product_id', 'p.id')
                            ->where('pv2.cpu', 'like', "%{$keyword}%");
                    });
            });
        }

        $products = (clone $query)
            ->select([
                'p.id',
                'p.name',
                'p.slug',
                'p.price',
                'p.sale_price',
                'b.name as brand_name',
                DB::raw('MIN(pv.cpu) as cpu'),
                DB::raw('MIN(pv.ram) as ram'),
                DB::raw('MIN(pv.ssd) as ssd'),
                DB::raw('MAX(pi.image_url) as image_url'),
            ])
            ->selectRaw('COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.id), 0) AS rating')
            ->groupBy('p.id', 'p.name', 'p.slug', 'p.price', 'p.sale_price', 'b.name')
            ->orderByDesc('p.id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $totalProducts = (clone $query)->distinct('p.id')->count('p.id');
        $brands = DB::table('brands')->select(['id', 'name', 'logo_image', 'slug'])->orderBy('name')->get();
        $categories = DB::table('categories')->select(['id', 'name', 'slug'])->orderBy('name')->get();

        return view('storefront.product.list', $this->baseViewData($request) + [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'brands' => $brands,
            'categories' => $categories,
            'brandSlug' => $brandSlug,
            'categorySlug' => $categorySlug,
            'keyword' => $keyword,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    public function productDetail(Request $request): View|RedirectResponse
    {
        $slug = trim((string) $request->query('slug', ''));
        if ($slug === '') {
            return redirect()->route('store.home');
        }

        $product = DB::table('products as p')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->select(['p.*', 'b.name as brand_name', 'c.name as category_name', 'c.slug as category_slug'])
            ->where('p.slug', $slug)
            ->where('p.status', 1)
            ->first();

        if (!$product) {
            return redirect()->route('store.home');
        }

        $images = DB::table('product_images')
            ->select(['image_url', 'is_main'])
            ->where('product_id', (int) $product->id)
            ->orderByDesc('is_main')
            ->orderBy('id')
            ->get();

        $variants = DB::table('product_variants')->where('product_id', (int) $product->id)->get();

        return view('storefront.product.detail', $this->baseViewData($request) + [
            'product' => $product,
            'images' => $images,
            'variants' => $variants,
        ]);
    }

    public function cartIndex(Request $request): View|RedirectResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            return redirect()->route('store.auth.login');
        }

        $cart = DB::table('carts')->select(['id'])->where('user_id', $userId)->first();
        $cartItems = collect();

        if ($cart) {
            $cartItems = DB::table('cart_items as ci')
                ->join('products as p', 'ci.product_id', '=', 'p.id')
                ->leftJoin('product_images as pi', function ($join): void {
                    $join->on('pi.product_id', '=', 'p.id')->where('pi.is_main', '=', 1);
                })
                ->select([
                    'ci.id as cart_item_id',
                    'ci.quantity',
                    'ci.price',
                    'p.id as product_id',
                    'p.name',
                    'p.slug',
                    'pi.image_url',
                ])
                ->where('ci.cart_id', (int) $cart->id)
                ->orderByDesc('ci.id')
                ->get();
        }

        return view('storefront.cart.index', $this->baseViewData($request) + [
            'cartItems' => $cartItems,
        ]);
    }

    public function cartAdd(Request $request): RedirectResponse|JsonResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Chua dang nhap'], 401);
            }
            return redirect()->route('store.auth.login');
        }

        $productId = (int) $request->query('id', 0);
        if ($productId <= 0) {
            return redirect()->route('store.product.list');
        }

        $product = DB::table('products')
            ->select(['id', 'price', 'sale_price'])
            ->where('id', $productId)
            ->where('status', 1)
            ->first();

        if (!$product) {
            return redirect()->route('store.product.list');
        }

        $price = !empty($product->sale_price) ? (float) $product->sale_price : (float) $product->price;

        $cart = DB::table('carts')->select(['id'])->where('user_id', $userId)->first();
        $cartId = $cart ? (int) $cart->id : DB::table('carts')->insertGetId(['user_id' => $userId, 'created_at' => now()]);

        $cartItem = DB::table('cart_items')
            ->select(['id', 'quantity'])
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            DB::table('cart_items')->where('id', (int) $cartItem->id)->update([
                'quantity' => ((int) $cartItem->quantity) + 1,
            ]);
        } else {
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => 1,
                'price' => $price,
            ]);
        }

        $cartCount = (int) DB::table('cart_items as ci')
            ->join('carts as c', 'c.id', '=', 'ci.cart_id')
            ->where('c.user_id', $userId)
            ->sum('ci.quantity');

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Da them san pham vao gio hang',
                'cartCount' => $cartCount,
            ]);
        }

        return redirect()->route('store.cart.index');
    }

    public function cartUpdate(Request $request): JsonResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Chua dang nhap'], 401);
        }

        $validated = $request->validate([
            'cart_item_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = DB::table('carts')->select(['id'])->where('user_id', $userId)->first();
        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'Khong tim thay gio hang'], 404);
        }

        $updated = DB::table('cart_items')
            ->where('cart_id', (int) $cart->id)
            ->where('id', (int) $validated['cart_item_id'])
            ->update(['quantity' => (int) $validated['quantity']]);

        if ($updated === 0) {
            return response()->json(['success' => false, 'message' => 'Khong tim thay san pham'], 404);
        }

        return response()->json(['success' => true]);
    }

    public function cartRemove(Request $request): RedirectResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            return redirect()->route('store.auth.login');
        }

        $cartItemId = (int) $request->query('id', 0);

        $cart = DB::table('carts')->select(['id'])->where('user_id', $userId)->first();
        if ($cart) {
            DB::table('cart_items')
                ->where('cart_id', (int) $cart->id)
                ->where('id', $cartItemId)
                ->delete();
        }

        return redirect()->route('store.cart.index');
    }

    public function checkoutForm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected_items' => ['required', 'array', 'min:1'],
            'selected_items.*' => ['integer', 'min:1'],
        ]);

        $request->session()->put('checkout_item_ids', array_values(array_unique(array_map('intval', $validated['selected_items']))));

        return redirect('/cart/checkout.php');
    }

    public function checkoutShow(Request $request): View|RedirectResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            return redirect()->route('store.auth.login');
        }

        $itemIds = (array) $request->session()->get('checkout_item_ids', []);
        if (empty($itemIds)) {
            return redirect()->route('store.cart.index');
        }

        $cart = DB::table('carts')->select(['id'])->where('user_id', $userId)->first();
        if (!$cart) {
            return redirect()->route('store.cart.index');
        }

        $items = DB::table('cart_items as ci')
            ->join('products as p', 'ci.product_id', '=', 'p.id')
            ->leftJoin('product_images as pi', function ($join): void {
                $join->on('pi.product_id', '=', 'p.id')->where('pi.is_main', '=', 1);
            })
            ->select([
                'ci.id as cart_item_id',
                'ci.quantity',
                'ci.price',
                'p.name',
                'p.slug',
                'pi.image_url',
            ])
            ->where('ci.cart_id', (int) $cart->id)
            ->whereIn('ci.id', $itemIds)
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('store.cart.index');
        }

        $address = DB::table('addresses')->where('user_id', $userId)->first();
        $totalPrice = $items->sum(fn($i) => ((float) $i->price) * ((int) $i->quantity));

        return view('storefront.cart.checkout', $this->baseViewData($request) + [
            'items' => $items,
            'userAddress' => $address,
            'totalPrice' => $totalPrice,
        ]);
    }

    public function checkoutProcess(Request $request): RedirectResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            return redirect()->route('store.auth.login');
        }

        $validated = $request->validate([
            'cart_item_ids' => ['required', 'array', 'min:1'],
            'cart_item_ids.*' => ['integer', 'min:1'],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'in:cod,vnpay'],
        ]);

        $cart = DB::table('carts')->select(['id'])->where('user_id', $userId)->first();
        if (!$cart) {
            return redirect()->route('store.cart.index');
        }

        DB::beginTransaction();

        try {
            $cartItemIds = array_values(array_unique(array_map('intval', $validated['cart_item_ids'])));
            $items = DB::table('cart_items as ci')
                ->join('products as p', 'p.id', '=', 'ci.product_id')
                ->select(['ci.id', 'ci.product_id', 'ci.quantity', 'ci.price', 'p.name as product_name', 'p.stock'])
                ->whereIn('ci.id', $cartItemIds)
                ->where('ci.cart_id', (int) $cart->id)
                ->get();

            if ($items->isEmpty()) {
                throw new \RuntimeException('Khong co san pham hop le');
            }

            foreach ($items as $item) {
                if ((int) $item->stock < (int) $item->quantity) {
                    throw new \RuntimeException('So luong san pham khong du');
                }
            }

            $totalPrice = $items->sum(fn($i) => ((float) $i->price) * ((int) $i->quantity));

            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'customer_name' => trim($validated['customer_name']),
                'customer_phone' => trim($validated['phone']),
                'customer_address' => trim($validated['address']),
                'total_price' => $totalPrice,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            DB::table('payments')->insert([
                'order_id' => $orderId,
                'method' => $validated['payment_method'],
                'amount' => $totalPrice,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            foreach ($items as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => (int) $item->product_id,
                    'product_name' => (string) $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                ]);

                if ($validated['payment_method'] === 'cod') {
                    DB::table('products')->where('id', (int) $item->product_id)->decrement('stock', (int) $item->quantity);
                }
            }

            DB::table('cart_items')->whereIn('id', $cartItemIds)->delete();

            DB::commit();

            if ($validated['payment_method'] === 'vnpay') {
                return redirect('/payment/vnpay/create?order_id=' . $orderId);
            }

            return redirect('/cart/success.php?order_id=' . $orderId);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function checkoutSuccess(Request $request): View
    {
        $orderId = (int) $request->query('order_id', 0);

        return view('storefront.cart.success', $this->baseViewData($request) + [
            'orderId' => $orderId,
        ]);
    }

    public function ordersIndex(Request $request): View|RedirectResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            return redirect()->route('store.auth.login');
        }

        $orders = DB::table('orders')
            ->select(['id', 'total_price', 'status', 'created_at'])
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get();

        return view('storefront.orders.index', $this->baseViewData($request) + [
            'orders' => $orders,
        ]);
    }

    public function ordersDetail(Request $request): View|RedirectResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            return redirect()->route('store.auth.login');
        }

        $orderId = (int) $request->query('id', 0);
        if ($orderId <= 0) {
            return redirect()->route('store.orders.index');
        }

        $order = DB::table('orders as o')
            ->leftJoin('payments as p', 'p.order_id', '=', 'o.id')
            ->leftJoin('addresses as a', 'a.user_id', '=', 'o.user_id')
            ->select(['o.*', 'p.method as payment_method', 'p.status as payment_status', 'a.city'])
            ->where('o.id', $orderId)
            ->where('o.user_id', $userId)
            ->first();

        if (!$order) {
            return redirect()->route('store.orders.index');
        }

        $items = DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->leftJoin('product_images as pi', function ($join): void {
                $join->on('pi.product_id', '=', 'p.id')->where('pi.is_main', '=', 1);
            })
            ->select(['oi.quantity', 'oi.price', 'p.name', 'p.slug', 'pi.image_url'])
            ->where('oi.order_id', $orderId)
            ->get();

        return view('storefront.orders.detail', $this->baseViewData($request) + [
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function ordersCancel(Request $request): JsonResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Chua dang nhap'], 401);
        }

        $orderId = (int) $request->input('order_id', 0);

        $order = DB::table('orders')
            ->select(['id', 'status'])
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Khong tim thay don hang'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Khong the huy don nay'], 422);
        }

        DB::beginTransaction();

        try {
            $items = DB::table('order_items')->select(['product_id', 'quantity'])->where('order_id', $orderId)->get();
            foreach ($items as $item) {
                DB::table('products')->where('id', (int) $item->product_id)->increment('stock', (int) $item->quantity);
            }

            DB::table('orders')->where('id', $orderId)->update(['status' => 'cancel']);
            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Loi khi huy don'], 500);
        }
    }

    public function showProfile(Request $request): View|RedirectResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            return redirect()->route('store.auth.login');
        }

        $address = DB::table('addresses')->where('user_id', $userId)->first();

        return view('storefront.user.profile', $this->baseViewData($request) + [
            'variant' => $address,
            'error' => '',
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $userId = $this->requireUserId($request);
        if (!$userId) {
            return redirect()->route('store.auth.login');
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'address_detail' => ['required', 'string', 'max:1000'],
        ]);

        $existing = DB::table('addresses')->select(['id'])->where('user_id', $userId)->first();

        if ($existing) {
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

        return redirect('/user/profile.php?success=1');
    }

    private function baseViewData(Request $request): array
    {
        $userId = $this->requireUserId($request);
        $frontUrl = rtrim($request->getSchemeAndHttpHost() . $request->getBasePath(), '/');

        $cartCount = 0;
        if ($userId) {
            $cartCount = (int) DB::table('cart_items as ci')
                ->join('carts as c', 'c.id', '=', 'ci.cart_id')
                ->where('c.user_id', $userId)
                ->sum('ci.quantity');
        }

        return [
            'frontUrl' => $frontUrl,
            'baseUrl' => $frontUrl,
            'siteName' => 'TechShop',
            'sessionUserId' => $userId,
            'sessionUserName' => (string) $request->session()->get('user_name', ''),
            'cartCount' => $cartCount,
        ];
    }

    private function requireUserId(Request $request): int
    {
        $sessionUserId = (int) $request->session()->get('user_id', 0);
        if ($sessionUserId > 0) {
            return $sessionUserId;
        }

        $cookieName = (string) env('TECHSHOP_JWT_COOKIE', 'techshop_token');
        $token = (string) $request->cookie($cookieName, '');
        if ($token === '') {
            return 0;
        }

        $secret = (string) env('TECHSHOP_JWT_SECRET', '');
        $algorithm = (string) env('TECHSHOP_JWT_ALGORITHM', 'HS256');

        if ($secret === '') {
            return 0;
        }

        try {
            $payload = (array) \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($secret, $algorithm));
            $userId = (int) ($payload['user_id'] ?? 0);
            if ($userId > 0) {
                $request->session()->put('user_id', $userId);
                $request->session()->put('user_name', (string) ($payload['user_name'] ?? ''));
            }

            return $userId;
        } catch (\Throwable) {
            return 0;
        }
    }

    private function makeToken(int $userId, string $userName, string $role): string
    {
        $secret = (string) env('TECHSHOP_JWT_SECRET');
        $algorithm = (string) env('TECHSHOP_JWT_ALGORITHM', 'HS256');
        $issuedAt = time();
        $expires = $issuedAt + 86400;

        return JWT::encode([
            'iat' => $issuedAt,
            'exp' => $expires,
            'user_id' => $userId,
            'user_name' => $userName,
            'role' => $role,
        ], $secret, $algorithm);
    }
}
