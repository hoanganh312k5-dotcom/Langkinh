<?php /** @var string|null $title */ ?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(isset($title) && $title ? $title . ' – ' : '') ?><?= e(config('shop.name')) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap&subset=vietnamese" rel="stylesheet">
<link href="<?= asset('assets/vendor/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= asset('assets/css/app.css') ?>" rel="stylesheet">
