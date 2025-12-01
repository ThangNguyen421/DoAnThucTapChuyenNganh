<?php
// Tên file: models/CategoryModel.php
class CategoryModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Lấy danh sách tất cả danh mục
     * @return array Danh sách danh mục
     */
    public function getAllCategories() {
        $sql = "SELECT MaDanhMuc, TenDanhMuc FROM DanhMuc ORDER BY TenDanhMuc ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
?>