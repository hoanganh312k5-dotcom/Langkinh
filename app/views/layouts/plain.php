<?php /** @var string $content */ /** @var array $flashes */ ?>
<!doctype html>
<html lang="vi">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body>
  <div class="container">
    <div class="no-print pt-3"><?php require APP_PATH . '/views/partials/flash.php'; ?></div>
    <?= $content ?>
  </div>
  <?php require APP_PATH . '/views/partials/scripts.php'; ?>
</body>
</html>
