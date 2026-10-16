<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LegacyUiController extends Controller
{
    public function render(Request $request, ?string $path = null): Response
    {
        $requested = trim((string) $path, '/');

        // Apache subfolder rewrite can pass `TechShop/...` as route segment.
        if ($requested === 'TechShop') {
            $requested = '';
        } elseif (str_starts_with($requested, 'TechShop/')) {
            $requested = substr($requested, 9);
        }

        // Some rewrites call controller with null route path; recover from URI.
        if ($requested === '' || $requested === '/') {
            $uriPath = parse_url($request->getRequestUri(), PHP_URL_PATH) ?: '';
            $uriPath = ltrim((string) $uriPath, '/');
            if (str_starts_with($uriPath, 'TechShop/')) {
                $uriPath = substr($uriPath, 9);
            }
            if ($uriPath !== '' && $uriPath !== 'public') {
                $requested = $uriPath;
            }
        }

        // Apache rewrite can surface paths like "public" or "public/index.php".
        if ($requested === 'public' || $requested === 'public/index.php' || $requested === 'index.php') {
            $requested = '';
        }

        if (str_starts_with($requested, 'public/')) {
            $requested = substr($requested, 7);
        }

        if ($requested === '') {
            $requested = 'home.php';
        }

        $legacyRoot = base_path('legacy/front-end');
        $targetPath = $legacyRoot . DIRECTORY_SEPARATOR . $requested;
        $target = realpath($targetPath);

        if (!$target && !str_ends_with($requested, '.php')) {
            $target = realpath($targetPath . '.php');
        }
        $rootReal = realpath($legacyRoot);

        if (!$target || !$rootReal || strncmp($target, $rootReal, strlen($rootReal)) !== 0 || !is_file($target)) {
            abort(404);
        }

        ob_start();
        include $target;
        $content = (string) ob_get_clean();

        return response($content);
    }
}
