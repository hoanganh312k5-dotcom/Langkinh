<?php
/** @var array $p */ /** @var array $images */ /** @var array $related */
$out = (int)$p['stock'] < 1;
$low = (int)config('rules.low_stock');
$max = min((int)$p['stock'], (int)config('rules.max_qty_per_line'));
$main = $images[0]['image_path'] ?? null;
$specs = array_filter([
    'Mã sản phẩm' => $p['code'], 'Thương hiệu' => $p['brand_name'], 'Danh mục' => $p['category_name'],
    'Kiểu dáng' => $p['shape'], 'Chất liệu' => $p['material'], 'Màu sắc' => $p['color'],
    'Kích thước / quy cách' => $p['size'],
]);
?>
<nav class="small mb-3" aria-label="breadcrumb">
  <a href="<?= url('/') ?>">Trang chủ</a> <span class="text-mute">/</span>
  <a href="<?= url('/san-pham', ['category' => $p['category_id']]) ?>"><?= e($p['category_name']) ?></a> <span class="text-mute">/</span>
  <span class="text-mute"><?= e($p['name']) ?></span>
</nav>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="gallery-main"><img id="galleryMain" src="<?= e(product_img($main)) ?>" alt="<?= e($p['name']) ?>"></div>
    <?php if (count($images) > 1): ?>
      <div class="gallery-thumbs d-flex gap-2 mt-2 flex-wrap">
        <?php foreach ($images as $i => $img): ?>
          <button type="button" class="<?= $i === 0 ? 'on' : '' ?>" data-src="<?= e(product_img($img['image_path'])) ?>" aria-label="Xem ảnh <?= $i + 1 ?>">
            <img src="<?= e(product_img($img['image_path'])) ?>" alt="">
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-5">
    <div class="eyebrow mb-1"><?= e($p['brand_name']) ?></div>
    <h1 class="h2 mb-2"><?= e($p['name']) ?></h1>
    <div class="price-lg num mb-2"><?= vnd($p['price']) ?></div>

    <div class="mb-3">
      <?php if ($out): ?>
        <span class="pill pill-mute">Hết hàng</span>
      <?php elseif ((int)$p['stock'] <= $low): ?>
        <span class="pill pill-warn">Chỉ còn <?= (int)$p['stock'] ?> sản phẩm</span>
      <?php else: ?>
        <span class="pill pill-ok">Còn hàng</span>
      <?php endif; ?>
    </div>

    <?php if (!$out): ?>
      <form action="<?= url('/gio-hang/them') ?>" method="post" class="mb-4"><?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <input type="hidden" name="back" value="<?= e('/san-pham/' . $p['id']) ?>">
        <label class="form-label" for="qty">Số lượng</label>
        <div class="d-flex gap-2 flex-wrap align-items-center">
          <div class="qty">
            <button type="button" data-step="-1" aria-label="Giảm">−</button>
            <input id="qty" type="number" name="qty" value="1" min="1" max="<?= $max ?>">
            <button type="button" data-step="1" aria-label="Tăng">+</button>
          </div>
          <button class="btn btn-ghost" type="submit">Thêm vào giỏ</button>
          <button class="btn btn-primary" type="submit" name="buy_now" value="1">Mua ngay</button>
        </div>
        <div class="form-text">Tối đa <?= $max ?> sản phẩm cho mỗi lần thêm.</div>
      </form>
    <?php endif; ?>

    <dl class="spec-list lk-card lk-card-pad mb-3">
      <?php foreach ($specs as $k => $v): ?><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd><?php endforeach; ?>
    </dl>

    <?php if ($p['description']): ?>
      <h2 class="h6">Mô tả</h2>
      <p class="text-ink2"><?= nl2br(e($p['description'])) ?></p>
    <?php endif; ?>
  </div>
</div>

<?php if ($related): ?>
  <div class="section-title"><h2>Sản phẩm liên quan</h2></div>
  <div class="row g-3">
    <?php foreach ($related as $p): ?><div class="col-6 col-lg-3"><?php require APP_PATH . '/views/partials/product_card.php'; ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>
