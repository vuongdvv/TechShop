<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'TechShop' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f7f8fa;
            color: #222;
        }

        .topbar {
            background: #111827;
            color: #fff;
            padding: 14px 20px;
        }

        .topbar a {
            color: #fff;
            text-decoration: none;
            margin-right: 16px;
        }

        .container {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 14px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .08);
        }

        .muted {
            color: #6b7280;
        }

        .btn {
            display: inline-block;
            background: #0f766e;
            color: #fff;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            border: 0;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 12px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <header class="topbar">
        <a href="{{ route('home') }}">TechShop Laravel</a>
        <a href="{{ route('auth.login') }}">Dang nhap</a>
        <a href="{{ route('auth.register') }}">Dang ky</a>
        <a href="{{ route('cart.index') }}">Gio hang</a>
        <a href="{{ route('user.profile') }}">Ho so</a>
    </header>
    <main class="container">
        @yield('content')
    </main>
</body>

</html>