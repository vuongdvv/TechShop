<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

define("JWT_SECRET", "40e1d9cdef7e6fce7d287969ce2a1ef07c39558661c74b90171bcecfed6c8b1c");
define("JWT_ALGORITHM", "HS256");
define("JWT_EXPIRATION", 86400); // 24 hours
define("JWT_COOKIE_NAME", "techshop_token");


function generateJWT($userId, $userName, $role)
{
    $issuedAt = time();
    $payload = [
        'iat'       => $issuedAt,
        'exp'       => $issuedAt + JWT_EXPIRATION,
        'user_id'   => $userId,
        'user_name' => $userName,
        'role'      => $role,
    ];
    return JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);
}


function verifyJWT($token)
{
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGORITHM));
        return (array) $decoded;
    } catch (ExpiredException $e) {
        return null;
    } catch (\Exception $e) {
        return null;
    }
}


function setJWTCookie($token)
{
    setcookie(JWT_COOKIE_NAME, $token, [
        'expires'  => time() + JWT_EXPIRATION,
        'path'     => '/',
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);
}

function clearJWTCookie()
{
    setcookie(JWT_COOKIE_NAME, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);
}


function getJWTPayload()
{
    $token = $_COOKIE[JWT_COOKIE_NAME] ?? null;
    if (!$token) {
        return null;
    }
    return verifyJWT($token);
}
