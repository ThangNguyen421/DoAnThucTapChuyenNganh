<?php

session_start();
// Bắt đầu session để lưu trữ thông tin giỏ hàng và các trạng thái người dùng.

// --- 1. CẤU HÌNH DATABASE ---
$db_host = 'localhost';
$db_name = 'gundam_store';
$db_user = 'root';
$db_pass = '';

try {
    // Khởi tạo kết nối PDO (PHP Data Objects) đến database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Báo lỗi chi tiết
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch dữ liệu dưới dạng mảng kết hợp
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage()); // Dừng nếu kết nối lỗi
}

// --- 2. XỬ LÝ GIỎ HÀNG ---
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Khởi tạo giỏ hàng nếu chưa tồn tại
}

if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];

    // Chuẩn bị câu lệnh SQL để kiểm tra tồn kho
    $stmt_stock = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt_stock->execute([$product_id]);
    $stock_check = $stmt_stock->fetch();

    if ($stock_check && $stock_check['stock'] > 0) {
        $current_qty = $_SESSION['cart'][$product_id] ?? 0;
        if ($current_qty + 1 <= $stock_check['stock']) {
            if (!isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] = 1;
            } else {
                $_SESSION['cart'][$product_id]++; // Tăng số lượng trong giỏ
            }
        }
    }
    
    // Chuyển hướng để tránh lỗi gửi lại form khi refresh
    header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit;
}

// --- 3. DỮ LIỆU MENU ĐA CẤP (CHO NAV HEADER) ---
$menu_tree = [
    'Mô hình Gundam' => ['sd' => 'SD Gundam', 'hg' => 'HG Gundam', 'rg' => 'RG Gundam', 'mg' => 'MG Gundam', 'pg' => 'PG Gundam'],
    'Mô hình Figure' => ['onepiece' => 'One Piece', 'dragonball' => 'Dragon Ball', 'naruto' => 'Naruto'],
    'Dụng cụ & Phụ kiện' => ['kimbam' => 'Kìm bấm', 'son' => 'Sơn mô hình', 'base' => 'Action Base'],
    'Game & Console' => ['ps5' => 'PlayStation 5', 'switch' => 'Nintendo Switch', 'xbox' => 'Xbox Series X']
];

// --- 4. LẤY SẢN PHẨM TỪ DATABASE ---
$search = htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES);
$cat_filter = htmlspecialchars($_GET['category'] ?? '', ENT_QUOTES);

$sql = "SELECT * FROM products WHERE name LIKE ?";
$params = ["%$search%"];

if (!empty($cat_filter) && $cat_filter !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $cat_filter;
}
$sql .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

$cart_count = array_sum($_SESSION['cart']);

// --- DỮ LIỆU SẢN PHẨM MẪU (Dùng cho các khối tĩnh và carousel) ---
$carousel_products = [
    ['id' => 101, 'name' => 'MG RX-78-2 GUNDAM VER 3.0', 'category' => 'MG', 'price' => 1200000, 'image' => 'images/rx78.jpg'],
    ['id' => 102, 'name' => 'HG ZAKU II (RED COMET)', 'category' => 'HG', 'price' => 350000, 'image' => 'images/zaku.jpg'],
    ['id' => 103, 'name' => 'RG ASTRAY RED FRAME', 'category' => 'RG', 'price' => 780000, 'image' => 'images/astray.jpg'],
    ['id' => 104, 'name' => 'POP UP PARADE LELOUCH', 'category' => 'FIGURE', 'price' => 850000, 'image' => 'images/lelouch.jpg'],
    ['id' => 105, 'name' => 'SHF LUFFY GEAR 5', 'category' => 'ONEPIECE', 'price' => 1500000, 'image' => 'images/luffy_g5.jpg'],
    ['id' => 106, 'name' => 'HG GUNDAM AERIAL', 'category' => 'HG', 'price' => 500000, 'image' => 'images/aerial.jpg'],
    ['id' => 107, 'name' => 'FIGMA MIKU V4X', 'category' => 'FIGURE', 'price' => 1800000, 'image' => 'images/miku.jpg'],
];

