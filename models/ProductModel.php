<?php
// Tên file: models/ProductModel.php
// Vai trò: Xử lý tất cả các thao tác CRUD liên quan đến Sản phẩm, Danh mục và Ảnh

class ProductModel {
    private $pdo;

    /**
     * Khởi tạo ProductModel với đối tượng kết nối cơ sở dữ liệu PDO.
     * @param PDO $pdo Đối tượng kết nối DB đã được khởi tạo.
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ==========================================================
    //                        PHẦN 1: QUẢN LÝ DANH MỤC
    // ==========================================================
    
    /**
     * Lấy tất cả danh mục (dùng cho dropdown khi thêm/sửa sản phẩm)
     * @return array Danh sách danh mục
     */
    public function getAllCategories() {
        $sql = "SELECT * FROM DanhMuc ORDER BY TenDanhMuc ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Thêm Danh mục mới
     */
    public function addCategory($name, $description = null) {
        $sql = "INSERT INTO DanhMuc (TenDanhMuc, MoTa) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$name, $description]);
    }

    // ==========================================================
    //                        PHẦN 2: QUẢN LÝ SẢN PHẨM (CREATE/READ)
    // ==========================================================
    
    /**
     * Lấy tất cả sản phẩm (dùng cho admin/products/list.php)
     * @return array Danh sách sản phẩm kèm theo tên danh mục
     */
    public function getAllProducts() {
        // Sử dụng JOIN để lấy tên danh mục từ bảng DanhMuc
        $sql = "SELECT p.*, d.TenDanhMuc 
                FROM SanPham p
                JOIN DanhMuc d ON p.MaDanhMuc = d.MaDanhMuc 
                ORDER BY p.NgayTao DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Thêm sản phẩm mới (CREATE) - Xử lý Transaction cho nhiều bảng
     * @param array $data Dữ liệu sản phẩm (không bao gồm ảnh phụ)
     * @param array $extraImageUrls Mảng các URL tương đối của ảnh phụ
     * @return int|bool MaSanPham mới hoặc FALSE nếu thất bại
     */
    public function addProduct($data, $extraImageUrls = []) {
        $this->pdo->beginTransaction(); 
        
        try {
            // 1. CHÈN VÀO BẢNG SanPham
            $sql = "INSERT INTO SanPham (MaDanhMuc, TenSanPham, MoTa, GiaBan, TonKho, URLAnhChinh, TrangThai) 
                    VALUES (:ma_dm, :ten_sp, :mo_ta, :gia_ban, :ton_kho, :url_chinh, :trang_thai)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'ma_dm'     => $data['MaDanhMuc'],
                'ten_sp'    => $data['TenSanPham'],
                'mo_ta'     => $data['MoTa'],
                'gia_ban'   => $data['GiaBan'],
                'ton_kho'   => $data['TonKho'],
                'url_chinh' => $data['URLAnhChinh'], 
                'trang_thai'=> $data['TrangThai'] ?? 'active'
            ]);

            $newProductId = $this->pdo->lastInsertId();

            // 2. CHÈN VÀO BẢNG AnhSanPham (nếu có ảnh phụ)
            if ($newProductId && !empty($extraImageUrls)) {
                $this->addExtraImages($newProductId, $extraImageUrls);
            }
            
            $this->pdo->commit(); 
            return $newProductId;
            
        } catch (\PDOException $e) {
            $this->pdo->rollBack(); 
            // In lỗi ra log để debug
            // error_log("Lỗi thêm sản phẩm: " . $e->getMessage()); 
            return false;
        }
    }

    
    // ==========================================================
    //                        PHẦN 3: QUẢN LÝ ẢNH PHỤ
    // ==========================================================

    /**
     * Thêm các URL ảnh phụ vào bảng AnhSanPham
     * @param int $productId MaSanPham của sản phẩm
     * @param array $urls Mảng các URL tương đối của ảnh
     * @return bool TRUE nếu thành công, FALSE nếu thất bại
     */
    public function addExtraImages(int $productId, array $urls) {
        if (empty($urls)) {
            return true;
        }

        $sqlImages = "INSERT INTO AnhSanPham (MaSanPham, URLAnh) VALUES (?, ?)";
        $stmtImages = $this->pdo->prepare($sqlImages);

        foreach ($urls as $url) {
            if (!$stmtImages->execute([$productId, $url])) {
                return false;
            }
        }
        return true;
    }


/**
     * Xóa sản phẩm theo MaSanPham
     * @param int $productId MaSanPham cần xóa
     * @return array|bool Dữ liệu sản phẩm (để xóa ảnh) hoặc FALSE nếu thất bại
     */
    public function deleteProduct(int $productId) {
        $this->pdo->beginTransaction();
        
        try {
            // 1. Lấy thông tin ảnh chính trước khi xóa (để xóa file)
            $sqlSelect = "SELECT URLAnhChinh FROM SanPham WHERE MaSanPham = ?";
            $stmtSelect = $this->pdo->prepare($sqlSelect);
            $stmtSelect->execute([$productId]);
            $productInfo = $stmtSelect->fetch();

            if (!$productInfo) {
                $this->pdo->rollBack();
                return false; // Không tìm thấy sản phẩm
            }
            
            // 2. Xóa tất cả ảnh phụ liên quan (AnhSanPham)
            $sqlDeleteImages = "DELETE FROM AnhSanPham WHERE MaSanPham = ?";
            $stmtDeleteImages = $this->pdo->prepare($sqlDeleteImages);
            $stmtDeleteImages->execute([$productId]);
            
            // 3. Xóa sản phẩm khỏi bảng SanPham
            $sqlDeleteProduct = "DELETE FROM SanPham WHERE MaSanPham = ?";
            $stmtDeleteProduct = $this->pdo->prepare($sqlDeleteProduct);
            $stmtDeleteProduct->execute([$productId]);
            
            $this->pdo->commit();
            return $productInfo; // Trả về thông tin ảnh để xóa file vật lý
            
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            // error_log("Lỗi xóa sản phẩm: " . $e->getMessage()); 
            return false;
        }
    }

    
    
    // ... Các hàm getProductById, updateProduct, deleteProduct sẽ được thêm sau ...
}
?>