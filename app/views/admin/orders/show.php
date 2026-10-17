<?php /** @var array $o */ /** @var array $items */ /** @var array $logs */
$next = Order::NEXT[$o['status']] ?? []; ?>
<div class="page-head">
  <div>
    <a class="small" href="<?= url('/admin/don-hang') ?>">← Danh sách đơn hàng</a>
    <h1 class="mt-1">Đơn <span class="mono"><?= e($o['code']) ?></span> <?= status_pill($o['status']) ?></h1>
    <div class="text-mute small">Đặt lúc <?= fdatetime($o['created_at']) ?></div>
  </div>
  <a class="btn btn-ghost" target="_blank" rel="noopener" href="<?= url('/admin/don-hang/' . $o['id'] . '/hoa-don') ?>">In hóa đơn</a>
</div>

<div class="row g-4">
  <div class="col-xl-8">
    <div class="lk-card mb-4 table-wrap">
      <table class="table">
        <thead><tr><th>Sản phẩm</th><th class="text-end">Đơn giá</th><th class="text-center">SL</th><th class="text-end">Thành tiền</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><div class="d-flex gap-3 align-items-center"><img class="thumb-sm" src="<?= e(product_img($it['image'])) ?>" alt="">
              <a href="<?= url('/admin/san-pham/' . $it['product_id'] . '/sua') ?>"><?= e($it['product_name']) ?></a></div></td>
            <td class="text-end num"><?= vnd($it['unit_price']) ?></td>
            <td class="text-center"><?= (int)$it['quantity'] ?></td>
            <td class="text-end num fw-semibold"><?= vnd($it['unit_price'] * $it['quantity']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="3" class="text-end fw-semibold">Tổng cộng</td><td class="text-end num fw-bold fs-5"><?= vnd($o['total_amount']) ?></td></tr></tfoot>
      </table>
    </div>

    <div class="lk-card lk-card-pad">
      <h2 class="h6 mb-3">Cập nhật trạng thái</h2>
      <?php if (!$next): ?>
        <p class="mb-0 text-ink2">Đơn đã ở trạng thái cuối («<?= e(Order::STATUS[$o['status']]) ?>») nên không thể thay đổi.</p>
      <?php else: ?>
        <div class="d-flex gap-2 flex-wrap">
          <?php foreach ($next as $s): $cancel = $s === 'cancelled'; ?>
            <form action="<?= url('/admin/don-hang/' . $o['id'] . '/trang-thai') ?>" method="post"
                  <?= $cancel ? 'data-confirm="Hủy đơn ' . e($o['code']) . '? Tồn kho của các sản phẩm sẽ được hoàn lại."' : '' ?>><?= csrf_field() ?>
              <input type="hidden" name="status" value="<?= $s ?>">
              <button class="btn <?= $cancel ? 'btn-outline-danger' : 'btn-primary' ?>" type="submit"><?= $cancel ? 'Hủy đơn' : 'Chuyển sang «' . e(Order::STATUS[$s]) . '»' ?></button>
            </form>
          <?php endforeach; ?>
        </div>
        <p class="form-text mt-2 mb-0">Trạng thái chỉ đi tiến theo thứ tự; có thể hủy trước khi Hoàn thành.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="lk-card lk-card-pad mb-3">
      <h2 class="h6">Khách hàng</h2>
      <a class="fw-semibold" href="<?= url('/admin/khach-hang/' . $o['user_id']) ?>"><?= e($o['customer_name']) ?></a>
      <div class="small text-mute"><?= e($o['customer_email']) ?></div>
      <hr>
      <h2 class="h6">Giao đến</h2>
      <div class="fw-semibold"><?= e($o['receiver_name']) ?></div>
      <div><?= e($o['receiver_phone']) ?></div>
      <div class="text-ink2"><?= e($o['receiver_address']) ?></div>
      <?php if ($o['note']): ?><div class="small text-mute mt-2">Ghi chú: <?= e($o['note']) ?></div><?php endif; ?>
      <hr>
      <div class="small text-mute">Thanh toán</div>
      <div class="fw-semibold"><?= e(Order::PAY[$o['payment_method']] ?? '') ?></div>
    </div>
    <div class="lk-card lk-card-pad">
      <h2 class="h6 mb-3">Lịch sử trạng thái</h2>
      <ul class="log-list">
        <?php foreach (array_reverse($logs) as $l): ?>
          <li>
            <div class="fw-semibold small"><?= $l['old_status'] ? e(Order::STATUS[$l['old_status']]) . ' → ' : '' ?><?= e(Order::STATUS[$l['new_status']]) ?></div>
            <div class="small text-mute"><?= fdatetime($l['changed_at']) ?> · <?= $l['by_role'] === 'admin' ? 'Quản trị' : e($l['by_name']) ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
