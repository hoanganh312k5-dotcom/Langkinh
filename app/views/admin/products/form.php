<?php
/** @var array|null $p */ /** @var array $images */ /** @var array $categories */ /** @var array $brands */ /** @var bool $inOrders */
$id = $p['id'] ?? 0;
$v = fn(string $k, $d = '') => old($k, $p[$k] ?? $d);
$opt = fn(string $name, array $list, $cur) => implode('', array_map(
    fn($x) => '<option ' . ($cur === $x ? 'selected' : '') . '>' . e($x) . '</option>', $list));
?>
<div class="page-head">
  <h1><?= $p ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h1>
  <a class="btn btn-ghost btn-sm" href="<?= url('/admin/san-pham') ?>">← Danh sách</a>
</div>

<form action="<?= url('/admin/san-pham/luu') ?>" method="post" enctype="multipart/form-data" novalidate><?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$id ?>">
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="lk-card lk-card-pad">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label req" for="code">Mã sản phẩm</label>
            <input class="form-control mono" id="code" name="code" maxlength="30" required value="<?= e($v('code')) ?>" placeholder="GK-AU101"></div>
          <div class="col-md-8"><label class="form-label req" for="name">Tên sản phẩm</label>
            <input class="form-control" id="name" name="name" maxlength="200" required value="<?= e($v('name')) ?>"></div>

          <div class="col-md-6"><label class="form-label req" for="cat">Danh mục</label>
            <select class="form-select" id="cat" name="category_id" required><option value="">— Chọn —</option>
              <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= (int)$v('category_id') === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
          <div class="col-md-6"><label class="form-label req" for="br">Thương hiệu</label>
            <select class="form-select" id="br" name="brand_id" required><option value="">— Chọn —</option>
              <?php foreach ($brands as $b): ?><option value="<?= $b['id'] ?>" <?= (int)$v('brand_id') === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>

          <div class="col-md-6"><label class="form-label req" for="price">Giá bán (₫)</label>
            <input class="form-control" id="price" type="number" min="0" step="1000" name="price" required value="<?= e($v('price')) ?>"></div>
          <div class="col-md-6"><label class="form-label req" for="stock">Tồn kho</label>
            <input class="form-control" id="stock" type="number" min="0" step="1" name="stock" required value="<?= e($v('stock', '0')) ?>"></div>

          <div class="col-md-4"><label class="form-label" for="shape">Kiểu dáng</label>
            <select class="form-select" id="shape" name="shape"><option value="">—</option><?= $opt('shape', Product::SHAPES, $v('shape')) ?></select></div>
          <div class="col-md-4"><label class="form-label" for="mat">Chất liệu</label>
            <select class="form-select" id="mat" name="material"><option value="">—</option><?= $opt('material', Product::MATERIALS, $v('material')) ?></select></div>
          <div class="col-md-4"><label class="form-label" for="color">Màu sắc</label>
            <input class="form-control" id="color" name="color" list="colors" maxlength="50" value="<?= e($v('color')) ?>">
            <datalist id="colors"><?php foreach (Product::COLORS as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist></div>

          <div class="col-12"><label class="form-label" for="size">Kích thước / quy cách</label>
            <input class="form-control" id="size" name="size" maxlength="50" value="<?= e($v('size')) ?>" placeholder="49-21-140 hoặc Chiết suất 1.60"></div>
          <div class="col-12"><label class="form-label" for="desc">Mô tả</label>
            <textarea class="form-control" id="desc" name="description" rows="4"><?= e($v('description')) ?></textarea></div>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="lk-card lk-card-pad mb-3">
        <label class="form-label" for="status">Hiển thị trên cửa hàng</label>
        <select class="form-select" id="status" name="status">
          <option value="visible" <?= $v('status', 'visible') === 'visible' ? 'selected' : '' ?>>Đang bán</option>
          <option value="hidden" <?= $v('status') === 'hidden' ? 'selected' : '' ?>>Ẩn</option>
        </select>
        <?php if ($inOrders): ?><div class="form-text">Sản phẩm đã có trong đơn hàng: không thể xóa, chỉ có thể ẩn.</div><?php endif; ?>
      </div>

      <div class="lk-card lk-card-pad">
        <label class="form-label" for="imagesInput">Thêm ảnh</label>
        <input class="form-control" id="imagesInput" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
        <div class="form-text">JPG, PNG hoặc WEBP · tối đa 2 MB / ảnh. Ảnh đầu tiên sẽ là ảnh đại diện.</div>
        <div id="imagesPreview" class="mt-2"></div>
      </div>
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Lưu sản phẩm</button>
    <a class="btn btn-ghost" href="<?= url('/admin/san-pham') ?>">Hủy</a>
  </div>
</form>

<?php if ($p && $images): ?>
  <div class="lk-card lk-card-pad mt-4">
    <h2 class="h6 mb-3">Ảnh hiện có (<?= count($images) ?>)</h2>
    <div class="row g-3">
      <?php foreach ($images as $img): ?>
        <div class="col-6 col-md-4 col-xl-2">
          <div class="img-tile">
            <img src="<?= e(product_img($img['image_path'])) ?>" alt="">
            <div class="acts">
              <?php if ((int)$img['is_primary']): ?>
                <span class="pill pill-acc">Ảnh đại diện</span>
              <?php else: ?>
                <form action="<?= url('/admin/san-pham/' . $id . '/anh-chinh') ?>" method="post"><?= csrf_field() ?>
                  <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>"><button class="btn btn-sm btn-ghost" type="submit">Đặt làm đại diện</button></form>
              <?php endif; ?>
              <form action="<?= url('/admin/san-pham/' . $id . '/anh-xoa') ?>" method="post" data-confirm="Xóa ảnh này?"><?= csrf_field() ?>
                <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>"><button class="btn btn-sm btn-ghost text-danger" type="submit">Xóa</button></form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
