<?php
session_start();
include "database/database.php";

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /PHP/WEB/SHOPWEB/authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin đơn hàng theo user_id
$query = "
    SELECT o.*, p.title AS product_title, p.description AS product_desc, 
           p.price, p.image AS product_image
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die("Lỗi truy vấn: " . $stmt->error);
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Giỏ hàng</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --btec-orange: #f37021;
      --btec-orange-dark: #d35400;
    }

    body {
      background-color: #f7f7f7;
      font-family: Arial, sans-serif;
    }

    .card {
      border-radius: 12px;
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .product-img {
      max-width: 100%;
      border-radius: 8px;
    }

    .btn-pay {
      background-color: var(--btec-orange-dark);
      color: white;
    }

    .btn-pay:hover {
      background-color: var(--btec-orange);
    }

    h2 {
      color: var(--btec-orange-dark);
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <h2 class="mb-4 text-center">Giỏ hàng của bạn</h2>

    <?php if (isset($result) && $result->num_rows > 0): ?>
      <?php while ($order = $result->fetch_assoc()): ?>
        <div class="card p-4">
          <div class="row">
            <div class="col-md-4">
              <img src="image/<?= htmlspecialchars($order['product_image']) ?>" class="product-img" alt="Ảnh sản phẩm">
            </div>
            <div class="col-md-8">
              <h5><?= htmlspecialchars($order['product_title']) ?></h5>
              <p><?= htmlspecialchars($order['product_desc']) ?></p>
              <p><strong>Giá:</strong> <?= number_format($order['price'], 0, ',', '.') ?> USD</p>
              <p><strong>Trạng thái:</strong> <?= htmlspecialchars($order['status']) ?></p>
              <form method="POST" action="qr.php">
                <input type="hidden" name="total" value="<?= $order['price'] ?>">
                <input type="hidden" name="order_id_custom" value="<?= $order['id'] ?>">
                <button type="submit" class="btn btn-pay mt-2">Thanh toán đơn hàng này</button>
              </form>
              <a href="delete_order.php?id=<?= $order['id'] ?>" 
                 class="btn btn-danger mt-2"
                 onclick="return confirm('Bạn có chắc muốn xoá đơn hàng này?');">
                 Xóa khỏi giỏ hàng
              </a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center">Bạn chưa có đơn hàng nào trong giỏ.</p>
    <?php endif; ?>

    <div class="text-center mt-4">
      <a href="index.php" class="btn btn-secondary">← Về trang chính</a>
    </div>
  </div>
</body>
</html>