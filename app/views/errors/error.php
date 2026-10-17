<?php /** @var int $code */ /** @var string $title */ /** @var string $message */ /** @var bool $isAdmin */ ?>
<div class="text-center py-5">
  <div class="display-1 fw-bold" style="color:var(--lk-accent)"><?= (int)$code ?></div>
  <h1 class="h3 mb-2"><?= e($title) ?></h1>
  <p class="text-ink2 mb-4"><?= e($message) ?></p>
  <a class="btn btn-primary" href="<?= url($isAdmin ? '/admin' : '/') ?>">Về trang chủ</a>
</div>
