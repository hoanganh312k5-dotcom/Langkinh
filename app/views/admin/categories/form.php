<?php /** @var array|null $c */ $id = $c['id'] ?? 0; ?>
<div class="page-head"><h1><?= $c ? 'Sửa danh mục' : 'Thêm danh mục' ?></h1></div>
<form class="lk-card lk-card-pad" style="max-width:560px" action="<?= url('/admin/danh-muc/luu') ?>" method="post" novalidate><?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$id ?>">
  <div class="mb-3"><label class="form-label req" for="name">Tên danh mục</label>
    <input class="form-control" id="name" name="name" maxlength="100" required autofocus value="<?= e(old('name', $c['name'] ?? '')) ?>"></div>
  <div class="mb-3"><label class="form-label" for="desc">Mô tả</label>
    <input class="form-control" id="desc" name="description" maxlength="255" value="<?= e(old('description', $c['description'] ?? '')) ?>"></div>
  <div class="mb-4"><label class="form-label" for="st">Hiển thị trên cửa hàng</label>
    <select class="form-select" id="st" name="status">
      <?php $st = old('status', $c['status'] ?? 'visible'); ?>
      <option value="visible" <?= $st === 'visible' ? 'selected' : '' ?>>Hiện</option>
      <option value="hidden" <?= $st === 'hidden' ? 'selected' : '' ?>>Ẩn (sản phẩm trong danh mục cũng không hiển thị)</option>
    </select></div>
  <button class="btn btn-primary" type="submit">Lưu</button>
  <a class="btn btn-ghost" href="<?= url('/admin/danh-muc') ?>">Hủy</a>
</form>
