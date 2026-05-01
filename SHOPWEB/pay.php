<?php
session_start();
include "database/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /PHP/WEB/SHOPWEB/authentication/login.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

$order_stmt = $conn->prepare("
    SELECT 
        o.*, 
        u.username AS user_fullname, u.email, u.phone, u.address,
        p.title AS product_title, p.description AS product_desc, p.price, p.image AS product_image
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    WHERE o.id = ?
");

$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Không tìm thấy đơn hàng!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thanh toán đơn hàng</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --btec-orange: #f37021;
      --btec-orange-dark: #d35400;
    }

    body {
      background-color: #f9f9f9;
      font-family: 'Segoe UI', sans-serif;
    }

    .card {
      border-radius: 16px;
      border: 1px solid #eee;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
      background-color: #ffffff;
    }

    h1, h2, h3, h4 {
      color: var(--btec-orange-dark);
    }

    .btn-success {
      background-color: var(--btec-orange-dark);
      border-color: var(--btec-orange-dark);
    }

    .btn-success:hover {
      background-color: var(--btec-orange);
      border-color: var(--btec-orange);
    }

    .list-group-item {
      border: none;
      padding: 12px 16px;
      background-color: #fefefe;
    }

    .list-group-item strong {
      color: var(--btec-orange-dark);
    }

    .btn-back {
      text-decoration: none;
      color: var(--btec-orange-dark);
      font-weight: 500;
      display: inline-block;
      margin-bottom: 15px;
    }

    .btn-back:hover {
      color: var(--btec-orange);
    }

    .product-img {
      max-width: 100%;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="col-md-10 mx-auto">
      <a href="index.php" class="btn-back">&larr; Quay lại cửa hàng</a>
      <div class="card p-4">
        <h2 class="mb-4 text-center">Thông tin đơn hàng</h2>
        <div class="row">
          <div class="col-md-5">
            <img src="image/<?= htmlspecialchars($order['product_image']) ?>" alt="Sản phẩm" class="product-img mb-3">
          </div>
          <div class="col-md-7">
            <ul class="list-group mb-4">
              <li class="list-group-item"><strong>Người mua:</strong> <?= htmlspecialchars($order['user_fullname']) ?> (<?= htmlspecialchars($order['email']) ?>)</li>
              <li class="list-group-item"><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?></li>
              <li class="list-group-item"><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?></li>
              <li class="list-group-item"><strong>Sản phẩm:</strong> <?= htmlspecialchars($order['product_title']) ?></li>
              <li class="list-group-item"><strong>Mô tả:</strong> <?= htmlspecialchars($order['product_desc']) ?></li>
              <li class="list-group-item"><strong>Giá:</strong> <?= number_format($order['price'], 0, ',', '.') ?> USD</li>
              <li class="list-group-item"><strong>Trạng thái:</strong> <?= htmlspecialchars($order['status']) ?></li>
            </ul>

            <form action="qr.php" method="POST">
              <input type="hidden" name="total" value="<?= $order['price'] ?>">
              <input type="hidden" name="invoice_id" value=""> <!-- không dùng hóa đơn sẵn -->
              <input type="hidden" name="appointment_id" value="0"> <!-- không có lịch hẹn -->
              <input type="hidden" name="order_id_custom" value="<?= $order['id'] ?>"> <!-- custom để xử lý thêm nếu cần -->
              <button type="submit" class="btn btn-success w-100">Thanh toán ngay</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>