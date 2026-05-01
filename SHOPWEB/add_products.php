<?php
session_start();
include("database/database.php");

// Chỉ cho phép admin truy cập
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    echo "<div class='alert alert-danger text-center mt-5'>Bạn không có quyền truy cập trang này!</div>";
    exit();
}

// Thêm hoặc cập nhật sản phẩm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_product'])) {
    $id = $_POST['product_id'] ?? '';
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $image = trim($_POST['image']);
    $status = trim($_POST['status']);
    $contact = trim($_POST['contact']);
    $created_at = date("Y-m-d H:i:s");

    if ($id) {
        $stmt = $conn->prepare("UPDATE products SET title=?, description=?, price=?, image=?, status=?, contact=? WHERE id=?");
        $stmt->bind_param("ssisssi", $title, $description, $price, $image, $status, $contact, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO products (title, description, price, image, status, contact, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissss", $title, $description, $price, $image, $status, $contact, $created_at);
    }

    if ($stmt->execute()) {
        header("Location: add_products.php");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center mt-3'>Lỗi: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: add_product.php");
    exit();
}

// Lấy danh sách sản phẩm
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-center text-primary">Quản lý Sản phẩm Ô Tô</h2>

    <form method="POST" class="row g-3 bg-white p-4 rounded shadow-sm">
        <input type="hidden" name="product_id" id="product_id">

        <div class="col-md-6">
            <input type="text" name="title" id="title" class="form-control" placeholder="Tiêu đề" required>
        </div>
        <div class="col-md-6">
            <input type="number" name="price" id="price" class="form-control" placeholder="Giá" required>
        </div>
        <div class="col-md-6">
            <input type="text" name="image" id="image" class="form-control" placeholder="Tên ảnh (vd: vf8.jpg)" required>
        </div>
        <div class="col-md-6">
            <input type="text" name="status" id="status" class="form-control" placeholder="Trạng thái" required>
        </div>
        <div class="col-md-6">
            <input type="text" name="contact" id="contact" class="form-control" placeholder="Liên hệ" required>
        </div>
        <div class="col-md-12">
            <textarea name="description" id="description" rows="3" class="form-control" placeholder="Mô tả" required></textarea>
        </div>
        <div class="col-12 text-end">
            <button type="submit" name="save_product" class="btn btn-success">Lưu Sản phẩm</button>
        </div>
    </form>

    <div class="mt-5">
        <table class="table table-striped table-bordered shadow-sm">
            <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Giá</th>
                <th>Ảnh</th>
                <th>Trạng thái</th>
                <th>Liên hệ</th>
                <th>Hành động</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= number_format($row['price']) ?> đ</td>
                    <td><?= htmlspecialchars($row['image']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['contact']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editProduct(
                            '<?= $row['id'] ?>',
                            '<?= htmlspecialchars($row['title']) ?>',
                            '<?= htmlspecialchars($row['description']) ?>',
                            '<?= $row['price'] ?>',
                            '<?= htmlspecialchars($row['image']) ?>',
                            '<?= htmlspecialchars($row['status']) ?>',
                            '<?= htmlspecialchars($row['contact']) ?>'
                        )">Sửa</button>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa sản phẩm này?')">Xóa</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <a href="admin.php" class="btn btn-secondary mt-3">← Về trang admin</a>
</div>

<script>
function editProduct(id, title, description, price, image, status, contact) {
    document.getElementById('product_id').value = id;
    document.getElementById('title').value = title;
    document.getElementById('description').value = description;
    document.getElementById('price').value = price;
    document.getElementById('image').value = image;
    document.getElementById('status').value = status;
    document.getElementById('contact').value = contact;
}
</script>
</body>
</html>