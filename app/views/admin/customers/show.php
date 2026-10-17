<?php /** @var array $u */ /** @var array $orders */
$spent = array_sum(array_map(fn($o) => $o['status'] === 'done' ? (int)$o['total_amount'] : 0, $orders)); ?>
<div class="page-head">
  <div><a class="small" href="<?= url('/admin/khach-hang') ?>">← Danh sách khách hàng</a>
    <h1 class="mt-1"><?= e($u['name']) ?> <?= $u['status'] === 'active' ? '<span class="pill pill-ok">Hoạt động</span>' : '<span class="pill pill-bad">Đã khóa</span>' ?></h1></div>
  <div class="d-flex gap-2">
    <?php if ($u['status'] === 'active'): ?>
      <form action="<?= url('/admin/khach-hang/' . $u['id'] . '/khoa') ?>" method="post" data-confirm="Khóa tài khoản này?"><?= csrf_field() ?><input type="hidden" name="back" value="detail">
        <button class="btn btn-outline-danger" type="submit">Khóa tài khoản</button></form>
    <?php else: ?>
      <form action="<?= url('/admin/khach-hang/' . $u['id'] . '/mo-khoa') ?>" method="post"><?= csrf_field() ?><input type="hidden" name="back" value="detail">
        <button class="btn btn-primary" type="submit">Mở khóa</button></form>
    <?php endif; ?>
    <?php if (!$orders): ?>
      <form action="<?= url('/admin/khach-hang/' . $u['id'] . '/xoa') ?>" method="post" data-confirm="Xóa vĩnh viễn khách hàng này?"><?= csrf_field() ?>
        <button class="btn btn-ghost text-danger" type="submit">Xóa</button></form>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="lk-card lk-card-pad">
      <dl class="spec-list mb-0" style="grid-template-columns:100px 1fr">
        <dt>Email</dt><dd><?= e($u['email']) ?></dd>
        <dt>Điện thoại</dt><dd><?= e($u['phone']) ?></dd>
        <dt>Địa chỉ</dt><dd><?= e($u['address'] ?: '—') ?></dd>
        <dt>Đăng ký</dt><dd><?= fdate($u['created_at']) ?></dd>
        <dt>Số đơn</dt><dd><?= count($orders) ?></dd>
        <dt>Đã chi</dt><dd><?= vnd($spent) ?></dd>
      </dl>
      <?php if ($orders): ?><p class="small text-mute mt-3 mb-0">Khách đã có đơn hàng nên chỉ có thể khóa, không thể xóa.</p><?php endif; ?>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="lk-card table-wrap">
      <table class="table table-hover">
        <thead><tr><th>Mã đơn</th><th>Ngày</th><th class="text-end">Tổng</th><th>Trạng thái</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr><td><a class="mono fw-semibold" href="<?= url('/admin/don-hang/' . $o['id']) ?>"><?= e($o['code']) ?></a></td>
            <td class="num"><?= fdatetime($o['created_at']) ?></td><td class="text-end num"><?= vnd($o['total_amount']) ?></td><td><?= status_pill($o['status']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?><tr><td colspan="4" class="text-center text-mute py-4">Khách chưa có đơn hàng.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
