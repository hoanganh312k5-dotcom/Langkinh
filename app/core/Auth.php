<?php
declare(strict_types=1);

/**
 * Đăng nhập / phân quyền dựa trên session.
 * Session chỉ lưu id người dùng; thông tin (vai trò, trạng thái khóa) luôn đọc lại từ DB
 * nên khi admin khóa tài khoản, hiệu lực ngay ở request kế tiếp.
 */
final class Auth
{
    private static ?array $cache = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = $_SESSION['uid'] ?? null;
            if ($id) {
                $u = Database::one('SELECT id, name, email, phone, address, role, status FROM users WHERE id = ?', [(int)$id]);
                if ($u && $u['status'] === 'active') {
                    self::$cache = $u;
                } else {
                    unset($_SESSION['uid']);   // tài khoản bị khóa/xóa -> đăng xuất
                }
            }
        }
        return self::$cache;
    }

    public static function check(): bool   { return self::user() !== null; }
    public static function id(): ?int      { return self::user()['id'] ?? null; }
    public static function isAdmin(): bool { return (self::user()['role'] ?? '') === 'admin'; }

    public static function login(array $user): void
    {
        session_regenerate_id(true);       // chống session fixation; giỏ hàng khách (trong session) được giữ nguyên
        $_SESSION['uid'] = (int)$user['id'];
        self::$loaded = false;
        self::$cache = null;
    }

    public static function logout(): void
    {
        unset($_SESSION['uid']);
        session_regenerate_id(true);
        self::$loaded = false;
        self::$cache = null;
    }

    /**
     * Thử đăng nhập. Trả về mảng user nếu đúng, ném AppException nếu sai.
     * $role: 'customer' (trang khách) hoặc 'admin' (trang quản trị).
     */
    public static function attempt(string $email, string $password, string $role): array
    {
        // Chặn dò mật khẩu: sai 5 lần -> khóa 60 giây
        $t = $_SESSION['login_fail'] ?? ['n' => 0, 'until' => 0];
        if ($t['until'] > time()) {
            throw new AppException('Bạn nhập sai quá nhiều lần. Vui lòng thử lại sau ' . ($t['until'] - time()) . ' giây.');
        }

        $u = Database::one('SELECT * FROM users WHERE email = ?', [mb_strtolower($email)]);
        $ok = $u && $u['role'] === $role && password_verify($password, $u['password_hash']);

        if (!$ok) {
            $t['n']++;
            if ($t['n'] >= 5) {
                $t = ['n' => 0, 'until' => time() + 60];
            }
            $_SESSION['login_fail'] = $t;
            // Thông báo chung – không tiết lộ email có tồn tại hay không
            throw new AppException('Email hoặc mật khẩu không đúng.');
        }
        if ($u['status'] !== 'active') {
            throw new AppException('Tài khoản của bạn đã bị khóa. Vui lòng liên hệ cửa hàng để được hỗ trợ.');
        }
        unset($_SESSION['login_fail']);
        return $u;
    }
}
