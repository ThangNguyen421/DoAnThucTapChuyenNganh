<?php
// Tên file: admin/check_admin.php

// 1. Kiểm tra Session đã được khởi động chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user_id'])) {
    // Không đăng nhập: Chuyển hướng về trang login
    header('Location: ../views/auth/login.php');
    exit();
}

// 3. Kiểm tra vai trò (Authorization)
if ($_SESSION['user_role'] !== 'admin') {
    // Không phải Admin: Hiển thị lỗi hoặc chuyển hướng về trang chủ khách hàng
    header('Location: ../public/index.php'); 
    exit();
}

// Nếu vượt qua 3 bước trên, người dùng là Admin và có thể tiếp tục
?>