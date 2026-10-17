<div class="auth-wrap" style="margin-top:6vh">
  <div class="text-center mb-3"><span class="brand"><?php require APP_PATH . '/views/partials/logo.php'; ?> <?= e(config('shop.name')) ?></span></div>
  <div class="lk-card lk-card-pad p-4">
    <h1 class="h5 mb-1">Đăng nhập quản trị</h1>
    <p class="text-ink2 small mb-4">Khu vực dành cho quản trị viên cửa hàng.</p>
    <form action="<?= url('/admin/dang-nhap') ?>" method="post" novalidate><?= csrf_field() ?>
      <div class="mb-3"><label class="form-label" for="email">Email</label>
        <input class="form-control" id="email" type="email" name="email" autocomplete="username" required autofocus value="<?= e(old('email')) ?>"></div>
      <div class="mb-4"><label class="form-label" for="pw">Mật khẩu</label>
        <input class="form-control" id="pw" type="password" name="password" autocomplete="current-password" required></div>
      <button class="btn btn-primary w-100" type="submit">Đăng nhập</button>
    </form>
    <p class="text-center small mt-3 mb-0"><a href="<?= url('/') ?>">← Về cửa hàng</a></p>
  </div>
</div>
