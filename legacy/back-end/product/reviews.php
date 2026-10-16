<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";
require_once dirname(__DIR__) . "/api_client.php";

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    header('Content-Type: application/json; charset=utf-8');

    $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
    $token = $_COOKIE[$jwtCookie] ?? '';
    $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
            exit;
        }

        $apiResult = callLaravelApi('GET', '/products/' . $productId . '/reviews', [
            'page' => $page,
        ], $cookieHeader);

        if ($apiResult['json']) {
            $data = $apiResult['json']['data'] ?? [];
            echo json_encode([
                'success' => (bool)($apiResult['json']['success'] ?? $apiResult['ok']),
                'reviews' => $data['reviews'] ?? [],
                'total' => (int)($data['pagination']['total'] ?? 0),
                'page' => (int)($data['pagination']['page'] ?? $page),
                'total_pages' => (int)($data['pagination']['total_pages'] ?? 0),
                'message' => $apiResult['json']['message'] ?? null,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể tải đánh giá từ API Laravel',
            ]);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
            exit;
        }

        $apiResult = callLaravelApi('POST', '/products/' . $productId . '/reviews', [
            'rating' => $rating,
            'content' => $content,
        ], $cookieHeader);

        if ($apiResult['json']) {
            $review = $apiResult['json']['data']['review'] ?? null;
            echo json_encode([
                'success' => (bool)($apiResult['json']['success'] ?? $apiResult['ok']),
                'message' => $apiResult['json']['message'] ?? ($apiResult['ok'] ? 'Đánh giá thành công!' : 'Đánh giá thất bại'),
                'review' => $review ? [
                    'id' => $review['id'] ?? null,
                    'product_id' => (int)($review['product_id'] ?? $productId),
                    'user_name' => $review['user_name'] ?? '',
                    'rating' => (int)($review['rating'] ?? $rating),
                    'content' => $review['content'] ?? $content,
                    'created_at' => $review['created_at'] ?? date('Y-m-d H:i:s'),
                ] : null,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể gửi đánh giá lên API Laravel',
            ]);
        }
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá']);
        exit;
    }

    $user_id = (int)$_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
        exit;
    }

    if ($rating < 1) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng chọn số sao đánh giá']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ? AND status = 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này rồi']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, content, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiis", $user_id, $product_id, $rating, $content);

    if ($stmt->execute()) {
        $review_id = $stmt->insert_id;

        $stmt = $conn->prepare("
            SELECT r.*, u.full_name AS user_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $review = $stmt->get_result()->fetch_assoc();

        echo json_encode([
            'success' => true,
            'message' => 'Đánh giá thành công!',
            'review' => [
                'id' => $review['id'],
                'product_id' => (int)$review['product_id'],
                'user_name' => $review['user_name'],
                'rating' => (int)$review['rating'],
                'content' => $review['content'],
                'created_at' => $review['created_at']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại']);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');

    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;

    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
        exit;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM reviews WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare("
        SELECT r.*, u.full_name AS user_name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("iii", $product_id, $limit, $offset);
    $stmt->execute();
    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'total' => (int)$total,
        'page' => $page,
        'total_pages' => ceil($total / $limit)
    ]);
    exit;
}
