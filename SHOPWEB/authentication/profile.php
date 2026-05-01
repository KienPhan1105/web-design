<?php
session_start();
require '../database/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /PHP/WEB/SHOPWEB/authentication/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, username, dob, description, role, email, phone, address FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "<div class='alert alert-danger'>Không tìm thấy người dùng!</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #002147, #f37021);
            color: #fff;
        }
        .card {
            background-color: #ffffffdd;
            color: #000;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        .form-label {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <h2 class="mb-4 text-center text-dark">Thông tin cá nhân</h2>
                    <form>
                        <div class="mb-3">
                            <label class="form-label">ID người dùng</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['id']) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['email'] ?? 'Chưa cập nhật') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['address'] ?? 'Chưa cập nhật') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ngày sinh</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['dob'] ?? 'Chưa cập nhật') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giới thiệu</label>
                            <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($user['description'] ?? 'Không có mô tả') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vai trò</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                        </div>
                        <div class="d-grid mt-4">
                            <a href="/PHP/WEB/SHOPWEB/index.php" class="btn btn-btec">← Về trang chính</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>