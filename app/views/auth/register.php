<div class="auth-wrap wide">
  <div class="lk-card lk-card-pad p-4">
    <h1 class="h4 mb-1">Tạo tài khoản</h1>
    <p class="text-ink2 small mb-4">Đăng ký để đặt hàng và theo dõi đơn hàng của bạn.</p>
    <form action="<?= url('/dang-ky') ?>" method="post" class="row g-3" novalidate><?= csrf_field() ?>
      <div class="col-md-6">
        <label class="form-label req" for="name">Họ tên</label>
        <input class="form-control" id="name" name="name" maxlength="100" required autofocus value="<?= e(old('name')) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label req" for="phone">Số điện thoại</label>
        <input class="form-control" id="phone" name="phone" inputmode="tel" required value="<?= e(old('phone')) ?>">
      </div>
      <div class="col-12">
        <label class="form-label req" for="email">Email</label>
        <input class="form-control" id="email" type="email" name="email" autocomplete="username" required value="<?= e(old('email')) ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="address">Địa chỉ</label>
        <input class="form-control" id="address" name="address" maxlength="255" value="<?= e(old('address')) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label req" for="pw">Mật khẩu</label>
        <input class="form-control" id="pw" type="password" name="password" autocomplete="new-password" required minlength="<?= (int)config('rules.min_password') ?>">
        <div class="form-text">Tối thiểu <?= (int)config('rules.min_password') ?> ký tự, gồm chữ và số.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label req" for="pw2">Nhập lại mật khẩu</label>
        <input class="form-control" id="pw2" type="password" name="password_confirm" autocomplete="new-password" required>
      </div>
      <div class="col-12"><button class="btn btn-primary w-100" type="submit">Đăng ký</button></div>
    </form>
    <p class="text-center small mt-3 mb-0">Đã có tài khoản? <a href="<?= url('/dang-nhap') ?>">Đăng nhập</a></p>
  </div>
</div>
