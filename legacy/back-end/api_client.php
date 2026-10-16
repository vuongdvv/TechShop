<?php

function callLaravelApi($method, $path, $payload = [], $cookieHeader = '', $useMultipart = false)
{
    if (!defined('LARAVEL_API_BASE')) {
        return ['ok' => false, 'status' => 500, 'json' => null, 'raw' => 'LARAVEL_API_BASE is not defined'];
    }

    $url = rtrim(LARAVEL_API_BASE, '/') . '/' . ltrim($path, '/');
    $method = strtoupper((string)$method);

    $ch = curl_init();

    if ($method === 'GET' && !empty($payload)) {
        $url .= '?' . http_build_query($payload);
    }

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ];

    if ($cookieHeader !== '') {
        $options[CURLOPT_HTTPHEADER][] = 'Cookie: ' . $cookieHeader;
    }

    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        if ($useMultipart) {
            $options[CURLOPT_POSTFIELDS] = $payload;
        } else {
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            $options[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        }
    }

    curl_setopt_array($ch, $options);

    $raw = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'status' => 502, 'json' => null, 'raw' => $error ?: 'Unable to call Laravel API'];
    }

    $json = json_decode($raw, true);

    return [
        'ok' => $status >= 200 && $status < 300,
        'status' => $status,
        'json' => is_array($json) ? $json : null,
        'raw' => $raw,
    ];
}
