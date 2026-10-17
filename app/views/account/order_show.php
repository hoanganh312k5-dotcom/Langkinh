<?php
/** @var array $o */ /** @var array $items */ /** @var array $logs */
$flow = ['pending', 'confirmed', 'shipping', 'done'];
$cancelled = $o['status'] === 'cancelled';
$reached = array_column($logs, 'new_status');
?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <a class="small" href="<?= url('/don-hang') ?>">← Đơn hàng của tôi</a>
    <h1 class="h3 mb-1 mt-1">Đơn <span class="mono"><?= e($o['code']) ?></span></h1>
    <div class="text-mute small">Đặt lúc <?= fdatetime($o['created_at']) ?></div>
  </div>
  <div><?= status_pill($o['status']) ?></div>
</div>

<div class="lk-card lk-card-pad mb-4">
  <ol class="steps">
    <?php foreach ($flow as $s):
        $cls = in_array($s, $reached, true) && !($cancelled && $s !== 'pending') ? 'done' : '';
        if ($cancelled && $s === 'pending') { $cls = 'done'; } ?>
      <li class="<?= $cls ?>"><?= e(Order::STATUS[$s]) ?></li>
    <?php endforeach; ?>
    <?php if ($cancelled): ?><li class="cancel"><?= e(Order::STATUS['cancelled']) ?></li><?php endif; ?>
  </ol>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="lk-card mb-4">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Sản phẩm</th><th class="text-end">Đơn giá</th><th class="text-center">SL</th><th class="text-end">Thành tiền</th></tr></thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td>
                <div class="d-flex gap-3 align-items-center">
                  <img class="cart-img" style="width:64px" src="<?= e(product_img($it['image'])) ?>" alt="">
                  <a class="fw-semibold" href="<?= url('/san-pham/' . $it['product_id']) ?>"><?= e($it['product_name']) ?></a>
                </div>
              </td>
              <td class="text-end num"><?= vnd($it['unit_price']) ?></td>
              <td class="text-center"><?= (int)$it['quantity'] ?></td>
              <td class="text-end num fw-semibold"><?= vnd($it['unit_price'] * $it['quantity']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><td colspan="3" class="text-end fw-semibold">Tổng cộng</td><td class="text-end num fw-bold fs-5"><?= vnd($o['total_amount']) ?></td></tr>
          </tfoot>
        </table>
      </div>
    </div>

    <?php if ($o['status'] === 'pending'): ?>
      <form action="<?= url('/don-hang/' . $o['id'] . '/huy') ?>" method="post" data-confirm="Bạn chắc chắn muốn hủy đơn <?= e($o['code']) ?>? Thao tác này không thể hoàn tác."><?= csrf_field() ?>
        <button class="btn btn-outline-danger" type="submit">Hủy đơn hàng</button>
        <span class="small text-mute ms-2">Chỉ hủy được khi đơn còn «Chờ xác nhận».</span>
      </form>
    <?php endif; ?>
  </div>

  <div class="col-lg-4">
    <div class="lk-card lk-card-pad mb-3">
      <h2 class="h6">Thông tin nhận hàng</h2>
      <div class="fw-semibold"><?= e($o['receiver_name']) ?></div>
      <div><?= e($o['receiver_phone']) ?></div>
      <div class="text-ink2"><?= e($o['receiver_address']) ?></div>
      <?php if ($o['note']): ?><div class="small text-mute mt-2">Ghi chú: <?= e($o['note']) ?></div><?php endif; ?>
      <hr>
      <div class="small text-mute">Thanh toán</div>
      <div class="fw-semibold"><?= e(Order::PAY[$o['payment_method']] ?? '') ?></div>
      <?php if ($o['payment_method'] === 'bank' && !in_array($o['status'], ['cancelled', 'done'], true)): $bank = config('shop.bank'); ?>
        <div class="alert alert-info small mt-3 mb-0">
          <strong>Thông tin chuyển khoản</strong><br>
          <?= e($bank['name']) ?><br>
          STK: <span class="mono"><?= e($bank['number']) ?></span><br>
          Chủ TK: <?= e($bank['holder']) ?><br>
          Số tiền: <strong><?= vnd($o['total_amount']) ?></strong><br>
          Nội dung: <span class="mono"><?= e($o['code']) ?></span>
        </div>
      <?php endif; ?>
    </div>

    <div class="lk-card lk-card-pad">
      <h2 class="h6 mb-3">Lịch sử trạng thái</h2>
      <ul class="log-list">
        <?php foreach (array_reverse($logs) as $l): ?>
          <li>
            <div class="fw-semibold small"><?= e(Order::STATUS[$l['new_status']]) ?></div>
            <div class="small text-mute"><?= fdatetime($l['changed_at']) ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
