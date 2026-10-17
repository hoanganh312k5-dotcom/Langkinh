<?php /** @var array $rows */ /** @var string $q */ ?>
<div class="page-head">
  <h1>Danh mục</h1>
  <a class="btn btn-primary" href="<?= url('/admin/danh-muc/tao') ?>">+ Thêm danh mục</a>
</div>
<form class="mb-3 d-flex gap-2" method="get">
  <input class="form-control" style="max-width:320px" type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm theo tên danh mục">
  <button class="btn btn-ghost" type="submit">Tìm</button>
  <?php if ($q !== ''): ?><a class="btn btn-ghost" href="<?= url('/admin/danh-muc') ?>">Xóa lọc</a><?php endif; ?>
</form>
<div class="lk-card table-wrap">
  <table class="table table-hover">
    <thead><tr><th>#</th><th>Tên danh mục</th><th>Mô tả</th><th class="text-center">Sản phẩm</th><th>Hiển thị</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $c): ?>
      <tr>
        <td class="text-mute"><?= (int)$c['id'] ?></td>
        <td class="fw-semibold"><?= e($c['name']) ?></td>
        <td class="text-ink2"><?= e($c['description']) ?></td>
        <td class="text-center"><?= (int)$c['product_count'] ?></td>
        <td><?= $c['status'] === 'visible' ? '<span class="pill pill-ok">Hiện</span>' : '<span class="pill pill-mute">Ẩn</span>' ?></td>
        <td class="text-end text-nowrap">
          <a class="btn btn-sm btn-ghost" href="<?= url('/admin/danh-muc/' . $c['id'] . '/sua') ?>">Sửa</a>
          <form class="d-inline" action="<?= url('/admin/danh-muc/' . $c['id'] . '/xoa') ?>" method="post" data-confirm="Xóa danh mục «<?= e($c['name']) ?>»?"><?= csrf_field() ?>
            <button class="btn btn-sm btn-ghost text-danger" type="submit">Xóa</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-mute py-4">Không có danh mục nào.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
