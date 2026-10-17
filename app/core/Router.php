<?php
declare(strict_types=1);

/**
 * Router đơn giản: ghép "METHOD + đường dẫn" với "Controller@action".
 * Tham số {id} khớp với số nguyên. Middleware: 'auth' (khách đã đăng nhập),
 * 'admin' (quản trị), 'guest' (chưa đăng nhập).
 */
final class Router
{
    private array $routes = [];

    public function get(string $pattern, string $handler, array $mw = []): void
    {
        $this->add('GET', $pattern, $handler, $mw);
    }

    public function post(string $pattern, string $handler, array $mw = []): void
    {
        $this->add('POST', $pattern, $handler, $mw);
    }

    private function add(string $method, string $pattern, string $handler, array $mw): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $pattern) . '$#u';
        $this->routes[] = compact('method', 'regex', 'handler', 'mw');
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $pathMatched = false;

        foreach ($this->routes as $r) {
            if (!preg_match($r['regex'], $path, $m)) {
                continue;
            }
            $pathMatched = true;
            if ($r['method'] !== $method) {
                continue;
            }

            // Mọi POST đều phải có CSRF token hợp lệ
            if ($method === 'POST' && !csrf_valid()) {
                Controller::renderError(419, 'Phiên làm việc hết hạn', 'Biểu mẫu không hợp lệ hoặc đã hết hạn. Vui lòng tải lại trang và thử lại.');
                return;
            }

            foreach ($r['mw'] as $mw) {
                $this->runMiddleware($mw);
            }

            [$class, $action] = explode('@', $r['handler']);
            $params = array_map('intval', array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY));
            (new $class())->$action(...array_values($params));
            return;
        }

        if ($pathMatched) {
            Controller::renderError(405, 'Phương thức không được hỗ trợ', 'Yêu cầu này không hợp lệ.');
            return;
        }
        Controller::renderError(404, 'Không tìm thấy trang', 'Đường dẫn bạn truy cập không tồn tại hoặc đã bị di chuyển.');
    }

    private function runMiddleware(string $mw): void
    {
        $isAdminArea = str_starts_with(request_path(), '/admin');
        switch ($mw) {
            case 'auth':      // cần đăng nhập (khách hàng)
                if (!Auth::check() || Auth::isAdmin()) {
                    flash('warn', 'Vui lòng đăng nhập để tiếp tục.');
                    $_SESSION['intended'] = $_SERVER['REQUEST_METHOD'] === 'GET' ? request_path() : '/gio-hang';
                    redirect('/dang-nhap');
                }
                break;
            case 'admin':     // cần quyền quản trị
                if (!Auth::check()) {
                    flash('warn', 'Vui lòng đăng nhập quản trị.');
                    redirect('/admin/dang-nhap');
                }
                if (!Auth::isAdmin()) {
                    Controller::renderError(403, 'Không có quyền truy cập', 'Khu vực này chỉ dành cho quản trị viên.');
                    exit;
                }
                break;
            case 'guest':     // chỉ cho người chưa đăng nhập
                if (Auth::check()) {
                    redirect(Auth::isAdmin() ? '/admin' : '/');
                }
                break;
        }
    }
}
