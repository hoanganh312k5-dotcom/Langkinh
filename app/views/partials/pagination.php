<?php
/** @var array $pg  (từ paginate()) */
if ($pg['pages'] <= 1) {
    return;
}
$q = query_without(['page']);
$link = fn(int $n) => url(request_path(), $q + ['page' => $n]);
$from = max(1, $pg['page'] - 2);
$to = min($pg['pages'], $pg['page'] + 2);
?>
<nav aria-label="Phân trang" class="mt-4 d-flex justify-content-center">
  <ul class="pagination mb-0">
    <li class="page-item <?= $pg['page'] <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $link($pg['page'] - 1) ?>" aria-label="Trang trước">‹</a></li>
    <?php if ($from > 1): ?>
      <li class="page-item"><a class="page-link" href="<?= $link(1) ?>">1</a></li>
      <?php if ($from > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
    <?php endif; ?>
    <?php for ($i = $from; $i <= $to; $i++): ?>
      <li class="page-item <?= $i === $pg['page'] ? 'active' : '' ?>"><a class="page-link" href="<?= $link($i) ?>"><?= $i ?></a></li>
    <?php endfor; ?>
    <?php if ($to < $pg['pages']): ?>
      <?php if ($to < $pg['pages'] - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
      <li class="page-item"><a class="page-link" href="<?= $link($pg['pages']) ?>"><?= $pg['pages'] ?></a></li>
    <?php endif; ?>
    <li class="page-item <?= $pg['page'] >= $pg['pages'] ? 'disabled' : '' ?>"><a class="page-link" href="<?= $link($pg['page'] + 1) ?>" aria-label="Trang sau">›</a></li>
  </ul>
</nav>
