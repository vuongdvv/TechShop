<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteName)</title>
    @stack('styles')
</head>

<body>
    @include('storefront.partials.header')
    @yield('content')
    @include('storefront.partials.footer')
    @stack('scripts')
</body>

</html>