<?php /** @var array $f */ /** @var array $rows */ /** @var array $pg */ /** @var array $categories */ /** @var array $brands */
$low = (int)config('rules.low_stock'); ?>
<div class="page-head">
  <div><h1>Sản phẩm</h1><div class="text-mute small"><?= (int)$pg['total'] ?> sản phẩm</div></div>
  <a class="btn btn-primary" href="<?= url('/admin/san-pham/tao') ?>">+ Thêm sản phẩm</a>
</div>

<form class="lk-card lk-card-pad mb-3 row g-2 align-items-end" method="get">
  <div class="col-md-3"><label class="form-label small mb-1" for="q">Tìm kiếm</label>
    <input class="form-control form-control-sm" id="q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Tên, mã, thương hiệu"></div>
  <div class="col-6 col-md-2"><label class="form-label small mb-1" for="cat">Danh mục</label>
    <select class="form-select form-select-sm" id="cat" name="category"><option value="">Tất cả</option>
      <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $f['category'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-2"><label class="form-label small mb-1" for="br">Thương hiệu</label>
    <select class="form-select form-select-sm" id="br" name="brand"><option value="">Tất cả</option>
      <?php foreach ($brands as $b): ?><option value="<?= $b['id'] ?>" <?= $f['brand'] === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-2"><label class="form-label small mb-1" for="st">Hiển thị</label>
    <select class="form-select form-select-sm" id="st" name="status"><option value="">Tất cả</option>
      <option value="visible" <?= $f['status'] === 'visible' ? 'selected' : '' ?>>Đang bán</option>
      <option value="hidden" <?= $f['status'] === 'hidden' ? 'selected' : '' ?>>Đang ẩn</option></select></div>
  <div class="col-6 col-md-2"><label class="form-label small mb-1" for="sk">Tồn kho</label>
    <select class="form-select form-select-sm" id="sk" name="stock"><option value="">Tất cả</option>
      <option value="low" <?= $f['stock'] === 'low' ? 'selected' : '' ?>>Sắp hết (≤ <?= $low ?>)</option>
      <option value="out" <?= $f['stock'] === 'out' ? 'selected' : '' ?>>Hết hàng</option></select></div>
  <div class="col-md-1 d-grid"><button class="btn btn-primary btn-sm" type="submit">Lọc</button></div>
</form>

<div class="lk-card table-wrap">
  <table class="table table-hover">
    <thead><tr><th>Ảnh</th><th>Sản phẩm</th><th>Danh mục</th><th class="text-end">Giá</th><th class="text-end">Tồn</th><th>Hiển thị</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $p): ?>
      <tr>
        <td><img class="thumb-sm" src="<?= e(product_img($p['image'])) ?>" alt=""></td>
        <td><a class="fw-semibold" href="<?= url('/admin/san-pham/' . $p['id'] . '/sua') ?>"><?= e($p['name']) ?></a>
          <div class="small text-mute"><span class="mono"><?= e($p['code']) ?></span> · <?= e($p['brand_name']) ?></div></td>
        <td><?= e($p['category_name']) ?></td>
        <td class="text-end num"><?= vnd($p['price']) ?></td>
        <td class="text-end num <?= (int)$p['stock'] === 0 ? 'stock-out' : ((int)$p['stock'] <= $low ? 'stock-low' : '') ?>"><?= (int)$p['stock'] ?></td>
        <td><?= $p['status'] === 'visible' ? '<span class="pill pill-ok">Đang bán</span>' : '<span class="pill pill-mute">Ẩn</span>' ?></td>
        <td class="text-end text-nowrap">
          <a class="btn btn-sm btn-ghost" href="<?= url('/admin/san-pham/' . $p['id'] . '/sua') ?>">Sửa</a>
          <form class="d-inline" action="<?= url('/admin/san-pham/' . $p['id'] . '/trang-thai') ?>" method="post"><?= csrf_field() ?>
            <button class="btn btn-sm btn-ghost" type="submit"><?= $p['status'] === 'visible' ? 'Ẩn' : 'Hiện' ?></button></form>
          <form class="d-inline" action="<?= url('/admin/san-pham/' . $p['id'] . '/xoa') ?>" method="post" data-confirm="Xóa sản phẩm «<?= e($p['name']) ?>»? Ảnh của sản phẩm cũng bị xóa."><?= csrf_field() ?>
            <button class="btn btn-sm btn-ghost text-danger" type="submit">Xóa</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-mute py-4">Không có sản phẩm phù hợp.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require APP_PATH . '/views/partials/pagination.php'; ?>
