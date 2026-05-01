<?php
session_start();
include "database/database.php";

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /PHP/WEB/SHOPWEB/authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Kiểm tra sản phẩm tồn tại
$product_stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product = $product_stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "Sản phẩm không tồn tại!";
    exit();
}

// Tạo đơn hàng mới
$order_stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, order_date, quantity, status, created_at) VALUES (?, ?, NOW(), 1, 'Đang xử lý', NOW())");
$order_stmt->bind_param("ii", $user_id, $product_id);

if ($order_stmt->execute()) {
    $order_id = $conn->insert_id;

    // Chuyển sang trang thanh toán
    header("Location: pay.php?order_id=$order_id");
    exit();
} else {
    echo "Tạo đơn hàng thất bại!";
}

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $user_id = $_SESSION['user_id']; // nếu bạn lưu user_id khi đăng nhập

    // Lưu vào bảng order hoặc cart
    $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
}

// 👉 Quay về trang index sau khi xử lý
header("Location: index.php");
exit();
?>