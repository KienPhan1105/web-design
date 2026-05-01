<?php
require '../database/database.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng ký tài khoản</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h2 class="text-center mb-4">Đăng ký tài khoản</h2>

  <div class="col-md-6 mx-auto shadow p-4 bg-white rounded">
    <form action="register.php" method="post">
      <div class="mb-3">
        <label for="username" class="form-label">Tên đăng nhập</label>
        <input type="text" class="form-control" id="username" name="username" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">Số điện thoại</label>
        <input type="text" class="form-control" id="phone" name="phone">
      </div>

      <div class="mb-3">
        <label for="address" class="form-label">Địa chỉ</label>
        <input type="text" class="form-control" id="address" name="address">
      </div>

      <div class="mb-3">
        <label for="dob" class="form-label">Ngày sinh</label>
        <input type="date" class="form-control" id="dob" name="dob">
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Mật khẩu</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>

      <div class="mb-3">
        <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
      </div>

      <div class="mb-3">
        <label for="role" class="form-label">Vai trò</label>
        <select class="form-select" id="role" name="role" required>
          <option value="user" selected>Người dùng</option>
          <option value="admin">Quản trị viên</option>
        </select>
      </div>

      <button type="submit" class="btn btn-success w-100">Đăng ký</button>
    </form>

    <div class="mt-3 text-center">
      <a href="login.php">Đã có tài khoản? Đăng nhập</a>
    </div>
  </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"]; // không mã hóa
    $confirm = $_POST["confirm_password"];
    $role = $_POST["role"];
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $dob = $_POST["dob"];

    if ($password !== $confirm) {
        echo "<div class='container mt-3 alert alert-danger text-center'>Mật khẩu không khớp.</div>";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            echo "<div class='container mt-3 alert alert-danger text-center'>Tên đăng nhập đã tồn tại.</div>";
        } else {
            $sql = "INSERT INTO users (username, email, password, role, phone, address, dob) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $username, $email, $password, $role, $phone, $address, $dob);
            
            if ($stmt->execute()) {
                echo "<div class='container mt-3 alert alert-success text-center'>Đăng ký thành công!</div>";
            } else {
                echo "<div class='container mt-3 alert alert-danger text-center'>Lỗi đăng ký: " . $stmt->error . "</div>";
            }
        }
    }
}
?>

</body>
</html>