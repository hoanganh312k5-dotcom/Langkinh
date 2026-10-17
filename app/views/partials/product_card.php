<?php
/** @var array $p  (hàng sản phẩm có image, brand_name, category_name) */
$low = (int)config('rules.low_stock');
$out = (int)$p['stock'] < 1;
?>
<article class="p-card">
  <?php if ($out): ?>
    <span class="tag-corner pill pill-mute">Hết hàng</span>
  <?php elseif ((int)$p['stock'] <= $low): ?>
    <span class="tag-corner pill pill-warn">Sắp hết</span>
  <?php endif; ?>
  <a class="thumb" href="<?= url('/san-pham/' . $p['id']) ?>" tabindex="-1" aria-hidden="true">
    <img src="<?= e(product_img($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
  </a>
  <div class="body">
    <span class="brand-name"><?= e($p['brand_name']) ?></span>
    <a class="title" href="<?= url('/san-pham/' . $p['id']) ?>"><?= e($p['name']) ?></a>
    <?php if ($p['shape'] || $p['material']): ?>
      <div class="small text-mute mb-2"><?= e(implode(' · ', array_filter([$p['shape'], $p['material'], $p['color']]))) ?></div>
    <?php endif; ?>
    <div class="d-flex align-items-center justify-content-between gap-2 mt-auto flex-wrap">
      <span class="price num"><?= vnd($p['price']) ?></span>
      <?php if (!$out): ?>
        <form action="<?= url('/gio-hang/them') ?>" method="post"><?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="back" value="<?= e(request_path() === '/' ? '/' : request_path() . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">
          <button class="btn btn-sm btn-outline-primary" type="submit" aria-label="Thêm <?= e($p['name']) ?> vào giỏ">Thêm vào giỏ</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</article>
