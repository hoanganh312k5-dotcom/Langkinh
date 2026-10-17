<?php
/** @var array $flashes */
$map = ['success' => 'alert-success auto-hide', 'danger' => 'alert-danger', 'warn' => 'alert-warn', 'info' => 'alert-info'];
foreach ($flashes as $f): ?>
  <div class="alert <?= $map[$f['type']] ?? 'alert-info' ?> mb-3" role="alert"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
