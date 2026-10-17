<?php /** @var array $f */ /** @var array $rows */ /** @var array $pg */ /** @var array $counts */ ?>
<div class="page-head"><div><h1>Đơn hàng</h1><div class="text-mute small"><?= (int)$pg['total'] ?> đơn phù hợp</div></div></div>

<div class="d-flex gap-1 flex-wrap mb-3">
  <a class="btn btn-sm <?= $f['status'] === '' ? 'btn-primary' : 'btn-ghost' ?>" href="<?= url('/admin/don-hang') ?>">Tất cả <span class="text-mute">(<?= array_sum($counts) ?>)</span></a>
  <?php foreach (Order::STATUS as $k => $label): ?>
    <a class="btn btn-sm <?= $f['status'] === $k ? 'btn-primary' : 'btn-ghost' ?>" href="<?= url('/admin/don-hang', ['status' => $k]) ?>"><?= e($label) ?> (<?= (int)$counts[$k] ?>)</a>
  <?php endforeach; ?>
</div>

<form class="lk-card lk-card-pad mb-3 d-flex flex-wrap gap-2 align-items-end" method="get">
  <input type="hidden" name="status" value="<?= e($f['status']) ?>">
  <div><label class="form-label small mb-1" for="q">Tìm kiếm</label><input class="form-control form-control-sm" id="q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Mã đơn, tên, SĐT, email"></div>
  <div><label class="form-label small mb-1" for="pay">Thanh toán</label>
    <select class="form-select form-select-sm" id="pay" name="payment"><option value="">Tất cả</option>
      <?php foreach (Order::PAY as $k => $l): ?><option value="<?= $k ?>" <?= $f['payment'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
  <div><label class="form-label small mb-1" for="from">Từ ngày</label><input class="form-control form-control-sm" id="from" type="date" name="from" value="<?= e($f['from']) ?>"></div>
  <div><label class="form-label small mb-1" for="to">Đến ngày</label><input class="form-control form-control-sm" id="to" type="date" name="to" value="<?= e($f['to']) ?>"></div>
  <button class="btn btn-primary btn-sm" type="submit">Lọc</button>
  <a class="btn btn-ghost btn-sm" href="<?= url('/admin/don-hang') ?>">Xóa lọc</a>
</form>

<div class="lk-card table-wrap">
  <table class="table table-hover">
    <thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Người nhận</th><th>Thanh toán</th><th class="text-end">Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $o): ?>
      <tr>
        <td><a class="mono fw-semibold" href="<?= url('/admin/don-hang/' . $o['id']) ?>"><?= e($o['code']) ?></a></td>
        <td class="num"><?= fdatetime($o['created_at']) ?></td>
        <td><?= e($o['receiver_name']) ?><div class="small text-mute"><?= e($o['receiver_phone']) ?></div></td>
        <td class="small"><?= e(Order::PAY[$o['payment_method']] ?? '') ?></td>
        <td class="text-end num fw-semibold"><?= vnd($o['total_amount']) ?></td>
        <td><?= status_pill($o['status']) ?></td>
        <td class="text-end"><a class="btn btn-sm btn-ghost" href="<?= url('/admin/don-hang/' . $o['id']) ?>">Xem</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-mute py-4">Không có đơn hàng phù hợp.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require APP_PATH . '/views/partials/pagination.php'; ?>
