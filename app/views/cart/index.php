<?php /** @var array $lines */ /** @var int $total */ /** @var bool $problem */ ?>
<h1 class="h3 mb-3">Giỏ hàng</h1>

<?php if (!$lines): ?>
  <div class="lk-card lk-card-pad text-center py-5">
    <h2 class="h5">Giỏ hàng của bạn đang trống</h2>
    <p class="text-ink2">Hãy chọn vài sản phẩm bạn thích.</p>
    <a class="btn btn-primary" href="<?= url('/san-pham') ?>">Tiếp tục mua sắm</a>
  </div>
<?php else: ?>
<div class="row g-4">
  <div class="col-lg-8">
    <form action="<?= url('/gio-hang/cap-nhat') ?>" method="post" data-autosubmit class="lk-card"><?= csrf_field() ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Sản phẩm</th><th class="text-end">Đơn giá</th><th class="text-center">Số lượng</th><th class="text-end">Thành tiền</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($lines as $l): $p = $l['product']; ?>
            <tr>
              <?php if ($p): ?>
                <td>
                  <div class="d-flex gap-3 align-items-center">
                    <img class="cart-img" src="<?= e(product_img($p['image'])) ?>" alt="">
                    <div>
                      <a class="fw-semibold" href="<?= url('/san-pham/' . $p['id']) ?>"><?= e($p['name']) ?></a>
                      <div class="small text-mute"><?= e($p['brand_name']) ?> · <?= e($p['code']) ?></div>
                      <?php if ($l['problem']): ?><div class="small" style="color:var(--lk-bad)"><?= e($l['problem']) ?></div><?php endif; ?>
                    </div>
                  </div>
                </td>
                <td class="text-end num"><?= vnd($p['price']) ?></td>
                <td class="text-center">
                  <div class="qty">
                    <button type="button" data-step="-1" aria-label="Giảm">−</button>
                    <input type="number" name="qty[<?= (int)$p['id'] ?>]" value="<?= (int)$l['qty'] ?>" min="0" max="<?= max(1, min((int)$p['stock'], (int)config('rules.max_qty_per_line'))) ?>" aria-label="Số lượng <?= e($p['name']) ?>">
                    <button type="button" data-step="1" aria-label="Tăng">+</button>
                  </div>
                </td>
                <td class="text-end num fw-semibold"><?= vnd($l['total']) ?></td>
              <?php else: ?>
                <td colspan="4" class="text-mute">Sản phẩm này đã ngừng bán.<input type="hidden" name="qty[<?= (int)$l['id'] ?>]" value="0"></td>
              <?php endif; ?>
              <td class="text-end">
                <button class="btn btn-sm btn-ghost" type="submit" formaction="<?= url('/gio-hang/xoa') ?>" name="product_id" value="<?= (int)$l['id'] ?>" aria-label="Xóa khỏi giỏ">Xóa</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="p-3 d-flex justify-content-between flex-wrap gap-2 border-top" style="border-color:var(--lk-line)!important">
        <a href="<?= url('/san-pham') ?>">← Tiếp tục mua sắm</a>
        <button class="btn btn-ghost btn-sm" type="submit">Cập nhật giỏ hàng</button>
      </div>
    </form>
  </div>

  <div class="col-lg-4">
    <div class="lk-card lk-card-pad">
      <h2 class="h6 mb-3">Tóm tắt đơn hàng</h2>
      <div class="summary-row"><span class="text-ink2">Tạm tính (<?= Cart::count() ?> sản phẩm)</span><span class="num"><?= vnd($total) ?></span></div>
      <div class="summary-row"><span class="text-ink2">Phí vận chuyển</span><span>Thỏa thuận khi xác nhận</span></div>
      <div class="summary-row summary-total"><span>Tổng cộng</span><span class="num"><?= vnd($total) ?></span></div>

      <?php if ($problem): ?>
        <div class="alert alert-warn mt-3 mb-0 small">Một số sản phẩm không đủ hàng. Hãy điều chỉnh số lượng hoặc xóa chúng để tiếp tục.</div>
      <?php elseif (Auth::check() && !Auth::isAdmin()): ?>
        <a class="btn btn-primary w-100 mt-3" href="<?= url('/dat-hang') ?>">Tiến hành đặt hàng</a>
      <?php elseif (Auth::isAdmin()): ?>
        <div class="alert alert-info mt-3 mb-0 small">Tài khoản quản trị không thể đặt hàng.</div>
      <?php else: ?>
        <a class="btn btn-primary w-100 mt-3" href="<?= url('/dang-nhap') ?>">Đăng nhập để đặt hàng</a>
        <p class="small text-mute mt-2 mb-0 text-center">Giỏ hàng của bạn sẽ được giữ lại sau khi đăng nhập. Chưa có tài khoản? <a href="<?= url('/dang-ky') ?>">Đăng ký</a></p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>
