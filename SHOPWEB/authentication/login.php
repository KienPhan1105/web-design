<?php
session_start();
require '../database/database.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="text-center mb-4">Đăng nhập hệ thống</h2>

    <div class="d-flex justify-content-center">
      <form action="login.php" method="post" class="p-4 bg-white border rounded shadow" style="min-width: 300px; max-width: 400px; width: 100%;">
        <div class="mb-3">
          <label for="username" class="form-label">Tên đăng nhập</label>
          <input type="text" class="form-control" name="username" id="username" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Mật khẩu</label>
          <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <div class="mb-3">
          <label for="role" class="form-label">Loại tài khoản</label>
          <select class="form-select" name="role" id="role" required>
            <option value="user">Người dùng</option>
            <option value="admin">Quản trị viên</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
        <div class="mt-3 text-center">
          <a href="register.php">Chưa có tài khoản? Đăng ký</a>
        </div>
      </form>
    </div>
  </div>

<?php
if (
  isset($_POST["username"], $_POST["password"], $_POST["role"]) &&
  $_POST["username"] && $_POST["password"] && $_POST["role"]
) {
    try {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $role = $_POST["role"];

        $sql = "SELECT * FROM users WHERE username = ? AND password = ? AND role = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $password, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($role === 'admin') {
                header("Location: /PHP/WEB/SHOPWEB/admin.php");
                exit;
            } else {
                header("Location: /PHP/WEB/SHOPWEB/index.php");
                exit;
            }
        } else {
            echo "<div class='container mt-3 text-center text-danger'>
            Sai thông tin đăng nhập hoặc vai trò.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='container mt-3 text-danger text-center'>Lỗi: " . $e->getMessage() . "</div>";
    }
}
?>
</body>
</html>