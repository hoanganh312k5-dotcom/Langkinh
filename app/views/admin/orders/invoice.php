<?php /** @var array $o */ /** @var array $items */ ?>
<div class="no-print d-flex gap-2 justify-content-center pt-3">
  <button class="btn btn-primary" type="button" data-print>In hóa đơn</button>
  <a class="btn btn-ghost" href="<?= url('/admin/don-hang/' . $o['id']) ?>">← Quay lại đơn hàng</a>
</div>
<div class="invoice">
  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <div class="brand mb-1"><?php require APP_PATH . '/views/partials/logo.php'; ?> <?= e(config('shop.name')) ?></div>
      <div class="small text-ink2"><?= e(config('shop.address')) ?><br>ĐT: <?= e(config('shop.phone')) ?> · <?= e(config('shop.email')) ?></div>
    </div>
    <div class="text-end">
      <h1 class="h4 mb-1">HÓA ĐƠN BÁN HÀNG</h1>
      <div>Số: <strong class="mono"><?= e($o['code']) ?></strong></div>
      <div class="small text-ink2">Ngày lập: <?= fdate($o['created_at']) ?></div>
    </div>
  </div>

  <div class="row mb-4 small">
    <div class="col-6">
      <div class="text-mute text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.06em">Khách hàng</div>
      <div class="fw-semibold"><?= e($o['customer_name']) ?></div><div><?= e($o['customer_email']) ?></div>
    </div>
    <div class="col-6">
      <div class="text-mute text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.06em">Giao đến</div>
      <div class="fw-semibold"><?= e($o['receiver_name']) ?> · <?= e($o['receiver_phone']) ?></div><div><?= e($o['receiver_address']) ?></div>
    </div>
  </div>

  <table class="table">
    <thead><tr><th>#</th><th>Sản phẩm</th><th class="text-end">Đơn giá</th><th class="text-center">SL</th><th class="text-end">Thành tiền</th></tr></thead>
    <tbody>
    <?php foreach ($items as $i => $it): ?>
      <tr><td><?= $i + 1 ?></td><td><?= e($it['product_name']) ?></td><td class="text-end num"><?= vnd($it['unit_price']) ?></td>
        <td class="text-center"><?= (int)$it['quantity'] ?></td><td class="text-end num"><?= vnd($it['unit_price'] * $it['quantity']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="4" class="text-end fw-bold">Tổng thanh toán</td><td class="text-end num fw-bold fs-5"><?= vnd($o['total_amount']) ?></td></tr></tfoot>
  </table>

  <div class="small text-ink2 mt-3">
    Hình thức thanh toán: <strong><?= e(Order::PAY[$o['payment_method']] ?? '') ?></strong> · Trạng thái đơn: <strong><?= e(Order::STATUS[$o['status']]) ?></strong>
    <?php if ($o['note']): ?><br>Ghi chú: <?= e($o['note']) ?><?php endif; ?>
  </div>
  <div class="row text-center mt-5 pt-4 small">
    <div class="col-6"><strong>Người mua hàng</strong><br><span class="text-mute">(Ký, ghi rõ họ tên)</span></div>
    <div class="col-6"><strong>Người bán hàng</strong><br><span class="text-mute">(Ký, ghi rõ họ tên)</span></div>
  </div>
  <p class="text-center small text-mute mt-5 mb-0">Cảm ơn quý khách đã mua hàng tại <?= e(config('shop.name')) ?>!</p>
</div>
