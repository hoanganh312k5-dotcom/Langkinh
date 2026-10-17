<?php /** @var array|null $b */ $id = $b['id'] ?? 0; ?>
<div class="page-head"><h1><?= $b ? 'Sửa thương hiệu' : 'Thêm thương hiệu' ?></h1></div>
<form class="lk-card lk-card-pad" style="max-width:560px" action="<?= url('/admin/thuong-hieu/luu') ?>" method="post" novalidate><?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$id ?>">
  <div class="mb-3"><label class="form-label req" for="name">Tên thương hiệu</label>
    <input class="form-control" id="name" name="name" maxlength="100" required autofocus value="<?= e(old('name', $b['name'] ?? '')) ?>"></div>
  <div class="mb-4"><label class="form-label" for="desc">Mô tả</label>
    <input class="form-control" id="desc" name="description" maxlength="255" value="<?= e(old('description', $b['description'] ?? '')) ?>"></div>
  <button class="btn btn-primary" type="submit">Lưu</button>
  <a class="btn btn-ghost" href="<?= url('/admin/thuong-hieu') ?>">Hủy</a>
</form>
