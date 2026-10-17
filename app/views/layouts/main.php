<?php
/** @var string $content */
/** @var array $flashes */
$path = request_path();
$user = Auth::user();
$cartCount = Cart::count();
$nav = [['/', 'Trang chủ']];
foreach (Category::visible() as $c) {
    $nav[] = ['/san-pham?category=' . $c['id'], $c['name'], (int)$c['id']];
}
$curCat = input_int('category');
?>
<!doctype html>
<html lang="vi">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body>
<div class="topbar d-none d-md-block">
  <div class="container d-flex justify-content-between">
    <span><?= e(config('shop.address')) ?></span>
    <span>Hotline <a href="tel:<?= e(preg_replace('/\s/', '', config('shop.phone'))) ?>"><strong><?= e(config('shop.phone')) ?></strong></a></span>
  </div>
</div>

<header class="site-header">
  <div class="container py-2 d-flex align-items-center gap-2 gap-lg-3 flex-wrap flex-xl-nowrap">
    <a class="brand" href="<?= url('/') ?>"><?php require APP_PATH . '/views/partials/logo.php'; ?> <?= e(config('shop.name')) ?></a>

    <nav class="nav-main d-none d-lg-flex ms-2" aria-label="Danh mục">
      <?php foreach ($nav as $n):
          $active = isset($n[2]) ? ($path === '/san-pham' && $curCat === $n[2]) : ($path === '/'); ?>
        <a href="<?= url($n[0]) ?>" class="<?= $active ? 'active' : '' ?>"><?= e($n[1]) ?></a>
      <?php endforeach; ?>
    </nav>

    <form class="search-box ms-auto" action="<?= url('/san-pham') ?>" method="get" role="search">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
      <input class="form-control" type="search" name="q" value="<?= e(input('q')) ?>" placeholder="Tìm gọng, kính râm, tròng…" aria-label="Tìm kiếm">
    </form>

    <a class="cart-link" href="<?= url('/gio-hang') ?>" aria-label="Giỏ hàng">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.6 8.4a2 2 0 0 1-2 1.6H9a2 2 0 0 1-2-1.6L5 3H2"/><circle cx="9" cy="20" r="1.2"/><circle cx="18" cy="20" r="1.2"/></svg>
      <?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
    </a>

    <?php if ($user): ?>
      <div class="dropdown">
        <button class="btn btn-ghost btn-sm dropdown-toggle" data-bs-toggle="dropdown" type="button"><?= e(mb_substr(trim(strrchr(' ' . $user['name'], ' ')), 0, 18)) ?></button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php if ($user['role'] === 'admin'): ?>
            <li><a class="dropdown-item" href="<?= url('/admin') ?>">Trang quản trị</a></li>
          <?php else: ?>
            <li><a class="dropdown-item" href="<?= url('/tai-khoan') ?>">Thông tin cá nhân</a></li>
            <li><a class="dropdown-item" href="<?= url('/don-hang') ?>">Đơn hàng của tôi</a></li>
          <?php endif; ?>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form action="<?= url('/dang-xuat') ?>" method="post"><?= csrf_field() ?>
              <button class="dropdown-item" type="submit">Đăng xuất</button>
            </form>
          </li>
        </ul>
      </div>
    <?php else: ?>
      <a class="btn btn-ghost btn-sm" href="<?= url('/dang-nhap') ?>">Đăng nhập</a>
      <a class="btn btn-primary btn-sm d-none d-sm-inline-block" href="<?= url('/dang-ky') ?>">Đăng ký</a>
    <?php endif; ?>
  </div>
  <div class="container d-lg-none pb-2 nav-main" style="overflow-x:auto;white-space:nowrap">
    <?php foreach ($nav as $n): ?><a href="<?= url($n[0]) ?>"><?= e($n[1]) ?></a><?php endforeach; ?>
  </div>
</header>

<main class="container py-4">
  <?php require APP_PATH . '/views/partials/flash.php'; ?>
  <?= $content ?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-5">
        <div class="brand text-white mb-2"><?php require APP_PATH . '/views/partials/logo.php'; ?> <?= e(config('shop.name')) ?></div>
        <p class="mb-0">Cửa hàng kính mắt trực tuyến: gọng, kính râm, tròng kính và phụ kiện chăm sóc kính.</p>
      </div>
      <div class="col-6 col-md-3">
        <h6>Mua sắm</h6>
        <ul class="list-unstyled mb-0">
          <?php foreach (Category::visible() as $c): ?>
            <li><a href="<?= url('/san-pham', ['category' => $c['id']]) ?>"><?= e($c['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-6 col-md-4">
        <h6>Liên hệ</h6>
        <div><?= e(config('shop.address')) ?></div>
        <div>Điện thoại: <?= e(config('shop.phone')) ?></div>
        <div>Email: <?= e(config('shop.email')) ?></div>
      </div>
    </div>
    <hr style="border-color:rgba(255,255,255,.12)">
    <div class="d-flex justify-content-between flex-wrap gap-2 small">
      <span>© <?= date('Y') ?> <?= e(config('shop.name')) ?>. Đồ án môn học – website thương mại điện tử kính mắt.</span>
      <a href="<?= url('/admin/dang-nhap') ?>">Quản trị</a>
    </div>
  </div>
</footer>
<?php require APP_PATH . '/views/partials/scripts.php'; ?>
</body>
</html>
