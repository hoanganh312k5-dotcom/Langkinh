<?php /** @var array $f */ /** @var array $rows */ /** @var array $pg */ /** @var array $categories */ /** @var int $low */
$ret = http_build_query(query_without([]));
?>
<div class="page-head"><div><h1>Tồn kho</h1><div class="text-mute small">Cảnh báo sắp hết khi tồn ≤ <?= $low ?></div></div></div>

<form class="lk-card lk-card-pad mb-3 d-flex flex-wrap gap-2 align-items-end" method="get">
  <div><label class="form-label small mb-1" for="q">Tìm kiếm</label><input class="form-control form-control-sm" id="q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Tên hoặc mã"></div>
  <div><label class="form-label small mb-1" for="cat">Danh mục</label>
    <select class="form-select form-select-sm" id="cat" name="category"><option value="">Tất cả</option>
      <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $f['category'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
  <div><label class="form-label small mb-1" for="sk">Tình trạng</label>
    <select class="form-select form-select-sm" id="sk" name="stock">
      <option value="">Tất cả</option>
      <option value="low" <?= $f['stock'] === 'low' ? 'selected' : '' ?>>Sắp hết</option>
      <option value="out" <?= $f['stock'] === 'out' ? 'selected' : '' ?>>Hết hàng</option>
      <option value="in" <?= $f['stock'] === 'in' ? 'selected' : '' ?>>Còn hàng</option></select></div>
  <button class="btn btn-primary btn-sm" type="submit">Lọc</button>
</form>

<div class="lk-card table-wrap">
  <table class="table">
    <thead><tr><th>Sản phẩm</th><th class="text-end">Tồn hiện tại</th><th>Tình trạng</th><th>Cập nhật</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $p): $s = (int)$p['stock']; ?>
      <tr>
        <td><div class="d-flex gap-3 align-items-center"><img class="thumb-sm" src="<?= e(product_img($p['image'])) ?>" alt="">
          <div><a class="fw-semibold" href="<?= url('/admin/san-pham/' . $p['id'] . '/sua') ?>"><?= e($p['name']) ?></a>
          <div class="small mono text-mute"><?= e($p['code']) ?></div></div></div></td>
        <td class="text-end num fs-5 <?= $s === 0 ? 'stock-out' : ($s <= $low ? 'stock-low' : '') ?>"><?= $s ?></td>
        <td><?= $s === 0 ? '<span class="pill pill-bad">Hết hàng</span>' : ($s <= $low ? '<span class="pill pill-warn">Sắp hết</span>' : '<span class="pill pill-ok">Ổn định</span>') ?></td>
        <td>
          <form class="d-flex gap-1 flex-wrap" action="<?= url('/admin/ton-kho/' . $p['id']) ?>" method="post"><?= csrf_field() ?>
            <input type="hidden" name="return" value="<?= e($ret) ?>">
            <input class="form-control form-control-sm" style="width:90px" type="number" name="amount" required placeholder="Số lượng" aria-label="Số lượng cho <?= e($p['name']) ?>">
            <button class="btn btn-sm btn-primary" type="submit" name="mode" value="set" title="Đặt tồn kho bằng đúng số này">Đặt bằng</button>
            <button class="btn btn-sm btn-ghost" type="submit" name="mode" value="add" title="Cộng thêm (số âm để trừ bớt)">Nhập thêm</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="4" class="text-center text-mute py-4">Không có sản phẩm phù hợp.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require APP_PATH . '/views/partials/pagination.php'; ?>
