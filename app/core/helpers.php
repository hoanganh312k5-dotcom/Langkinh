<?php
declare(strict_types=1);

/* ============================================================
 * Hàm trợ giúp dùng trong toàn bộ ứng dụng / view
 * ============================================================ */

/** Escape HTML – luôn dùng khi in dữ liệu ra view (chống XSS). */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Đường dẫn gốc của site (vd. "/langkinh/public" hoặc ""), tự nhận diện. */
function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $cfg = config('app.base_path');
    if ($cfg !== null) {
        return $base = rtrim((string)$cfg, '/');
    }
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $pub = realpath(PUBLIC_PATH);
    if ($docRoot && $pub && strpos($pub, $docRoot) === 0) {
        $base = str_replace('\\', '/', substr($pub, strlen($docRoot)));
    } else {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    }
    return $base = rtrim($base, '/');
}

/** Đường dẫn của request hiện tại, đã bỏ phần base_path. */
function request_path(): string
{
    $path = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
    $base = base_path();
    if ($base !== '' && strpos($path, $base) === 0) {
        $path = substr($path, strlen($base));
    }
    if (strpos($path, '/index.php') === 0) {
        $path = substr($path, strlen('/index.php'));
    }
    $path = '/' . trim($path, '/');
    return $path;
}

/** URL tuyệt đối-theo-gốc tới một route: url('/san-pham', ['q' => 'gong']) */
function url(string $path = '/', array $query = []): string
{
    $u = base_path() . '/' . ltrim($path, '/');
    $u = rtrim($u, '/') === '' ? '/' : $u;
    if ($query) {
        $query = array_filter($query, fn($v) => $v !== '' && $v !== null);
        if ($query) {
            $u .= '?' . http_build_query($query);
        }
    }
    return $u;
}

/** URL tới file tĩnh trong public/ (css, js, ảnh). */
function asset(string $path): string
{
    return base_path() . '/' . ltrim($path, '/');
}

/** URL ảnh sản phẩm (file lưu trong public/uploads/products). */
function product_img(?string $file): string
{
    return $file ? asset(config('upload.dir') . '/' . $file) : asset('assets/img/no-image.svg');
}

function redirect(string $path, array $query = []): never
{
    header('Location: ' . url($path, $query));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/* ---------------- Định dạng hiển thị ---------------- */

function vnd($n): string
{
    return number_format((float)$n, 0, ',', '.') . ' ₫';
}

function fdate(?string $dt): string
{
    return $dt ? date('d/m/Y', strtotime($dt)) : '';
}

function fdatetime(?string $dt): string
{
    return $dt ? date('d/m/Y H:i', strtotime($dt)) : '';
}

/* ---------------- Flash message & dữ liệu form cũ ---------------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $message];
}

function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function set_old(array $data): void
{
    unset($data['password'], $data['password_confirm'], $data['current_password'], $data['new_password'], $data['_csrf']);
    $_SESSION['old'] = $data;
}

function old(string $key, $default = '')
{
    return $_SESSION['old'][$key] ?? $default;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

/* ---------------- CSRF ---------------- */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = $_POST['_csrf'] ?? '';
    return is_string($sent) && $sent !== '' && hash_equals($_SESSION['_csrf'] ?? '', $sent);
}

/* ---------------- Đọc input an toàn ---------------- */

function input(string $key, $default = ''): string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_array($v) ? '' : trim((string)$v);
}

function input_int(string $key, int $default = 0): int
{
    $v = $_POST[$key] ?? $_GET[$key] ?? null;
    return (is_string($v) || is_int($v)) && preg_match('/^-?\d+$/', trim((string)$v)) ? (int)$v : $default;
}

/* ---------------- Nhãn trạng thái ---------------- */

function status_pill(string $status): string
{
    $cls = Order::PILL[$status] ?? 'pill-mute';
    return '<span class="pill ' . $cls . '">' . e(Order::STATUS[$status] ?? $status) . '</span>';
}

/** Phân trang: trả về mảng thông tin để partial pagination dùng. */
function paginate(int $total, int $page, int $perPage): array
{
    $pages = max(1, (int)ceil($total / $perPage));
    $page = min(max(1, $page), $pages);
    return ['total' => $total, 'page' => $page, 'pages' => $pages, 'per' => $perPage, 'offset' => ($page - 1) * $perPage];
}

/** Chuỗi query hiện tại, bỏ một số khóa (dùng để giữ bộ lọc khi chuyển trang). */
function query_without(array $drop = ['page']): array
{
    $q = $_GET;
    foreach ($drop as $k) {
        unset($q[$k]);
    }
    return array_filter($q, fn($v) => !is_array($v) && $v !== '');
}
