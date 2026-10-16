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
        $reviewId = isset($_GET['review_id']) ? (int)$_GET['review_id'] : 0;
        if ($reviewId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $apiResult = callLaravelApi('GET', '/reviews/' . $reviewId . '/comments', [], $cookieHeader);
        $apiJson = $apiResult['json'] ?? [];
        $apiData = $apiJson['data'] ?? [];

        echo json_encode([
            'success' => (bool)($apiJson['success'] ?? $apiResult['ok']),
            'comments' => $apiData['comments'] ?? [],
            'message' => $apiJson['message'] ?? null,
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = [
            'product_id' => (int)($_POST['product_id'] ?? 0),
            'review_id' => (int)($_POST['review_id'] ?? 0),
            'parent_id' => (int)($_POST['parent_id'] ?? 0),
            'content' => trim($_POST['content'] ?? ''),
        ];

        $apiResult = callLaravelApi('POST', '/comments', $payload, $cookieHeader);
        $apiJson = $apiResult['json'] ?? [];
        $comment = $apiJson['data']['comment'] ?? null;

        echo json_encode([
            'success' => (bool)($apiJson['success'] ?? $apiResult['ok']),
            'message' => $apiJson['message'] ?? ($apiResult['ok'] ? 'Bình luận thành công!' : 'Có lỗi xảy ra, vui lòng thử lại'),
            'comment' => $comment,
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để bình luận']);
        exit;
    }
    $user_id = (int)$_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    if ($product_id <= 0 || $review_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }
    if (empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không được để trống']);
        exit;
    }
    $stmt = $conn->prepare("SELECT id FROM reviews WHERE id = ? AND product_id = ?");
    $stmt->bind_param("ii", $review_id, $product_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'Đánh giá không tồn tại']);
        exit;
    }

    if ($parent_id > 0) {
        $stmt = $conn->prepare("SELECT id FROM comments WHERE id = ? AND review_id = ?");
        $stmt->bind_param("ii", $parent_id, $review_id);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            echo json_encode(['success' => false, 'message' => 'Bình luận cha không tồn tại']);
            exit;
        }
    }
    $parent_id_val = $parent_id > 0 ? $parent_id : null;

    $stmt = $conn->prepare("INSERT INTO comments (user_id, product_id, review_id, parent_id, content, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiis", $user_id, $product_id, $review_id, $parent_id_val, $content);

    if ($stmt->execute()) {
        $comment_id = $stmt->insert_id;
        $stmt = $conn->prepare("
            SELECT c.*, u.full_name AS user_name
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ");
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $comment = $stmt->get_result()->fetch_assoc();

        echo json_encode([
            'success' => true,
            'message' => 'Bình luận thành công!',
            'comment' => [
                'id' => $comment['id'],
                'user_name' => $comment['user_name'],
                'review_id' => (int)$comment['review_id'],
                'parent_id' => $comment['parent_id'],
                'content' => $comment['content'],
                'created_at' => $comment['created_at']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại']);
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');

    $review_id = isset($_GET['review_id']) ? (int)$_GET['review_id'] : 0;
    if ($review_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT c.*, u.full_name AS user_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.review_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
    exit;
}
