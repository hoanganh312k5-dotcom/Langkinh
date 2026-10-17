<div class="auth-wrap">
  <div class="lk-card lk-card-pad p-4">
    <h1 class="h4 mb-1">Đăng nhập</h1>
    <p class="text-ink2 small mb-4">Chào mừng bạn quay lại <?= e(config('shop.name')) ?>.</p>
    <form action="<?= url('/dang-nhap') ?>" method="post" novalidate><?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control" id="email" type="email" name="email" autocomplete="username" required autofocus value="<?= e(old('email')) ?>">
      </div>
      <div class="mb-4">
        <label class="form-label" for="pw">Mật khẩu</label>
        <input class="form-control" id="pw" type="password" name="password" autocomplete="current-password" required>
      </div>
      <button class="btn btn-primary w-100" type="submit">Đăng nhập</button>
    </form>
    <p class="text-center small mt-3 mb-0">Chưa có tài khoản? <a href="<?= url('/dang-ky') ?>">Đăng ký ngay</a></p>
  </div>
</div>
