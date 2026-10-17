<?php
/** @var array $f */ /** @var array $rows */ /** @var array $pg */ /** @var array $categories */ /** @var array $brands */
/** @var array $shapes */ /** @var array $materials */ /** @var array $colors */ /** @var array|null $activeCat */
$hasFilter = (bool)array_filter([$f['q'], $f['category'], $f['brand'], $f['shape'], $f['material'], $f['color'], $f['min'], $f['max'], $f['stock']]);
$keep = fn(array $drop) => array_diff_key(array_filter($f, fn($v) => $v !== '' && $v !== 0), array_flip($drop));
?>
<div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
  <div>
    <h1 class="h3 mb-1"><?= $activeCat ? e($activeCat['name']) : ($f['q'] !== '' ? 'Kết quả cho “' . e($f['q']) . '”' : 'Tất cả sản phẩm') ?></h1>
    <div class="text-mute small"><?= (int)$pg['total'] ?> sản phẩm</div>
  </div>
  <form method="get" class="d-flex align-items-center gap-2">
    <?php foreach ($keep(['sort', 'page']) as $k => $v): ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"><?php endforeach; ?>
    <label for="sort" class="small text-mute mb-0">Sắp xếp</label>
    <select id="sort" name="sort" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
      <?php foreach (Product::SORTS as $k => $label): ?>
        <option value="<?= $k ?>" <?= $f['sort'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<div class="row g-4">
  <aside class="col-lg-3">
    <form class="filter-box" method="get" action="<?= url('/san-pham') ?>">
      <input type="hidden" name="sort" value="<?= e($f['sort']) ?>">
      <h6>Tìm kiếm</h6>
      <input class="form-control form-control-sm" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Tên, mã, thương hiệu">

      <h6>Danh mục</h6>
      <a class="cat-link <?= !$f['category'] ? 'active' : '' ?>" href="<?= url('/san-pham', $keep(['category', 'page'])) ?>">Tất cả</a>
      <?php foreach ($categories as $c): ?>
        <a class="cat-link <?= $f['category'] === (int)$c['id'] ? 'active' : '' ?>" href="<?= url('/san-pham', $keep(['category', 'page']) + ['category' => $c['id']]) ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
      <input type="hidden" name="category" value="<?= (int)$f['category'] ?: '' ?>">

      <h6>Thương hiệu</h6>
      <select name="brand" class="form-select form-select-sm">
        <option value="">Tất cả</option>
        <?php foreach ($brands as $b): ?><option value="<?= $b['id'] ?>" <?= $f['brand'] === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
      </select>

      <h6>Kiểu dáng</h6>
      <select name="shape" class="form-select form-select-sm">
        <option value="">Tất cả</option>
        <?php foreach ($shapes as $v): ?><option <?= $f['shape'] === $v ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
      </select>

      <h6>Chất liệu</h6>
      <select name="material" class="form-select form-select-sm">
        <option value="">Tất cả</option>
        <?php foreach ($materials as $v): ?><option <?= $f['material'] === $v ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
      </select>

      <h6>Màu</h6>
      <select name="color" class="form-select form-select-sm">
        <option value="">Tất cả</option>
        <?php foreach ($colors as $v): ?><option <?= $f['color'] === $v ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
      </select>

      <h6>Khoảng giá (₫)</h6>
      <div class="d-flex gap-2">
        <input class="form-control form-control-sm" type="number" min="0" step="1000" name="min" value="<?= e($f['min']) ?>" placeholder="Từ">
        <input class="form-control form-control-sm" type="number" min="0" step="1000" name="max" value="<?= e($f['max']) ?>" placeholder="Đến">
      </div>

      <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" name="stock" value="in" id="inStock" <?= $f['stock'] === 'in' ? 'checked' : '' ?>>
        <label class="form-check-label" for="inStock">Chỉ hiện còn hàng</label>
      </div>

      <div class="d-grid gap-2 mt-3">
        <button class="btn btn-primary btn-sm" type="submit">Áp dụng bộ lọc</button>
        <?php if ($hasFilter): ?><a class="btn btn-ghost btn-sm" href="<?= url('/san-pham') ?>">Xóa bộ lọc</a><?php endif; ?>
      </div>
    </form>
  </aside>

  <section class="col-lg-9">
    <?php if (!$rows): ?>
      <div class="lk-card lk-card-pad text-center py-5">
        <h2 class="h5">Không tìm thấy sản phẩm phù hợp</h2>
        <p class="text-ink2">Thử bỏ bớt bộ lọc hoặc tìm bằng từ khóa khác.</p>
        <a class="btn btn-primary" href="<?= url('/san-pham') ?>">Xem tất cả sản phẩm</a>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($rows as $p): ?><div class="col-6 col-lg-6 col-xl-4"><?php require APP_PATH . '/views/partials/product_card.php'; ?></div><?php endforeach; ?>
      </div>
      <?php require APP_PATH . '/views/partials/pagination.php'; ?>
    <?php endif; ?>
  </section>
</div>
