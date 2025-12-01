<?php
// Tên file: models/OrderModel.php
class OrderModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Lấy danh sách tất cả đơn hàng (cho trang admin/orders/list.php)
     * @return array Danh sách đơn hàng
     */
    public function getAllOrders() {
        $sql = "SELECT dh.*, nd.HoTen AS TenNguoiDat 
                FROM DonHang dh
                JOIN NguoiDung nd ON dh.MaNguoiDung = nd.MaNguoiDung
                ORDER BY dh.NgayTao DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Lấy chi tiết một đơn hàng (cho trang admin/orders/detail.php)
     * @param int $orderId MaDonHang
     * @return array|false Thông tin đơn hàng và chi tiết sản phẩm
     */
    public function getOrderDetails(int $orderId) {
        // 1. Lấy thông tin chung của đơn hàng
        $sqlOrder = "SELECT dh.*, nd.HoTen AS TenNguoiDat, nd.Email
                     FROM DonHang dh
                     JOIN NguoiDung nd ON dh.MaNguoiDung = nd.MaNguoiDung
                     WHERE dh.MaDonHang = ?";
        $stmtOrder = $this->pdo->prepare($sqlOrder);
        $stmtOrder->execute([$orderId]);
        $order = $stmtOrder->fetch();

        if (!$order) {
            return false;
        }

        // 2. Lấy danh sách sản phẩm trong đơn hàng
        $sqlItems = "SELECT ctdh.*, sp.TenSanPham, sp.URLAnhChinh 
                     FROM ChiTietDonHang ctdh
                     JOIN SanPham sp ON ctdh.MaSanPham = sp.MaSanPham
                     WHERE ctdh.MaDonHang = ?";
        $stmtItems = $this->pdo->prepare($sqlItems);
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll();

        $order['items'] = $items;
        return $order;
    }

    /**
     * Cập nhật trạng thái đơn hàng
     * @param int $orderId MaDonHang
     * @param string $newStatus Trạng thái mới (ví dụ: 'processing', 'shipped', 'completed', 'cancelled')
     * @return bool
     */
    public function updateOrderStatus(int $orderId, string $newStatus) {
        $sql = "UPDATE DonHang SET TrangThai = ? WHERE MaDonHang = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$newStatus, $orderId]);
    }
}