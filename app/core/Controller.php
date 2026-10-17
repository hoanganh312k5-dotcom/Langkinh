<?php
declare(strict_types=1);

/**
 * Controller cơ sở: render view, trả về lỗi, đọc/ghi form.
 * View nằm trong app/views/<tên>.php và được bọc bởi layout (app/views/layouts/<layout>.php).
 */
abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $content = self::capture(APP_PATH . '/views/' . $view . '.php', $data);
        $data['content'] = $content;
        $data['flashes'] = flashes();
        echo self::capture(APP_PATH . '/views/layouts/' . $layout . '.php', $data);
        clear_old();
    }

    private static function capture(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $file;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string)ob_get_clean();
    }

    public static function renderError(int $code, string $title, string $message): void
    {
        http_response_code($code);
        $isAdmin = str_starts_with(request_path(), '/admin') && Auth::isAdmin();
        $content = self::capture(APP_PATH . '/views/errors/error.php', compact('code', 'title', 'message', 'isAdmin'));
        echo self::capture(APP_PATH . '/views/layouts/' . ($isAdmin ? 'admin' : 'main') . '.php', [
            'content' => $content, 'flashes' => flashes(), 'title' => $title,
        ]);
    }

    protected function back(string $fallback = '/'): never
    {
        redirect($fallback);
    }

    /** Lưu dữ liệu form + thông báo lỗi rồi quay lại trang form. */
    protected function failWith(string $path, string $message, ?array $keep = null): never
    {
        flash('danger', $message);
        set_old($keep ?? $_POST);
        redirect($path);
    }

    protected function notFound(): never
    {
        self::renderError(404, 'Không tìm thấy', 'Nội dung bạn tìm không tồn tại hoặc đã bị gỡ.');
        exit;
    }
}
