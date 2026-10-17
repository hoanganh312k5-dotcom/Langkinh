<?php /** @var array $f */ /** @var array $rows */ /** @var array $pg */ ?>
<div class="page-head"><div><h1>Khách hàng</h1><div class="text-mute small"><?= (int)$pg['total'] ?> khách hàng</div></div></div>
<form class="lk-card lk-card-pad mb-3 d-flex flex-wrap gap-2 align-items-end" method="get">
  <div><label class="form-label small mb-1" for="q">Tìm kiếm</label><input class="form-control form-control-sm" id="q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Tên, email, SĐT"></div>
  <div><label class="form-label small mb-1" for="st">Trạng thái</label>
    <select class="form-select form-select-sm" id="st" name="status"><option value="">Tất cả</option>
      <option value="active" <?= $f['status'] === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
      <option value="locked" <?= $f['status'] === 'locked' ? 'selected' : '' ?>>Đã khóa</option></select></div>
  <button class="btn btn-primary btn-sm" type="submit">Lọc</button>
</form>
<div class="lk-card table-wrap">
  <table class="table table-hover">
    <thead><tr><th>Khách hàng</th><th>Điện thoại</th><th>Ngày đăng ký</th><th class="text-center">Đơn</th><th class="text-end">Đã chi (Hoàn thành)</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $u): ?>
      <tr>
        <td><a class="fw-semibold" href="<?= url('/admin/khach-hang/' . $u['id']) ?>"><?= e($u['name']) ?></a><div class="small text-mute"><?= e($u['email']) ?></div></td>
        <td><?= e($u['phone']) ?></td>
        <td class="num"><?= fdate($u['created_at']) ?></td>
        <td class="text-center"><?= (int)$u['order_count'] ?></td>
        <td class="text-end num"><?= vnd($u['spent']) ?></td>
        <td><?= $u['status'] === 'active' ? '<span class="pill pill-ok">Hoạt động</span>' : '<span class="pill pill-bad">Đã khóa</span>' ?></td>
        <td class="text-end text-nowrap">
          <a class="btn btn-sm btn-ghost" href="<?= url('/admin/khach-hang/' . $u['id']) ?>">Xem</a>
          <?php if ($u['status'] === 'active'): ?>
            <form class="d-inline" action="<?= url('/admin/khach-hang/' . $u['id'] . '/khoa') ?>" method="post" data-confirm="Khóa tài khoản «<?= e($u['name']) ?>»? Khách sẽ không thể đăng nhập hay đặt hàng."><?= csrf_field() ?>
              <button class="btn btn-sm btn-ghost text-danger" type="submit">Khóa</button></form>
          <?php else: ?>
            <form class="d-inline" action="<?= url('/admin/khach-hang/' . $u['id'] . '/mo-khoa') ?>" method="post"><?= csrf_field() ?>
              <button class="btn btn-sm btn-ghost" type="submit">Mở khóa</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-mute py-4">Không có khách hàng phù hợp.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require APP_PATH . '/views/partials/pagination.php'; ?>
