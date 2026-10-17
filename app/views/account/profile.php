<?php /** @var array $u */ ?>
<h1 class="h3 mb-3">Tài khoản của tôi</h1>
<div class="row g-4">
  <div class="col-lg-7">
    <form class="lk-card lk-card-pad" action="<?= url('/tai-khoan') ?>" method="post" novalidate><?= csrf_field() ?>
      <h2 class="h6 mb-3">Thông tin cá nhân</h2>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" value="<?= e($u['email']) ?>" disabled>
        <div class="form-text">Email dùng để đăng nhập và không thể thay đổi.</div>
      </div>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label req" for="name">Họ tên</label>
          <input class="form-control" id="name" name="name" maxlength="100" required value="<?= e(old('name', $u['name'])) ?>"></div>
        <div class="col-md-6"><label class="form-label req" for="phone">Số điện thoại</label>
          <input class="form-control" id="phone" name="phone" required value="<?= e(old('phone', $u['phone'])) ?>"></div>
        <div class="col-12"><label class="form-label" for="address">Địa chỉ</label>
          <input class="form-control" id="address" name="address" maxlength="255" value="<?= e(old('address', $u['address'])) ?>"></div>
      </div>
      <button class="btn btn-primary mt-3" type="submit">Lưu thay đổi</button>
    </form>
  </div>
  <div class="col-lg-5">
    <form class="lk-card lk-card-pad" action="<?= url('/tai-khoan/mat-khau') ?>" method="post" novalidate><?= csrf_field() ?>
      <h2 class="h6 mb-3">Đổi mật khẩu</h2>
      <div class="mb-3"><label class="form-label" for="cp">Mật khẩu hiện tại</label>
        <input class="form-control" id="cp" type="password" name="current_password" autocomplete="current-password" required></div>
      <div class="mb-3"><label class="form-label" for="np">Mật khẩu mới</label>
        <input class="form-control" id="np" type="password" name="new_password" autocomplete="new-password" required>
        <div class="form-text">Tối thiểu <?= (int)config('rules.min_password') ?> ký tự, gồm chữ và số.</div></div>
      <div class="mb-3"><label class="form-label" for="np2">Nhập lại mật khẩu mới</label>
        <input class="form-control" id="np2" type="password" name="new_password_confirm" autocomplete="new-password" required></div>
      <button class="btn btn-ghost" type="submit">Đổi mật khẩu</button>
    </form>
  </div>
</div>
