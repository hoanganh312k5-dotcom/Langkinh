<?php
declare(strict_types=1);

/** Người dùng: đăng ký (UC01), hồ sơ (UC03), quản lý khách hàng (UC20). */
final class User
{
    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function emailExists(string $email, int $exceptId = 0): bool
    {
        return (bool)Database::value('SELECT id FROM users WHERE email = ? AND id <> ?', [mb_strtolower($email), $exceptId]);
    }

    /** Kiểm tra mật khẩu mới theo quy tắc: >= 8 ký tự, có chữ và số. Trả về chuỗi lỗi hoặc null. */
    public static function passwordError(string $pw): ?string
    {
        $min = (int)config('rules.min_password');
        if (mb_strlen($pw) < $min) {
            return "Mật khẩu phải có ít nhất $min ký tự.";
        }
        if (!preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw)) {
            return 'Mật khẩu phải gồm cả chữ và số.';
        }
        return null;
    }

    public static function phoneValid(string $phone): bool
    {
        return (bool)preg_match('/^(0|\+84)\d{9,10}$/', preg_replace('/[\s.\-]/', '', $phone));
    }

    public static function register(string $name, string $email, string $phone, string $address, string $password): int
    {
        return Database::insert(
            "INSERT INTO users (name, email, phone, address, password_hash, role, status) VALUES (?, ?, ?, ?, ?, 'customer', 'active')",
            [$name, mb_strtolower($email), $phone, $address, password_hash($password, PASSWORD_DEFAULT)]
        );
    }

    public static function updateProfile(int $id, string $name, string $phone, string $address): void
    {
        Database::exec('UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?', [$name, $phone, $address, $id]);
    }

    public static function changePassword(int $id, string $current, string $new): void
    {
        $u = self::find($id);
        if (!$u || !password_verify($current, $u['password_hash'])) {
            throw new AppException('Mật khẩu hiện tại không đúng.');
        }
        if ($err = self::passwordError($new)) {
            throw new AppException($err);
        }
        Database::exec('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $id]);
    }

    /* ---------------- Quản trị khách hàng ---------------- */

    public static function adminList(array $f, int $page, int $per): array
    {
        $w = ["u.role = 'customer'"];
        $p = [];
        if (($f['q'] ?? '') !== '') {
            $like = '%' . Order::escapeLike($f['q']) . '%';
            $w[] = '(u.name LIKE :q1 OR u.email LIKE :q2 OR u.phone LIKE :q3)';
            $p += ['q1' => $like, 'q2' => $like, 'q3' => $like];
        }
        if (in_array($f['status'] ?? '', ['active', 'locked'], true)) {
            $w[] = 'u.status = :st';
            $p['st'] = $f['status'];
        }
        $where = implode(' AND ', $w);
        $total = (int)Database::value("SELECT COUNT(*) FROM users u WHERE $where", $p);
        $pg = paginate($total, (int)($f['page'] ?? 1), $per);
        $rows = Database::all(
            "SELECT u.*, COUNT(o.id) AS order_count,
                    COALESCE(SUM(CASE WHEN o.status = 'done' THEN o.total_amount END), 0) AS spent
               FROM users u LEFT JOIN orders o ON o.user_id = u.id
              WHERE $where GROUP BY u.id ORDER BY u.created_at DESC, u.id DESC
              LIMIT {$pg['per']} OFFSET {$pg['offset']}",
            $p
        );
        return ['rows' => $rows, 'pg' => $pg];
    }

    public static function orderCount(int $id): int
    {
        return (int)Database::value('SELECT COUNT(*) FROM orders WHERE user_id = ?', [$id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        Database::exec("UPDATE users SET status = ? WHERE id = ? AND role = 'customer'", [$status, $id]);
    }

    /** Chỉ xóa được khách chưa có đơn nào; ngược lại chỉ khóa. */
    public static function delete(int $id): void
    {
        if (self::orderCount($id) > 0) {
            throw new AppException('Khách hàng đã có đơn hàng nên không thể xóa, chỉ có thể khóa tài khoản.');
        }
        Database::exec("DELETE FROM users WHERE id = ? AND role = 'customer'", [$id]);
    }
}
