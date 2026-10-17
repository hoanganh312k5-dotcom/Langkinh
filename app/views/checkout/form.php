<?php /** @var array $lines */ /** @var int $total */ /** @var array $u */ ?>
<h1 class="h3 mb-3">Đặt hàng</h1>
<form action="<?= url('/dat-hang') ?>" method="post" class="row g-4" novalidate><?= csrf_field() ?>
  <div class="col-lg-7">
    <div class="lk-card lk-card-pad mb-3">
      <h2 class="h6 mb-3">Thông tin nhận hàng</h2>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label req" for="rn">Họ tên người nhận</label>
          <input class="form-control" id="rn" name="receiver_name" maxlength="100" required value="<?= e(old('receiver_name', $u['name'])) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label req" for="rp">Số điện thoại</label>
          <input class="form-control" id="rp" name="receiver_phone" inputmode="tel" required value="<?= e(old('receiver_phone', $u['phone'])) ?>">
        </div>
        <div class="col-12">
          <label class="form-label req" for="ra">Địa chỉ giao hàng</label>
          <input class="form-control" id="ra" name="receiver_address" maxlength="255" required placeholder="Số nhà, đường, quận/huyện, tỉnh/thành" value="<?= e(old('receiver_address', $u['address'])) ?>">
        </div>
        <div class="col-12">
          <label class="form-label" for="nt">Ghi chú</label>
          <textarea class="form-control" id="nt" name="note" rows="2" maxlength="500" placeholder="Ví dụ: giao giờ hành chính"><?= e(old('note')) ?></textarea>
        </div>
      </div>
    </div>

    <div class="lk-card lk-card-pad">
      <h2 class="h6 mb-3">Phương thức thanh toán</h2>
      <div class="d-grid gap-2">
        <?php foreach (Order::PAY as $k => $label): ?>
          <label class="pay-opt">
            <input class="form-check-input mt-1" type="radio" name="payment" value="<?= $k ?>" <?= old('payment', 'cod') === $k ? 'checked' : '' ?>>
            <span>
              <strong><?= e($label) ?></strong>
              <span class="d-block small text-ink2">
                <?= $k === 'cod' ? 'Thanh toán bằng tiền mặt cho nhân viên giao hàng.' : 'Thông tin tài khoản sẽ hiển thị sau khi đặt hàng thành công.' ?>
              </span>
            </span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="lk-card lk-card-pad">
      <h2 class="h6 mb-3">Đơn hàng của bạn</h2>
      <?php foreach ($lines as $l): $p = $l['product']; ?>
        <div class="d-flex gap-3 mb-3 align-items-center">
          <img class="cart-img" style="width:64px" src="<?= e(product_img($p['image'])) ?>" alt="">
          <div class="flex-grow-1">
            <div class="fw-semibold small"><?= e($p['name']) ?></div>
            <div class="small text-mute"><?= vnd($p['price']) ?> × <?= (int)$l['qty'] ?></div>
          </div>
          <div class="num fw-semibold small"><?= vnd($l['total']) ?></div>
        </div>
      <?php endforeach; ?>
      <div class="summary-row summary-total"><span>Tổng cộng</span><span class="num"><?= vnd($total) ?></span></div>
      <button class="btn btn-primary w-100 mt-3" type="submit">Xác nhận đặt hàng</button>
      <a class="d-block text-center mt-2 small" href="<?= url('/gio-hang') ?>">← Quay lại giỏ hàng</a>
    </div>
  </div>
</form>
