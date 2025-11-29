<?php
// Tên file: admin/check_admin.php

// 1. Kiểm tra Session đã được khởi động chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----------------------------------------------------
// Bổ sung: Định nghĩa Base URL (Thay thế 'ten_thu_muc_du_an' bằng tên thư mục gốc của bạn)
// Nếu bạn truy cập bằng http://localhost/ĐỒ ÁN THỰC TẬP/
$baseURL = '/ĐỒ ÁN THỰC TẬP'; 
// Nếu bạn truy cập trực tiếp bằng http://localhost/
// $baseURL = ''; 
// ----------------------------------------------------


// 2. Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user_id'])) {
    // Chuyển hướng về trang login sử dụng Base URL
    header('Location: ' . $baseURL . '/views/auth/login.php');
    exit();
}

// 3. Kiểm tra vai trò (Authorization)
if ($_SESSION['user_role'] !== 'admin') {
    // Chuyển hướng về trang chủ khách hàng sử dụng Base URL
    header('Location: ' . $baseURL . '/public/index.php'); 
    exit();
}

// Nếu vượt qua 3 bước trên, người dùng là Admin và có thể tiếp tục
?>