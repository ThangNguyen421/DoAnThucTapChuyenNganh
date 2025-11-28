<?php

// Đảm bảo file database.php đã được include ở file gọi (ví dụ: login.php, register.php)
// File này sẽ sử dụng biến $pdo được tạo ra từ database.php
class UserModel {
    private $pdo;

    /**
     * Khởi tạo lớp UserModel với đối tượng kết nối PDO
     * @param PDO $pdo Đối tượng kết nối database
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Đăng ký người dùng mới (Vai trò: 'customer')
     * @param string $fullName Tên đầy đủ
     * @param string $email Email (UNIQUE)
     * @param string $password Mật khẩu thô (sẽ được hash)
     * @return int|bool MaNguoiDung nếu thành công, FALSE nếu thất bại
     */
    public function registerUser($fullName, $email, $password) {
        // Mã hóa mật khẩu trước khi lưu vào DB
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer';
        
        // Chuẩn bị câu lệnh SQL
        $sql = "INSERT INTO NguoiDung (HoTen, Email, MatKhauHash, VaiTro) 
                VALUES (:fullname, :email, :passhash, :role)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Thực thi câu lệnh với các tham số đã bind
            $stmt->execute([
                ':fullname' => $fullName,
                ':email' => $email,
                ':passhash' => $passwordHash,
                ':role' => $role
            ]);

            // Trả về ID của người dùng vừa được tạo
            return $this->pdo->lastInsertId(); 
            
        } catch (\PDOException $e) {
            // Ghi log lỗi (nên dùng error_log trong môi trường production)
            // error_log("Lỗi đăng ký người dùng: " . $e->getMessage());
            
            // Lỗi phổ biến là trùng Email (UNIQUE constraint)
            return false;
        }
    }

    /**
     * Đăng nhập người dùng
     * @param string $email Email người dùng
     * @param string $password Mật khẩu thô
     * @return array|bool Dữ liệu người dùng (không bao gồm hash) nếu thành công, FALSE nếu thất bại
     */
    public function loginUser($email, $password) {
        // 1. Tìm người dùng theo Email
        $sql = "SELECT * FROM NguoiDung WHERE Email = :email";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            // 2. Kiểm tra mật khẩu
            if ($user && password_verify($password, $user['MatKhauHash'])) {
                
                // Cập nhật thời gian đăng nhập cuối
                $this->updateLastLogin($user['MaNguoiDung']);

                // Đăng nhập thành công, loại bỏ mật khẩu hash trước khi trả về
                unset($user['MatKhauHash']);
                return $user;
            }
            return false; // Sai email hoặc mật khẩu
            
        } catch (\PDOException $e) {
            // error_log("Lỗi đăng nhập: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật thời gian đăng nhập cuối
     * @param int $userId Mã người dùng
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE NguoiDung SET LanDangNhapCuoi = NOW() WHERE MaNguoiDung = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $userId]);
        } catch (\PDOException $e) {
            // Ghi log nếu cập nhật không thành công nhưng không ngăn luồng đăng nhập
            // error_log("Lỗi cập nhật LastLogin: " . $e->getMessage());
        }
    }

    /**
     * Lấy thông tin người dùng theo ID
     * @param int $userId Mã người dùng
     * @return array|bool Dữ liệu người dùng hoặc FALSE
     */
    public function getUserById($userId) {
        $sql = "SELECT * FROM NguoiDung WHERE MaNguoiDung = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
            
            if ($user) {
                unset($user['MatKhauHash']);
            }
            return $user;

        } catch (\PDOException $e) {
            return false;
        }
    }

    // Các hàm CRUD khác cho quản trị viên (ví dụ: updateUser, deleteUser, getAllUsers) 
    // sẽ được thêm vào đây sau.
}
?>