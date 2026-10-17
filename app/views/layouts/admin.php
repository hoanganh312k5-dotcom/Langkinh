<?php
/** @var string $content */
/** @var array $flashes */
$path = request_path();
$user = Auth::user();
$pending = (int)Database::value("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$lowCount = Product::lowStockCount();
$menu = [
    ['Tổng quan', [
        ['/admin', 'Bảng điều khiển', 'M3 12 12 4l9 8M5 10v10h5v-6h4v6h5V10', fn($p) => $p === '/admin'],
        ['/admin/thong-ke', 'Thống kê', 'M4 20V10M10 20V4M16 20v-7M22 20H2', fn($p) => str_starts_with($p, '/admin/thong-ke')],
    ]],
    ['Bán hàng', [
        ['/admin/don-hang', 'Đơn hàng', 'M6 3h12l2 4v13H4V7zM4 7h16M9 11h6', fn($p) => str_starts_with($p, '/admin/don-hang'), $pending],
        ['/admin/khach-hang', 'Khách hàng', 'M16 20v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z', fn($p) => str_starts_with($p, '/admin/khach-hang')],
    ]],
    ['Hàng hóa', [
        ['/admin/san-pham', 'Sản phẩm', 'M4 7l8-4 8 4v10l-8 4-8-4zM4 7l8 4 8-4M12 11v10', fn($p) => str_starts_with($p, '/admin/san-pham')],
        ['/admin/ton-kho', 'Tồn kho', 'M3 9l9-6 9 6v11H3zM9 20v-6h6v6', fn($p) => str_starts_with($p, '/admin/ton-kho'), $lowCount],
        ['/admin/danh-muc', 'Danh mục', 'M4 6h16M4 12h16M4 18h10', fn($p) => str_starts_with($p, '/admin/danh-muc')],
        ['/admin/thuong-hieu', 'Thương hiệu', 'M12 3l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.3 6.8 19.1l1-5.8L3.5 9.2l5.9-.9z', fn($p) => str_starts_with($p, '/admin/thuong-hieu')],
    ]],
];
?>
<!doctype html>
<html lang="vi">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
  <meta name="robots" content="noindex">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-side no-print">
    <a class="brand" href="<?= url('/admin') ?>"><?php require APP_PATH . '/views/partials/logo.php'; ?> <?= e(config('shop.name')) ?></a>
    <?php foreach ($menu as [$grp, $items]): ?>
      <div class="grp"><?= e($grp) ?></div>
      <?php foreach ($items as $it): ?>
        <a class="item <?= $it[3]($path) ? 'active' : '' ?>" href="<?= url($it[0]) ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="<?= e($it[2]) ?>"/></svg>
          <?= e($it[1]) ?>
          <?php if (!empty($it[4])): ?><span class="count"><?= (int)$it[4] ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <div class="grp">Khác</div>
    <a class="item" href="<?= url('/') ?>" target="_blank" rel="noopener">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
      Xem cửa hàng
    </a>
  </aside>

  <div class="admin-main">
    <div class="admin-top no-print">
      <span class="text-mute small"><?= e(date('d/m/Y')) ?></span>
      <div class="d-flex align-items-center gap-3">
        <span class="small"><strong><?= e($user['name'] ?? '') ?></strong> <span class="text-mute">· Quản trị</span></span>
        <form action="<?= url('/admin/dang-xuat') ?>" method="post"><?= csrf_field() ?>
          <button class="btn btn-ghost btn-sm" type="submit">Đăng xuất</button>
        </form>
      </div>
    </div>
    <div class="admin-content">
      <?php require APP_PATH . '/views/partials/flash.php'; ?>
      <?= $content ?>
    </div>
  </div>
</div>
<?php require APP_PATH . '/views/partials/scripts.php'; ?>
</body>
</html>
