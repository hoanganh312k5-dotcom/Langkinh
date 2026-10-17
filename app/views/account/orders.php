<?php /** @var array $rows */ /** @var array $pg */ /** @var string $status */ ?>
<h1 class="h3 mb-3">Đơn hàng của tôi</h1>
<ul class="nav nav-pills gap-1 mb-3 flex-wrap">
  <li class="nav-item"><a class="btn btn-sm <?= $status === '' ? 'btn-primary' : 'btn-ghost' ?>" href="<?= url('/don-hang') ?>">Tất cả</a></li>
  <?php foreach (Order::STATUS as $k => $label): ?>
    <li class="nav-item"><a class="btn btn-sm <?= $status === $k ? 'btn-primary' : 'btn-ghost' ?>" href="<?= url('/don-hang', ['status' => $k]) ?>"><?= e($label) ?></a></li>
  <?php endforeach; ?>
</ul>

<?php if (!$rows): ?>
  <div class="lk-card lk-card-pad text-center py-5">
    <h2 class="h5">Chưa có đơn hàng nào</h2>
    <p class="text-ink2"><?= $status ? 'Không có đơn nào ở trạng thái này.' : 'Hãy chọn một đôi kính ưng ý.' ?></p>
    <a class="btn btn-primary" href="<?= url('/san-pham') ?>">Mua sắm ngay</a>
  </div>
<?php else: ?>
  <div class="lk-card table-wrap">
    <table class="table table-hover">
      <thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Sản phẩm</th><th class="text-end">Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $o): ?>
        <tr>
          <td><a class="mono fw-semibold" href="<?= url('/don-hang/' . $o['id']) ?>"><?= e($o['code']) ?></a></td>
          <td class="num"><?= fdatetime($o['created_at']) ?></td>
          <td><?= (int)$o['item_count'] ?> sản phẩm</td>
          <td class="text-end num fw-semibold"><?= vnd($o['total_amount']) ?></td>
          <td><?= status_pill($o['status']) ?></td>
          <td class="text-end"><a class="btn btn-sm btn-ghost" href="<?= url('/don-hang/' . $o['id']) ?>">Chi tiết</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php require APP_PATH . '/views/partials/pagination.php'; ?>
<?php endif; ?>
