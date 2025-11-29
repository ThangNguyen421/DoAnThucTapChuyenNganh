<?php
// Tên file: admin/index.php

// Bước 1: Áp dụng lớp bảo vệ
require_once 'check_admin.php'; 

// Bao gồm các file logic cần thiết (ví dụ: database.php)
// (Lưu ý: Bạn có thể cần điều chỉnh đường dẫn ../../)
require_once __DIR__ . '/../core/database.php';

// Đây là trang Dashboard Admin.
// Biến Session đã có: $_SESSION['user_fullname'], $_SESSION['user_id']
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="products/list.php">Quản lý Sản phẩm</a></li>
                        <li class="nav-item"><a class="nav-link" href="orders/list.php">Quản lý Đơn hàng</a></li>
                        <li class="nav-item"><a class="nav-link" href="reports/sales_report.php">Báo cáo & Thống kê</a></li>
                    </ul>
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Tài khoản</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item"><a class="nav-link" href="#">Xin chào, <?php echo htmlspecialchars($_SESSION['user_fullname']); ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="../core/logout.php">Đăng xuất</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="h2 mt-3">Chào mừng, Quản trị viên!</h1>
                <p>Đây là khu vực quản lý hệ thống bán hàng. Hãy chọn các chức năng từ menu bên trái.</p>

                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <div class="col"><div class="card text-white bg-primary"><div class="card-body"><h5 class="card-title">Tổng Sản phẩm</h5><p class="card-text h3">0</p></div></div></div>
                    <div class="col"><div class="card text-white bg-success"><div class="card-body"><h5 class="card-title">Đơn hàng mới</h5><p class="card-text h3">0</p></div></div></div>
                    <div class="col"><div class="card text-white bg-warning"><div class="card-body"><h5 class="card-title">Doanh thu (Hôm nay)</h5><p class="card-text h3">0 VNĐ</p></div></div></div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>