$gundam_samples = [
    ['id' => 901, 'name' => 'MG EXIA IGNITION', 'category' => 'MG', 'price' => 1950000, 'image' => 'images/exia.jpg'],
    ['id' => 902, 'name' => 'HG BARBATOS LUPUS', 'category' => 'HG', 'price' => 380000, 'image' => 'images/barbatos.jpg'],
    ['id' => 903, 'name' => 'RG SAZABI', 'category' => 'RG', 'price' => 1300000, 'image' => 'images/sazabi.jpg'],
    ['id' => 911, 'name' => 'SD UNICORN GUNDAM', 'category' => 'SD', 'price' => 150000, 'image' => 'images/sd_unicorn.jpg'], 
];

$anime_samples = [
    ['id' => 904, 'name' => 'S.H.F SON GOKU', 'category' => 'DRAGONBALL', 'price' => 750000, 'image' => 'images/goku.jpg'],
    ['id' => 905, 'name' => 'MODEL KIT NARUTO', 'category' => 'NARUTO', 'price' => 450000, 'image' => 'images/naruto_kit.jpg'],
    ['id' => 906, 'name' => 'ONE PIECE GRANDLINE', 'category' => 'ONEPIECE', 'price' => 520000, 'image' => 'images/luffy.jpg'],
    ['id' => 907, 'name' => 'FMP MITSURI KANROJI', 'category' => 'DEMON SLAYER', 'price' => 1100000, 'image' => 'images/mitsuri.jpg'],
]; 

$figure_samples = [
    ['id' => 908, 'name' => 'NENDOROID KAGUYA', 'category' => 'FIGURE', 'price' => 890000, 'image' => 'images/kaguya.jpg'],
    ['id' => 909, 'name' => 'Figma Makise Kurisu', 'category' => 'FIGURE', 'price' => 1550000, 'image' => 'images/kurisu.jpg'],
    ['id' => 910, 'name' => 'POP UP PARADE LEVI', 'category' => 'AOT', 'price' => 650000, 'image' => 'images/levi.jpg'],
    ['id' => 912, 'name' => 'FIGMA SAO ASUNA', 'category' => 'FIGURE', 'price' => 1400000, 'image' => 'images/asuna.jpg'], 
];

