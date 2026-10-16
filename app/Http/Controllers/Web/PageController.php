<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    public function login(): View
    {
        return view('pages.auth.login');
    }

    public function register(): View
    {
        return view('pages.auth.register');
    }

    public function profile(): View
    {
        return view('pages.user.profile');
    }

    public function cart(): View
    {
        return view('pages.cart.index');
    }
}
