<?php
session_start();
include("database/database.php");

// Kiểm tra quyền admin
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    echo "<div class='alert alert-danger text-center mt-5'>Bạn không có quyền truy cập trang này!</div>";
    exit();
}

// Thêm hoặc cập nhật người dùng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_user'])) {
    $id = $_POST['user_id'] ?? '';
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $dob = trim($_POST['dob']);
    $description = trim($_POST['description']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if ($id) {
        if (!empty($password)) {
            $sql = "UPDATE users SET username=?, email=?, dob=?, description=?, password=?, phone=?, address=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $username, $email, $dob, $description, $password, $phone, $address, $id);
        } else {
            $sql = "UPDATE users SET username=?, email=?, dob=?, description=?, phone=?, address=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $username, $email, $dob, $description, $phone, $address, $id);
        }
    } else {
        $role = "user";
        $sql = "INSERT INTO users (username, email, password, dob, description, role, phone, address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $username, $email, $password, $dob, $description, $role, $phone, $address);
    }

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center mt-3'>Lỗi: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Xóa người dùng
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Thêm hoặc cập nhật đơn hàng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_order'])) {
    $id = $_POST['order_id'] ?? '';
    $user_id = $_POST['order_user_id'];
    $product_id = $_POST['order_product_id'];
    $quantity = $_POST['order_quantity'];
    $status = $_POST['order_status'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE orders SET user_id=?, product_id=?, quantity=?, status=? WHERE id=?");
        $stmt->bind_param("iiisi", $user_id, $product_id, $quantity, $status, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $product_id, $quantity, $status);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Xóa đơn hàng
if (isset($_GET['delete_order'])) {
    $id = $_GET['delete_order'];
    $stmt = $conn->prepare("DELETE FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Thêm hoặc cập nhật thanh toán
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_payment'])) {
    $id = $_POST['payment_id'] ?? '';
    $order_id = $_POST['payment_order_id'];
    $amount = $_POST['payment_amount'];
    $status = $_POST['payment_status'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE payments SET order_id=?, amount=?, status=? WHERE id=?");
        $stmt->bind_param("sisi", $order_id, $amount, $status, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO payments (order_id, amount, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $order_id, $amount, $status);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Xóa thanh toán
if (isset($_GET['delete_payment'])) {
    $id = $_GET['delete_payment'];
    $stmt = $conn->prepare("DELETE FROM payments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Truy vấn dữ liệu
$result = $conn->query("SELECT * FROM users");
$users = $conn->query("SELECT id, username FROM users");
$products = $conn->query("SELECT id, title FROM products");
$order_result = $conn->query("SELECT o.id, u.id as user_id, u.username, p.id as product_id, p.title AS product, o.quantity, o.status, p.price, o.created_at FROM orders o JOIN users u ON o.user_id = u.id JOIN products p ON o.product_id = p.id");
$payment_result = $conn->query("SELECT pay.id, pay.order_id, pay.amount, pay.status, pay.created_at FROM payments pay");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Quản Trị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-center text-primary">Trang Quản Trị</h2>

    <!-- Nút điều hướng -->
    <div class="btn-group mb-4 d-flex justify-content-center">
        <button onclick="showSection('section-users')" class="btn btn-outline-primary">Quản lý người dùng</button>
        <button onclick="showSection('section-orders')" class="btn btn-outline-success">Quản lý đơn hàng</button>
        <button onclick="showSection('section-payments')" class="btn btn-outline-warning">Quản lý thanh toán</button>
    </div>

    <!-- ========== PHẦN NGƯỜI DÙNG ========== -->
    <div id="section-users" class="section-content">
        <form method="POST" class="row g-3 bg-white p-4 rounded shadow-sm mb-4">
            <input type="hidden" name="user_id" id="user_id">
            <div class="col-md-6"><input type="text" name="username" id="username" class="form-control" placeholder="Tên người dùng" required></div>
            <div class="col-md-6"><input type="email" name="email" id="email" class="form-control" placeholder="Email" required></div>
            <div class="col-md-6"><input type="date" name="dob" id="dob" class="form-control" placeholder="Ngày sinh"></div>
            <div class="col-md-6"><input type="text" name="phone" id="phone" class="form-control" placeholder="Số điện thoại"></div>
            <div class="col-md-6"><input type="text" name="address" id="address" class="form-control" placeholder="Địa chỉ"></div>
            <div class="col-md-6"><input type="text" name="description" id="description" class="form-control" placeholder="Mô tả"></div>
            <div class="col-md-6"><input type="text" name="password" id="password" class="form-control" placeholder="Mật khẩu (bỏ trống nếu không đổi)"></div>
            <div class="col-12 text-end"><button type="submit" name="save_user" class="btn btn-success">Lưu Người Dùng</button></div>
        </form>

        <h4>Danh sách người dùng</h4>
        <table class="table table-striped table-bordered shadow-sm">
            <thead class="table-primary">
                <tr>
                    <th>ID</th><th>Tên</th><th>Email</th><th>Ngày sinh</th><th>SĐT</th><th>Địa chỉ</th><th>Mô tả</th><th>Quyền</th><th>Mật khẩu</th><th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['dob']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['role']) ?></td>
                    <td><?= htmlspecialchars($row['password']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editUser(
                            '<?= $row['id'] ?>',
                            '<?= addslashes($row['username']) ?>',
                            '<?= addslashes($row['email']) ?>',
                            '<?= $row['dob'] ?>',
                            '<?= addslashes($row['description']) ?>',
                            '<?= addslashes($row['phone']) ?>',
                            '<?= addslashes($row['address']) ?>'
                        )">Sửa</button>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary mb-3">← Về trang chính</a>
        <a href="add_products.php" class="btn btn-secondary mb-3">+ Thêm Sản Phẩm</a>
    </div>

    <!-- ========== PHẦN ĐƠN HÀNG ========== -->
    <div id="section-orders" class="section-content" style="display:none;">
    <!-- Form thêm/sửa đơn hàng -->
    <form method="POST" class="row g-3 bg-white p-4 rounded shadow-sm mb-4">
        <input type="hidden" name="order_id" id="order_id">
        <div class="col-md-3">
            <select name="order_user_id" id="order_user_id" class="form-select" required>
                <option value="">-- Chọn người dùng --</option>
                <?php $users->data_seek(0); while ($u = $users->fetch_assoc()): ?>
                    <option value="<?= $u['id'] ?>"><?= $u['username'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="order_product_id" id="order_product_id" class="form-select" required>
                <option value="">-- Chọn sản phẩm --</option>
                <?php $products->data_seek(0); while ($p = $products->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>"><?= $p['title'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="order_quantity" id="order_quantity" class="form-control" placeholder="Số lượng" required>
        </div>
        <div class="col-md-2">
            <select name="order_status" id="order_status" class="form-select">
                <option value="">-- Chọn trạng thái --</option>
                <option value="Đang xử lý">Đang xử lý</option>
                <option value="Đang giao">Đang giao</option>
                <option value="Hoàn tất">Hoàn tất</option>
            </select>
        </div>
        <div class="col-md-2 text-end">
            <button type="submit" name="save_order" class="btn btn-success">Lưu đơn hàng</button>
        </div>
    </form>

    <!-- Bảng danh sách đơn hàng -->
    <h4>Danh sách đơn hàng</h4>
    <table class="table table-bordered table-striped shadow-sm">
        <thead class="table-success">
            <tr>
                <th>ID</th>
                <th>Người dùng</th>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Trạng thái</th>
                <th>Giá</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($order = $order_result->fetch_assoc()): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['username']) ?></td>
                <td><?= htmlspecialchars($order['product']) ?></td>
                <td><?= $order['quantity'] ?></td>
                <td><?= $order['status'] ?></td>
                <td><?= number_format($order['price'], 0, ',', '.') ?> VNĐ</td>
                <td><?= $order['created_at'] ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editOrder(
                        '<?= $order['id'] ?>',
                        '<?= $order['user_id'] ?>',
                        '<?= $order['product_id'] ?>',
                        '<?= $order['quantity'] ?>',
                        '<?= addslashes($order['status']) ?>'
                    )">Sửa</button>
                    <a href="?delete_order=<?= $order['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa đơn hàng này?')">Xóa</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <a href="index.php" class="btn btn-secondary mb-3">← Về trang chính</a>
    <a href="add_products.php" class="btn btn-secondary mb-3">+ Thêm Sản Phẩm</a>
</div>

    <!-- ========== PHẦN THANH TOÁN ========== -->
    <div id="section-payments" class="section-content" style="display:none;">
    <!-- Form thêm/sửa thanh toán -->
    <form method="POST" class="row g-3 bg-white p-4 rounded shadow-sm mb-4">
        <input type="hidden" name="payment_id" id="payment_id">
        <div class="col-md-4">
            <input type="text" name="payment_order_id" id="payment_order_id" class="form-control" placeholder="ID đơn hàng" required>
        </div>
        <div class="col-md-4">
            <input type="number" name="payment_amount" id="payment_amount" class="form-control" placeholder="Số tiền" required>
        </div>
        <div class="col-md-3">
            <select name="payment_status" id="payment_status" class="form-select">
                <option value="">-- Chọn trạng thái --</option>
                <option value="Chờ xử lý">Chờ xử lý</option>
                <option value="Đã thanh toán">Đã thanh toán</option>
                <option value="Đã hủy">Đã hủy</option>
            </select>
        </div>
        <div class="col-md-1 text-end">
            <button type="submit" name="save_payment" class="btn btn-warning">Lưu</button>
        </div>
    </form>

    <!-- Bảng danh sách thanh toán -->
    <h4>Danh sách thanh toán</h4>
    <table class="table table-bordered table-striped shadow-sm">
        <thead class="table-warning">
            <tr>
                <th>ID</th>
                <th>Đơn hàng</th>
                <th>Số tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($pay = $payment_result->fetch_assoc()): ?>
            <tr>
                <td><?= $pay['id'] ?></td>
                <td><?= $pay['order_id'] ?></td>
                <td><?= number_format($pay['amount'], 0, ',', '.') ?> VNĐ</td>
                <td><?= $pay['status'] ?></td>
                <td><?= $pay['created_at'] ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editPayment(
                        '<?= $pay['id'] ?>',
                        '<?= $pay['order_id'] ?>',
                        '<?= $pay['amount'] ?>',
                        '<?= addslashes($pay['status']) ?>'
                    )">Sửa</button>
                    <a href="?delete_payment=<?= $pay['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa thanh toán này?')">Xóa</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <a href="index.php" class="btn btn-secondary mb-3">← Về trang chính</a>
    <a href="add_products.php" class="btn btn-secondary mb-3">+ Thêm Sản Phẩm</a>
</div>

<!-- SCRIPT -->
<script>
function showSection(sectionId) {
    document.querySelectorAll('.section-content').forEach(section => {
        section.style.display = section.id === sectionId ? 'block' : 'none';
    });
}

function editUser(id, username, email, dob, description, phone, address) {
    document.getElementById('user_id').value = id;
    document.getElementById('username').value = username;
    document.getElementById('email').value = email;
    document.getElementById('dob').value = dob;
    document.getElementById('description').value = description;
    document.getElementById('phone').value = phone;
    document.getElementById('address').value = address;
    document.getElementById('password').value = '';
}

function editOrder(id, user_id, product_id, quantity, status) {
    document.getElementById('order_id').value = id;
    document.getElementById('order_user_id').value = user_id;
    document.getElementById('order_product_id').value = product_id;
    document.getElementById('order_quantity').value = quantity;
    document.getElementById('order_status').value = status;
}

function editPayment(id, orderId, amount, status) {
    document.getElementById('payment_id').value = id;
    document.getElementById('payment_order_id').value = orderId;
    document.getElementById('payment_amount').value = amount;
    document.getElementById('payment_status').value = status;
}

</script>
</body>
</html>