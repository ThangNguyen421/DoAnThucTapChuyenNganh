<?php
// Tên file: index.php (Thư mục gốc)

// 1. KẾT NỐI VÀ KHỞI TẠO MODELS
require_once 'core/database.php';
require_once 'models/ProductModel.php';
require_once 'models/CategoryModel.php';
require_once 'core/functions.php'; // Cho hàm định dạng tiền tệ

// Khởi tạo Models
$productModel = new ProductModel($pdo);
$categoryModel = new CategoryModel($pdo);

// Lấy dữ liệu cần thiết cho trang chủ
$allCategories = $categoryModel->getAllCategories();
$latestProducts = $productModel->getProductsForHomepage(8); // Lấy 8 sản phẩm mới nhất

// Hàm format tiền tệ (Đảm bảo tồn tại trong core/functions.php)
if (!function_exists('format_currency')) {
    function format_currency($amount)
    {
        return number_format($amount, 0, ',', '.') . ' đ';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gundam Store - Trang Chủ</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="./core/public/assets/css/style.css">
</head>

<body class="bg-dark text-light">
    <nav class="navbar navbar-dark navbar-expand-lg bg-black py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-warning" href="index.php">GUNDAM STORE</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto gap-3">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Trang Chủ</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Sản Phẩm
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <?php foreach ($allCategories as $cat): ?>
                                <li>
                                    <a class="dropdown-item" href="products.php?cat_id=<?php echo $cat['MaDanhMuc']; ?>">
                                        <?php echo htmlspecialchars($cat['TenDanhMuc']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="products.php">Tất cả</a></li>
                        </ul>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item"><a class="nav-link" href="login.php">Đăng Nhập</a></li>
                    <li class="nav-item"><a class="btn btn-gold btn-sm" href="register.php">Đăng Ký</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php" title="Giỏ hàng">
                            <i class="fa-solid fa-cart-shopping"></i> (0) </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero mb-5">
        <h1>MÔ HÌNH GUNDAM CAO CẤP</h1>
        <p class="mt-3 fs-5">Khám phá bộ sưu tập Gunpla mới nhất với thiết kế đen + vàng ánh kim mạnh mẽ.</p>
        <a href="#new-products" class="btn btn-gold btn-lg mt-4">Xem Sản phẩm mới</a>
    </section>

    <section id="new-products" class="container py-5">
        <h2 class="text-center mb-5 text-warning">SẢN PHẨM MỚI NHẤT</h2>

        <div class="row g-4">
            <?php if (empty($latestProducts)): ?>
                <div class="col-12 text-center text-secondary py-5">
                    Hiện chưa có sản phẩm nào được hiển thị.
                </div>
            <?php else: ?>
                <?php foreach ($latestProducts as $product): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product-card p-3 h-100 rounded">
                            <a href="product_detail.php?id=<?php echo $product['MaSanPham']; ?>">
                                <img src="public<?php echo htmlspecialchars($product['URLAnhChinh'] ?? '/assets/images/placeholder.jpg'); ?>"
                                    class="w-100 rounded mb-3"
                                    alt="<?php echo htmlspecialchars($product['TenSanPham']); ?>"
                                    style="height: 250px; object-fit: cover;" />
                            </a>

                            <h5 class="text-light"><?php echo htmlspecialchars($product['TenSanPham']); ?></h5>
                            <p class="text-warning fs-5 fw-bold mt-auto"><?php echo format_currency($product['GiaBan']); ?></p>

                            <a href="product_detail.php?id=<?php echo $product['MaSanPham']; ?>" class="btn btn-gold w-100">Xem Chi tiết</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="products.php" class="btn btn-lg btn-secondary">Xem Tất cả Sản phẩm</a>
        </div>
    </section>

    <footer class="bg-black py-4 mt-5">
        <div class="container text-center">
            <p class="text-secondary">&copy; 2025 Gundam Store. All rights reserved.</p>
            <p class="text-warning">Liên hệ: support@gundamshop.vn</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
</body>

</html>