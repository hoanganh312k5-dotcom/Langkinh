<?php
/** @var array $categories */ /** @var array $newest */ /** @var array $best */ /** @var array $catCounts */
$hero = $newest[0] ?? null;
?>
<section class="hero p-4 p-lg-5 mb-4">
  <div class="row align-items-center g-4">
    <div class="col-lg-6">
      <div class="eyebrow mb-2">Kính mắt chính hãng</div>
      <h1 class="mb-3">Chọn một đôi kính hợp với khuôn mặt và thói quen của bạn.</h1>
      <p class="text-ink2 fs-6 mb-4">Gọng kính, kính râm, tròng chống ánh sáng xanh và phụ kiện chăm sóc kính. Đặt hàng trực tuyến, theo dõi đơn từ lúc xác nhận đến khi giao.</p>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-primary btn-lg" href="<?= url('/san-pham') ?>">Xem tất cả sản phẩm</a>
        <a class="btn btn-ghost btn-lg" href="<?= url('/san-pham', ['category' => 2]) ?>">Kính râm</a>
      </div>
    </div>
    <div class="col-lg-6">
      <?php if ($hero): ?>
        <a href="<?= url('/san-pham/' . $hero['id']) ?>"><img class="hero-img" src="<?= e(product_img($hero['image'])) ?>" alt="<?= e($hero['name']) ?>"></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="row g-3">
  <?php foreach ($categories as $c): ?>
    <div class="col-6 col-lg-3">
      <a class="cat-tile" href="<?= url('/san-pham', ['category' => $c['id']]) ?>">
        <div class="name"><?= e($c['name']) ?></div>
        <div class="meta"><?= (int)($catCounts[$c['id']] ?? 0) ?> sản phẩm</div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($best): ?>
<div class="section-title"><h2>Bán chạy</h2><a href="<?= url('/san-pham') ?>">Xem tất cả →</a></div>
<div class="row g-3">
  <?php foreach ($best as $p): ?><div class="col-6 col-lg-3"><?php require APP_PATH . '/views/partials/product_card.php'; ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="section-title"><h2>Sản phẩm mới</h2><a href="<?= url('/san-pham', ['sort' => 'newest']) ?>">Xem thêm →</a></div>
<div class="row g-3">
  <?php foreach ($newest as $p): ?><div class="col-6 col-lg-3"><?php require APP_PATH . '/views/partials/product_card.php'; ?></div><?php endforeach; ?>
</div>

<div class="row g-3 mt-4">
  <?php foreach ([
      ['Đặt hàng dễ dàng', 'Thêm vào giỏ không cần đăng nhập; đăng nhập khi thanh toán và giỏ hàng vẫn được giữ.'],
      ['Hai cách thanh toán', 'Thanh toán khi nhận hàng hoặc chuyển khoản ngân hàng.'],
      ['Theo dõi đơn hàng', 'Xem trạng thái đơn theo từng bước, hủy được khi đơn còn «Chờ xác nhận».'],
  ] as [$h, $t]): ?>
    <div class="col-md-4"><div class="lk-card lk-card-pad h-100"><h3 class="h6"><?= e($h) ?></h3><p class="mb-0 text-ink2 small"><?= e($t) ?></p></div></div>
  <?php endforeach; ?>
</div>
