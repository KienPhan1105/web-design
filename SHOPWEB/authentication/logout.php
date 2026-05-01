<?php
// Bắt đầu phiên làm việc
session_start();

// Hủy toàn bộ biến session
$_SESSION = array();

// Nếu dùng cookie để lưu session, xóa cookie đó luôn
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Chuyển hướng về trang login hoặc trang chính
header("Location: /PHP/WEB/SHOPWEB/index.php");
exit;