<?php
session_start();
include "database/database.php";

// Kiểm tra đăng nhập là admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // 👉 Nếu là admin, chuyển đến trang quản trị
        if ($user['role'] === 'admin') {
            header("Location: admin.php");
            exit;
        }

        // 👉 Nếu là người dùng thường, vào trang người dùng
        header("Location: user_dashboard.php");
        exit;
    } else {
        $error = "Thông tin đăng nhập không đúng!";
    }
}

// Truy xuất tất cả sản phẩm
$product_result = $conn->query("SELECT * FROM products");
$products = [];
if ($product_result && $product_result->num_rows > 0) {
  while ($row = $product_result->fetch_assoc()) {
    $products[] = $row;
  }
}
?>

<!-- hiển thị nút vào trang admin nếu là admin -->
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="admin.php" class="btn btn-primary">Vào Trang Quản Trị</a>
<?php endif; ?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>VinFast Car Store</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .card-img-top { height: 220px; object-fit: cover; }
    .min-card-height { min-height: 520px; }
    .navbar { background: linear-gradient(to right, #dff9fb, #f6e58d); }
    body { background-color: #f8f9fa; }
    footer { background-color: #f1f1f1; padding: 20px 0; }
    .pagination .page-link { cursor: pointer; }
  </style>
</head>



<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    
    <!-- Logo và tên thương hiệu -->
    <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="index.php">
      <img src="image/avartar.png" alt="Logo" class="me-2" style="width: 32px; height: 32px;">
      KIÊN Bán Xe Ô TÔ Điện
    </a>

    <!-- Nút toggle trên mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nội dung navbar -->
    <div class="collapse navbar-collapse" id="navbarNav">

      <!-- Menu trái -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" href="index.php">Trang chủ</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#aboutModal">Giới thiệu</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#contactModal">Liên hệ</a>
        </li>
      </ul>

      <!-- Menu phải: Giỏ hàng và người dùng -->
      <div class="d-flex align-items-center">
        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Dropdown người dùng -->
          <div class="dropdown me-3">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=0D8ABC&color=fff&size=36" 
                   alt="Avatar" class="rounded-circle me-2" width="36" height="36">
              <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
              <li><a class="dropdown-item" href="/PHP/WEB/SHOPWEB/authentication/profile.php">👤 Trang cá nhân</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="cart.php">🛒 Giỏ hàng</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/PHP/WEB/SHOPWEB/authentication/logout.php">🚪 Đăng xuất</a></li>
            </ul>
          </div>
        <?php else: ?>
          <!-- Nếu chưa đăng nhập -->
          <a href="/PHP/WEB/SHOPWEB/authentication/register.php" class="btn btn-outline-primary me-2">Đăng ký</a>
          <a href="/PHP/WEB/SHOPWEB/authentication/login.php" class="btn btn-primary">Đăng nhập</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Banner -->
<div class="container-fluid p-0">
  <img src="image/bannerImage.png" alt="Banner" class="img-fluid w-100" />
</div>

<!-- Dịch vụ nổi bật -->
<div class="container my-5">
  <h3 class="mb-4 text-center text-primary">Dịch vụ nổi bật</h3>
  <div id="product-grid" class="row row-cols-1 row-cols-md-3 g-4"></div>
  <nav class="d-flex justify-content-center mt-4">
    <ul id="pagination" class="pagination"></ul>
  </nav>
</div>

<!-- Danh sách ô tô -->
<div class="container mb-5">
  <h2 class="text-center text-success mb-4">📋 Danh sách ô tô còn hàng</h2>
  <div class="table-responsive">
    <table class="table table-hover table-striped table-bordered align-middle">
      <thead class="table-dark text-center">
        <tr><th>ID</th><th>Tiêu đề</th><th>Giá</th><th>Mô tả</th><th>Trạng thái</th></tr>
      </thead>
      <tbody>
        <?php foreach ($products as $product): ?>
          <tr class="text-center">
            <td><?= htmlspecialchars($product['id']) ?></td>
            <td><?= htmlspecialchars($product['title']) ?></td>
            <td><?= number_format($product['price'], 0, ',', '.') ?> USD</td>
            <td><?= htmlspecialchars($product['description']) ?></td>
            <td>
              <span class="badge bg-info"><?= htmlspecialchars($product['status'] ?? 'Chưa xác định') ?></span><br/>
              <small>📞 <?= htmlspecialchars($product['contact'] ?? 'N/A') ?></small><br/>
              <small>🗓️ <?= htmlspecialchars($product['created_at'] ?? '---') ?></small>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Footer -->
<footer class="text-center">
  <div class="container">
    <p class="mb-0">© 2025 VinFast Car Store 🚘 | All rights reserved.</p>
  </div>
</footer>

<!-- Modal About -->
<div class="modal fade" id="aboutModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-primary text-white"><h5 class="modal-title">Giới thiệu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><p>Chào mừng bạn đến với hệ thống bán xe VinFast.</p></div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button></div>
  </div></div>
</div>

<!-- Modal Contact -->
<div class="modal fade" id="contactModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-success text-white"><h5 class="modal-title">Liên hệ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <p>Bạn có thể liên hệ với chúng tôi qua:</p>
      <ul>
        <li>Email: support@example.com</li>
        <li>Điện thoại: 0123 456 789</li>
        <li>Facebook: facebook.com/tenban</li>
      </ul>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button></div>
  </div>
</div>
</div>

<script>
  const products = <?= json_encode($products) ?>;
  const perPage = 6;
  let currentPage = 1;

  function renderProducts(page = 1) {
    const start = (page - 1) * perPage;
    const end = start + perPage;
    const pageItems = products.slice(start, end);
    const container = document.getElementById('product-grid');
    container.innerHTML = pageItems.map(item => `
      <div class="col">
        <div class="card h-100 min-card-height shadow-sm border-0">
          <img src="image/${item.image}" class="card-img-top" alt="${item.title}">
          <div class="card-body">
            <h5 class="card-title">${item.title}</h5>
            <p class="card-text">${item.description}</p>
          </div>
          <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="fw-bold text-danger">${Number(item.price).toLocaleString()} USD</span>
            <div class="d-flex gap-2">
                <a href="order.php?id=${item.id}" class="btn btn-sm btn-outline-success">Thêm vào giỏ hàng</a>
                <button class="btn btn-sm btn-outline-success" onclick="showDetails(${item.id})">Chi tiết</button>
                <a href="order.php?id=${item.id}" class="btn btn-sm btn-success">Buy</a>
            </div>
          </div>
        </div>
      </div>
    `).join('');
  }

  function renderPagination() {
    const totalPages = Math.ceil(products.length / perPage);
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
      const li = document.createElement('li');
      li.className = 'page-item' + (i === currentPage ? ' active' : '');
      li.innerHTML = `<a class="page-link">${i}</a>`;
      li.onclick = () => {
        currentPage = i;
        renderProducts(currentPage);
        renderPagination();
      };
      pagination.appendChild(li);
    }
  }

  // Khởi tạo
  renderProducts();
  renderPagination();
</script>

<!-- Product Detail Modal -->
<div class="modal fade" id="productDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Chi tiết sản phẩm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-5">
            <img id="detail-image" src="" class="img-fluid rounded shadow-sm" alt="Product image">
          </div>
          <div class="col-md-7">
            <h4 id="detail-title" class="text-primary mb-3"></h4>
            <p id="detail-description"></p>
            <ul class="list-group list-group-flush">
              <li class="list-group-item"><strong>Giá:</strong> <span id="detail-price" class="text-danger fw-bold"></span></li>
              <li class="list-group-item"><strong>Trạng thái:</strong> <span id="detail-status" class="badge bg-secondary"></span></li>
              <li class="list-group-item"><strong>Ngày tạo:</strong> <span id="detail-date"></span></li>
              <li class="list-group-item"><strong>Liên hệ:</strong> <span id="detail-contact"></span></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
          <a id="buy-link" href="order.php" class="btn btn-success" role="button">Buy</a>
          <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

</body>

<script>
function showDetails(id) {
  const product = products.find(p => p.id == id);
  if (!product) return;

  // Gán nội dung modal
  document.getElementById('detail-title').textContent = product.title;
  document.getElementById('detail-description').textContent = product.description;
  document.getElementById('detail-price').textContent = Number(product.price).toLocaleString() + " USD";
  document.getElementById('detail-status').textContent = product.status == 0 ? "Còn hàng" : "Hết hàng";
  document.getElementById('detail-contact').textContent = product.contact;
  document.getElementById('detail-date').textContent = product.created_at;
  document.getElementById('detail-image').src = 'image/' + product.image;

  // Gán đúng href cho nút Buy
  document.getElementById('buy-link').href = 'order.php?id=' + product.id;

  // Hiển thị modal
  const modal = new bootstrap.Modal(document.getElementById('productDetailModal'));
  modal.show();
}
</script>

</html>