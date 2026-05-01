<?php
session_start();
include "database/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /PHP/WEB/SHOPWEB/authentication/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Đảm bảo chỉ xóa đơn hàng của đúng user
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
}

// Sau khi xóa, quay về lại giỏ hàng
header("Location: cart.php"); // hoặc tên file hiện tại
exit();