// --- HTML BẮT ĐẦU TỪ ĐÂY ---
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GUNDAM STORE - Thế Giới Mô Hình</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-top">
            <a href="index.php" class="logo-link">⚔️ GUNDAM STORE</a>
            
            <form class="search-bar" method="GET">
                <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?= $search ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>

            <div class="user-actions">
                <div class="auth-box">
                    <a href="login.php" class="auth-link">Đăng nhập</a>
                    <span class="divider">|</span>
                    <a href="register.php" class="auth-link">Đăng ký</a>
                </div>

                <div class="cart-icon" onclick="location.href='cart.php'">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?= $cart_count ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <nav class="nav">
            <div class="nav-item dropdown-wrapper">
                <a href="?category=all" class="nav-link menu-trigger">
                    <i class="fas fa-bars"></i> DANH MỤC SẢN PHẨM
                </a>
                <ul class="dropdown-menu">
                    <?php foreach ($menu_tree as $parent_name => $sub_items): ?>
                        <li class="dropdown-submenu">
                            <a href="#" class="submenu-toggle">
                                <?= htmlspecialchars($parent_name) ?> <i class="fas fa-chevron-right arrow-right"></i>
                            </a>
                            <ul class="submenu-content">
                                <?php foreach ($sub_items as $key => $label): ?>
                                    <li><a href="?category=<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="nav-links-stretch">
                <a href="index.php" class="nav-link">TRANG CHỦ</a>
                <a href="gioithieu.php" class="nav-link">GIỚI THIỆU</a>
                <a href="tintuc.php" class="nav-link">TIN TỨC</a>
                <a href="khuyenmai.php" class="nav-link">KHUYẾN MÃI</a>
                <a href="lienhe.php" class="nav-link">LIÊN HỆ</a>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <section class="banner">
            <h1>🤖 CHUYÊN MÔ HÌNH GUNDAM CHÍNH HÃNG 🤖</h1>
            <p>Khám phá bộ sưu tập HG, RG, MG, PG đỉnh cao!</p>
        </section>

        <div class="container">
            <h2 class="section-title">
                TẤT CẢ SẢN PHẨM
                <?php if ($search): ?> - Tìm kiếm: "<?= $search ?>" <?php endif; ?>
            </h2>

            <?php 
            // Lấy dữ liệu sản phẩm để hiển thị hoặc dùng mẫu (carousel)
            if (empty($products) && empty($search)) {
                $display_products = $carousel_products;
            } else {
                $display_products = $products;
            }

            if (empty($display_products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h3>Không tìm thấy sản phẩm nào</h3>
                    <p>Vui lòng thử lại với từ khóa khác.</p>
                </div>
            <?php else: ?>
                <div class="products-carousel-wrapper" id="product-carousel-all">
                    
                    <?php if (count($display_products) > 4): ?>
                    <button class="carousel-nav" id="nav-prev"><i class="fas fa-chevron-left"></i></button>
                    <?php endif; ?>

                    <div class="carousel-inner" id="carousel-inner"> 
                        <?php foreach ($display_products as $index => $product): ?>
                            <div class="product-card carousel-item" 
                                 data-index="<?= $index ?>"
                                 onclick="location.href='product.php?id=<?= $product['id'] ?? '' ?>'">
                                <img src="<?= htmlspecialchars($product['image'] ?? '') ?>" 
                                     class="product-image"
                                     onerror="this.src='https://via.placeholder.com/250x250/1a1a1a/ffd700?text=GUNDAM'">
                                <div class="product-info">
                                    <span class="product-category"><?= htmlspecialchars(strtoupper($product['category'] ?? '')) ?></span>
                                    <h3 class="product-name"><?= htmlspecialchars($product['name'] ?? 'Tên sản phẩm') ?></h3>
                                    <div class="product-price"><?= number_format($product['price'] ?? 0, 0, ',', '.') ?>đ</div>
                                    <form method="POST" onclick="event.stopPropagation()">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?? '' ?>">
                                        <button type="submit" name="add_to_cart" class="btn">
                                            <i class="fas fa-cart-plus"></i> THÊM
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($display_products) > 4): ?>
                    <button class="carousel-nav" id="nav-next"><i class="fas fa-chevron-right"></i></button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="view-more-container">
                <a href="#" class="view-more-btn">XEM THÊM TẤT CẢ</a>
            </div>


            <h2 class="section-title" style="margin-top: 50px;">
                GUNDAM
            </h2>
            <div class="products-grid">
                <?php foreach ($gundam_samples as $product): ?>
                    <div class="product-card" onclick="location.href='product.php?id=<?= $product['id'] ?>'">
                        <img src="<?= htmlspecialchars($product['image'] ?? '') ?>" 
                             class="product-image"
                             onerror="this.src='https://via.placeholder.com/250x250/1a1a1a/ffd700?text=GUNDAM'">
                        <div class="product-info">
                            <span class="product-category"><?= htmlspecialchars(strtoupper($product['category'])) ?></span>
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                            <form method="POST" onclick="event.stopPropagation()">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" name="add_to_cart" class="btn">
                                    <i class="fas fa-cart-plus"></i> XEM
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="view-more-container">
                <a href="?category=hg" class="view-more-btn">XEM THÊM GUNDAM</a>
            </div>
            
            <h2 class="section-title" style="margin-top: 50px;">
                MÔ HÌNH ANIME
            </h2>
            <div class="products-grid">
                <?php foreach ($anime_samples as $product): ?>
                    <div class="product-card" onclick="location.href='product.php?id=<?= $product['id'] ?>'">
                        <img src="<?= htmlspecialchars($product['image'] ?? '') ?>" 
                             class="product-image"
                             onerror="this.src='https://via.placeholder.com/250x250/1a1a1a/ffd700?text=ANIME'">
                        <div class="product-info">
                            <span class="product-category"><?= htmlspecialchars(strtoupper($product['category'])) ?></span>
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                            <form method="POST" onclick="event.stopPropagation()">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" name="add_to_cart" class="btn">
                                    <i class="fas fa-cart-plus"></i> XEM
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="view-more-container">
                <a href="?category=onepiece" class="view-more-btn">XEM THÊM MÔ HÌNH ANIME</a>
            </div>
            
            <h2 class="section-title" style="margin-top: 50px;">
                FIGURE
            </h2>
            <div class="products-grid">
                <?php foreach ($figure_samples as $product): ?>
                    <div class="product-card" onclick="location.href='product.php?id=<?= $product['id'] ?>'">
                        <img src="<?= htmlspecialchars($product['image'] ?? '') ?>" 
                             class="product-image"
                             onerror="this.src='https://via.placeholder.com/250x250/1a1a1a/ffd700?text=FIGURE'">
                        <div class="product-info">
                            <span class="product-category"><?= htmlspecialchars(strtoupper($product['category'])) ?></span>
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                            <form method="POST" onclick="event.stopPropagation()">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" name="add_to_cart" class="btn">
                                    <i class="fas fa-cart-plus"></i> XEM
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="view-more-container">
                <a href="?category=figure" class="view-more-btn">XEM THÊM FIGURE</a>
            </div>
            
        </div>
    </main>

    <footer class="footer">
        <div class="footer-main">
            <div class="footer-column">
                <h3>VỀ CHÚNG TÔI</h3>
                <p>Gundam Store chuyên cung cấp các mô hình Gundam chính hãng từ Nhật Bản, bao gồm các dòng HG, RG, MG, PG và nhiều hơn nữa. Cam kết chất lượng và giá tốt nhất.</p>
                <div class="social-icons" style="margin-top: 15px;">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="footer-column contact-info">
                <h3>LIÊN HỆ</h3>
                <p><i class="fas fa-phone-alt"></i> Hotline: 0123 456 789</p>
                <p><i class="fas fa-envelope"></i> Email: support@gundamstore.vn</p>
                <p><i class="fas fa-map-marker-alt"></i> TP. Hồ Chí Minh, Việt Nam</p>
            </div>

            <div class="footer-column">
                <h3>CHÍNH SÁCH</h3>
                <a href="#">Chính sách đổi trả</a>
                <a href="#">Chính sách bảo hành</a>
                <a href="#">Hướng dẫn thanh toán</a>
                <a href="#">Vận chuyển & Giao nhận</a>
            </div>
        </div>

        <div class="footer-bottom">
            <p>
                © 2024 GUNDAM STORE. All Rights Reserved. Powered by PHP ⚔️
            </p>
        </div>
    </footer>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('carousel-inner'); 
        const btnPrev = document.getElementById('nav-prev');
        const btnNext = document.getElementById('nav-next');
        
        // Kiểm tra cơ bản
        if (!container || !btnPrev || !btnNext) return; 

        const items = container.querySelectorAll('.product-card'); 
        const totalItems = items.length;
        const displayCount = 4;
        const maxIndex = totalItems - displayCount;
        
        // Thoát nếu không đủ sản phẩm để cuộn
        if (totalItems <= displayCount) {
            btnPrev.style.display = 'none';
            btnNext.style.display = 'none';
            return; 
        }

        let currentIndex = 0; 
        let isSliding = false;
        
        const itemWidthPercentage = 100 / displayCount; 
        const transitionDuration = 800; // 0.8 giây

        function updateButtonState() {
            btnPrev.disabled = (currentIndex === 0);
            btnNext.disabled = (currentIndex >= maxIndex);
            
            // Dù nút disabled, ta vẫn muốn chúng có vẻ "luôn có sẵn" (theo yêu cầu trước)
            // NHƯNG để logic đơn giản, ta giữ nguyên disabled.
        }

        function slide(direction) {
            if (isSliding) return;
            
            let newIndex = currentIndex + direction;

            // Kiểm tra giới hạn: Dừng ở đầu và cuối
            if (newIndex < 0 || newIndex > maxIndex) {
                return;
            }
            
            isSliding = true;
            currentIndex = newIndex;

            const offset = -currentIndex * itemWidthPercentage;
            container.style.transform = `translateX(${offset}%)`;

            // Mở khóa tương tác sau khi slide xong
            setTimeout(() => {
                isSliding = false;
                updateButtonState();
            }, transitionDuration);
        }
        
        // Gán sự kiện cho các nút
        btnNext.addEventListener('click', () => slide(1));
        btnPrev.addEventListener('click', () => slide(-1));

        // Thiết lập Transition CSS ban đầu
        container.style.transition = `transform ${transitionDuration}ms ease-in-out`;
        
        // Khởi tạo trạng thái nút ban đầu
        updateButtonState();
    });
</script>
</body>
</html>