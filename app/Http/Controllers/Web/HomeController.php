<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredProducts = DB::table('products')
            ->select(['id', 'name', 'slug', 'price', 'sale_price'])
            ->where('status', 1)
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('pages.home', [
            'featuredProducts' => $featuredProducts,
        ]);
    }
}
