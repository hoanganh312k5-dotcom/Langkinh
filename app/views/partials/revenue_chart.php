<?php
/**
 * Biểu đồ cột doanh thu theo ngày – SVG thuần, sinh từ PHP (không cần thư viện JS).
 * @var array $days  [['d'=>'2026-10-01','revenue'=>123,'orders'=>1], ...]
 */
$W = 760; $H = 230; $padL = 56; $padB = 28; $padT = 12; $padR = 8;
$max = max(array_column($days, 'revenue') ?: [0]);
// làm tròn "đẹp" trục Y
$step = 1;
if ($max > 0) {
    $raw = $max / 4;
    $mag = 10 ** floor(log10($raw));
    foreach ([1, 2, 2.5, 5, 10] as $m) { if ($raw <= $m * $mag) { $step = $m * $mag; break; } }
}
$top = $max > 0 ? $step * 4 : 4;
$n = max(1, count($days));
$bw = ($W - $padL - $padR) / $n;
$ph = $H - $padT - $padB;
$fmt = fn($v) => $v >= 1e6 ? rtrim(rtrim(number_format($v / 1e6, 1, ',', '.'), '0'), ',') . ' tr' : ($v >= 1e3 ? number_format($v / 1e3, 0, ',', '.') . ' k' : (string)$v);
?>
<svg class="chart" viewBox="0 0 <?= $W ?> <?= $H ?>" role="img" aria-label="Biểu đồ doanh thu theo ngày">
  <?php for ($i = 0; $i <= 4; $i++): $v = $top / 4 * $i; $y = $padT + $ph - ($v / $top) * $ph; ?>
    <line class="grid" x1="<?= $padL ?>" x2="<?= $W - $padR ?>" y1="<?= round($y, 1) ?>" y2="<?= round($y, 1) ?>"/>
    <text x="<?= $padL - 8 ?>" y="<?= round($y + 4, 1) ?>" text-anchor="end"><?= e($fmt($v)) ?></text>
  <?php endfor; ?>
  <?php $every = max(1, (int)ceil($n / 10));
  foreach ($days as $i => $d):
      $h = $top > 0 ? ($d['revenue'] / $top) * $ph : 0;
      $x = $padL + $i * $bw + $bw * .16; $w = $bw * .68; $y = $padT + $ph - $h; ?>
    <rect class="bar" x="<?= round($x, 1) ?>" y="<?= round($y, 1) ?>" width="<?= round($w, 1) ?>" height="<?= round(max($h, $d['revenue'] > 0 ? 2 : 0), 1) ?>" rx="3">
      <title><?= e(fdate($d['d'])) ?>: <?= e(vnd($d['revenue'])) ?> (<?= (int)$d['orders'] ?> đơn)</title>
    </rect>
    <?php if ($i % $every === 0): ?>
      <text x="<?= round($x + $w / 2, 1) ?>" y="<?= $H - 8 ?>" text-anchor="middle"><?= e(date('d/m', strtotime($d['d']))) ?></text>
    <?php endif; ?>
  <?php endforeach; ?>
</svg>
