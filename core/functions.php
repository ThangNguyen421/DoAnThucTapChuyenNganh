<?php
// Thêm vào file core/functions.php (hoặc tạo file nếu chưa có)

/**
 * Xử lý tải lên tệp tin và lưu vào thư mục đích an toàn.
 *
 * @param array $fileInfo Thông tin file từ $_FILES['ten_input_file']
 * @param string $targetDir Đường dẫn tuyệt đối đến thư mục lưu trữ (ví dụ: __DIR__ . '/../public/assets/images/products/')
 * @param array $allowedTypes Mảng các MIME type được phép (ví dụ: ['image/jpeg', 'image/png'])
 * @param int $maxSize Kích thước tối đa cho phép (byte)
 * @return string|bool Tên file mới (ví dụ: 'sanpham_12345.jpg') hoặc FALSE nếu thất bại
 */
function handleFileUpload(array $fileInfo, string $targetDir, array $allowedTypes, int $maxSize = 5000000) {
    if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
        return false; // Lỗi upload cơ bản
    }

    // 1. Kiểm tra kích thước file
    if ($fileInfo['size'] > $maxSize) {
        // error_log("Lỗi: Kích thước file vượt quá giới hạn.");
        return false;
    }

    // 2. Kiểm tra MIME Type thực tế (quan trọng cho bảo mật)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileInfo['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        // error_log("Lỗi: Loại file không được phép.");
        return false;
    }

    // 3. Tạo tên file mới duy nhất
    $extension = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
    $newFileName = uniqid('gundam_') . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $newFileName;

    // 4. Di chuyển file từ thư mục tạm thời đến thư mục đích
    if (!move_uploaded_file($fileInfo['tmp_name'], $targetPath)) {
        // error_log("Lỗi: Không thể di chuyển file.");
        return false;
    }

    // Trả về tên file đã được lưu
    return $newFileName;
}