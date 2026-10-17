<?php /** @var array $rows */ /** @var string $q */ ?>
<div class="page-head">
  <h1>Thương hiệu</h1>
  <a class="btn btn-primary" href="<?= url('/admin/thuong-hieu/tao') ?>">+ Thêm thương hiệu</a>
</div>
<form class="mb-3 d-flex gap-2" method="get">
  <input class="form-control" style="max-width:320px" type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm theo tên thương hiệu">
  <button class="btn btn-ghost" type="submit">Tìm</button>
  <?php if ($q !== ''): ?><a class="btn btn-ghost" href="<?= url('/admin/thuong-hieu') ?>">Xóa lọc</a><?php endif; ?>
</form>
<div class="lk-card table-wrap">
  <table class="table table-hover">
    <thead><tr><th>#</th><th>Tên thương hiệu</th><th>Mô tả</th><th class="text-center">Sản phẩm</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $b): ?>
      <tr>
        <td class="text-mute"><?= (int)$b['id'] ?></td>
        <td class="fw-semibold"><?= e($b['name']) ?></td>
        <td class="text-ink2"><?= e($b['description']) ?></td>
        <td class="text-center"><?= (int)$b['product_count'] ?></td>
        <td class="text-end text-nowrap">
          <a class="btn btn-sm btn-ghost" href="<?= url('/admin/thuong-hieu/' . $b['id'] . '/sua') ?>">Sửa</a>
          <form class="d-inline" action="<?= url('/admin/thuong-hieu/' . $b['id'] . '/xoa') ?>" method="post" data-confirm="Xóa thương hiệu «<?= e($b['name']) ?>»?"><?= csrf_field() ?>
            <button class="btn btn-sm btn-ghost text-danger" type="submit">Xóa</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="5" class="text-center text-mute py-4">Không có thương hiệu nào.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
