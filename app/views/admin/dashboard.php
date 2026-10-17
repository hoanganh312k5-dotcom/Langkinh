<?php
/** @var string $from */ /** @var string $to */ /** @var array $sum */ /** @var array $days */ /** @var array $status */
/** @var array $low */ /** @var int $lowCount */ /** @var array $recent */
$totalOrders = max(1, array_sum($status));
?>
<div class="page-head">
  <div><h1>Bảng điều khiển</h1><div class="text-mute small">30 ngày gần nhất: <?= fdate($from) ?> – <?= fdate($to) ?></div></div>
  <a class="btn btn-ghost btn-sm" href="<?= url('/admin/thong-ke') ?>">Thống kê chi tiết →</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="lbl">Doanh thu</div><div class="val"><?= vnd($sum['revenue']) ?></div><div class="sub">Chỉ tính đơn Hoàn thành</div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="lbl">Đơn hàng</div><div class="val"><?= (int)$sum['orders'] ?></div><div class="sub"><?= (int)$sum['done'] ?> hoàn thành · <?= (int)$sum['cancelled'] ?> hủy</div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="lbl">Chờ xác nhận</div><div class="val"><?= (int)$status['pending'] ?></div><div class="sub"><a href="<?= url('/admin/don-hang', ['status' => 'pending']) ?>">Xử lý ngay →</a></div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="lbl">Sắp hết / hết hàng</div><div class="val"><?= $lowCount ?></div><div class="sub"><a href="<?= url('/admin/ton-kho', ['stock' => 'low']) ?>">Xem tồn kho →</a></div></div></div>
</div>

<div class="row g-3">
  <div class="col-xl-8">
    <div class="lk-card lk-card-pad h-100">
      <h2 class="h6 mb-3">Doanh thu theo ngày</h2>
      <?php require APP_PATH . '/views/partials/revenue_chart.php'; ?>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="lk-card lk-card-pad h-100">
      <h2 class="h6 mb-3">Đơn theo trạng thái (tất cả)</h2>
      <?php foreach (Order::STATUS as $k => $label): ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between small mb-1"><span><?= e($label) ?></span><strong><?= (int)$status[$k] ?></strong></div>
          <div class="hbar"><span style="width:<?= round($status[$k] / $totalOrders * 100) ?>%"></span></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="col-xl-7">
    <div class="lk-card">
      <div class="p-3 pb-0 d-flex justify-content-between"><h2 class="h6 mb-0">Đơn hàng mới nhất</h2><a class="small" href="<?= url('/admin/don-hang') ?>">Tất cả</a></div>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Mã đơn</th><th>Người nhận</th><th class="text-end">Tổng</th><th>Trạng thái</th></tr></thead>
          <tbody>
          <?php foreach ($recent as $o): ?>
            <tr>
              <td><a class="mono fw-semibold" href="<?= url('/admin/don-hang/' . $o['id']) ?>"><?= e($o['code']) ?></a></td>
              <td><?= e($o['receiver_name']) ?></td>
              <td class="text-end num"><?= vnd($o['total_amount']) ?></td>
              <td><?= status_pill($o['status']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-xl-5">
    <div class="lk-card">
      <div class="p-3 pb-0 d-flex justify-content-between"><h2 class="h6 mb-0">Cần nhập thêm hàng</h2><a class="small" href="<?= url('/admin/ton-kho', ['stock' => 'low']) ?>">Quản lý</a></div>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Sản phẩm</th><th class="text-end">Tồn</th></tr></thead>
          <tbody>
          <?php foreach ($low as $p): ?>
            <tr>
              <td><a href="<?= url('/admin/san-pham/' . $p['id'] . '/sua') ?>"><?= e($p['name']) ?></a><div class="small mono text-mute"><?= e($p['code']) ?></div></td>
              <td class="text-end num <?= (int)$p['stock'] === 0 ? 'stock-out' : 'stock-low' ?>"><?= (int)$p['stock'] ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$low): ?><tr><td colspan="2" class="text-mute text-center py-4">Tồn kho ổn định.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
