<?php
/** @var string $range */ /** @var string $from */ /** @var string $to */ /** @var array $sum */ /** @var array $days */ /** @var array $status */ /** @var array $top */
$totalOrders = max(1, array_sum($status));
$maxQty = max(array_column($top, 'qty') ?: [1]);
?>
<div class="page-head"><h1>Thống kê</h1></div>

<form class="lk-card lk-card-pad mb-4 d-flex flex-wrap gap-2 align-items-end" method="get">
  <div class="btn-group" role="group" aria-label="Khoảng nhanh">
    <?php foreach (['7' => '7 ngày', '30' => '30 ngày', '90' => '90 ngày'] as $k => $label): ?>
      <a class="btn btn-sm <?= $range === (string)$k ? 'btn-primary' : 'btn-ghost' ?>" href="<?= url('/admin/thong-ke', ['range' => $k]) ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </div>
  <div class="ms-md-3"><label class="form-label mb-0 small" for="from">Từ ngày</label><input class="form-control form-control-sm" id="from" type="date" name="from" value="<?= e($from) ?>"></div>
  <div><label class="form-label mb-0 small" for="to">Đến ngày</label><input class="form-control form-control-sm" id="to" type="date" name="to" value="<?= e($to) ?>"></div>
  <button class="btn btn-primary btn-sm" type="submit">Xem</button>
  <span class="text-mute small ms-auto"><?= fdate($from) ?> – <?= fdate($to) ?></span>
</form>

<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="lbl">Doanh thu</div><div class="val"><?= vnd($sum['revenue']) ?></div><div class="sub">Chỉ tính đơn Hoàn thành</div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="lbl">Tổng số đơn</div><div class="val"><?= (int)$sum['orders'] ?></div><div class="sub"><?= (int)$sum['done'] ?> hoàn thành</div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="lbl">Giá trị đơn TB</div><div class="val"><?= $sum['done'] ? vnd($sum['revenue'] / $sum['done']) : '—' ?></div><div class="sub">Trên đơn Hoàn thành</div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="lbl">Khách mới</div><div class="val"><?= (int)$sum['new_customers'] ?></div><div class="sub">Tài khoản đăng ký trong kỳ</div></div></div>
</div>

<div class="row g-3">
  <div class="col-12">
    <div class="lk-card lk-card-pad"><h2 class="h6 mb-3">Doanh thu theo ngày</h2><?php require APP_PATH . '/views/partials/revenue_chart.php'; ?></div>
  </div>
  <div class="col-lg-7">
    <div class="lk-card">
      <div class="p-3 pb-0"><h2 class="h6 mb-0">Sản phẩm bán chạy (theo số lượng)</h2></div>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>#</th><th>Sản phẩm</th><th style="width:28%"></th><th class="text-end">SL bán</th><th class="text-end">Doanh thu</th></tr></thead>
          <tbody>
          <?php foreach ($top as $i => $t): ?>
            <tr>
              <td class="text-mute"><?= $i + 1 ?></td>
              <td><?= e($t['product_name']) ?></td>
              <td><div class="hbar"><span style="width:<?= round($t['qty'] / $maxQty * 100) ?>%"></span></div></td>
              <td class="text-end num fw-semibold"><?= (int)$t['qty'] ?></td>
              <td class="text-end num"><?= vnd($t['revenue']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$top): ?><tr><td colspan="5" class="text-center text-mute py-4">Chưa có đơn Hoàn thành trong khoảng này.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="lk-card lk-card-pad h-100">
      <h2 class="h6 mb-3">Đơn theo trạng thái</h2>
      <?php foreach (Order::STATUS as $k => $label): ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between small mb-1"><span><?= status_pill($k) ?></span><strong><?= (int)$status[$k] ?></strong></div>
          <div class="hbar"><span style="width:<?= round($status[$k] / $totalOrders * 100) ?>%"></span></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
