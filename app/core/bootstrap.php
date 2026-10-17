<?php
declare(strict_types=1);

/**
 * Khởi động ứng dụng: cấu hình, autoload, session, xử lý lỗi, hàm trợ giúp.
 */

// ---------- Cấu hình (config.php + config.local.php nếu có) ----------
$GLOBALS['LK_CONFIG'] = (function () {
    $cfg = require APP_PATH . '/config/config.php';
    $local = APP_PATH . '/config/config.local.php';
    if (is_file($local)) {
        $cfg = array_replace_recursive($cfg, require $local);
    }
    return $cfg;
})();

function config(string $path, $default = null)
{
    $v = $GLOBALS['LK_CONFIG'];
    foreach (explode('.', $path) as $k) {
        if (!is_array($v) || !array_key_exists($k, $v)) {
            return $default;
        }
        $v = $v[$k];
    }
    return $v;
}

date_default_timezone_set(config('app.timezone', 'Asia/Ho_Chi_Minh'));
mb_internal_encoding('UTF-8');

// ---------- Autoload: tên class -> file trong core / models / controllers ----------
spl_autoload_register(function (string $class) {
    foreach (['core', 'models', 'controllers', 'controllers/admin'] as $dir) {
        $file = APP_PATH . "/$dir/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require APP_PATH . '/core/helpers.php';

// ---------- Xử lý lỗi ----------
set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    error_log('[LangKinh] ' . $e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (config('app.debug')) {
        echo '<pre style="padding:16px;white-space:pre-wrap">' . e($e::class . ': ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n\n" . $e->getTraceAsString()) . '</pre>';
        return;
    }
    Controller::renderError(500, 'Có lỗi xảy ra', 'Hệ thống đang gặp sự cố. Vui lòng thử lại sau ít phút.');
});

// ---------- Session an toàn ----------
session_name('lk_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();
