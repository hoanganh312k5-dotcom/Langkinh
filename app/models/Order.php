<?php
declare(strict_types=1);

/**
 * Đơn hàng – nghiệp vụ trung tâm của hệ thống.
 *
 * Vòng đời:  Chờ xác nhận -> Đã xác nhận -> Đang giao -> Hoàn thành
 *            và có thể chuyển sang "Đã hủy" trước khi Hoàn thành.
 *            "Hoàn thành" và "Đã hủy" là trạng thái cuối, không đi lùi.
 *
 * Tồn kho: TRỪ khi tạo đơn, CỘNG LẠI khi đơn bị hủy.
 * Tên + đơn giá được lưu "đóng băng" trong order_details để sửa giá sau này không làm đổi đơn cũ.
 */
final class Order
{
    public const STATUS = [
        'pending'   => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping'  => 'Đang giao',
        'done'      => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
    public const PILL = [
        'pending' => 'pill-warn', 'confirmed' => 'pill-acc', 'shipping' => 'pill-info',
        'done' => 'pill-ok', 'cancelled' => 'pill-mute',
    ];
    public const NEXT = [
        'pending'   => ['confirmed', 'cancelled'],
        'confirmed' => ['shipping', 'cancelled'],
        'shipping'  => ['done', 'cancelled'],
        'done'      => [],
        'cancelled' => [],
    ];
    public const PAY = [
        'cod'  => 'Thanh toán khi nhận hàng',
        'bank' => 'Chuyển khoản ngân hàng',
    ];

    /* ---------------------------------------------------------------
     * UC09 – Tạo đơn. $cart: [product_id => số lượng]
     * Toàn bộ thao tác nằm trong MỘT transaction:
     * orders + order_details + trừ kho + order_status_logs. Lỗi bước nào thì hoàn tác tất cả.
     * --------------------------------------------------------------- */
    public static function create(int $userId, array $receiver, array $cart): array
    {
        if (!$cart) {
            throw new AppException('Giỏ hàng đang trống.');
        }
        ksort($cart); // khóa dòng theo thứ tự id để tránh deadlock khi nhiều người đặt cùng lúc

        return Database::transaction(function () use ($userId, $receiver, $cart) {
            $lines = [];
            $total = 0;
            foreach ($cart as $pid => $qty) {
                // FOR UPDATE: khóa dòng sản phẩm đến hết transaction -> không bán vượt tồn kho
                $p = Database::one(
                    'SELECT p.id, p.name, p.price, p.stock, p.status, c.status AS cat_status
                       FROM products p JOIN categories c ON c.id = p.category_id
                      WHERE p.id = ? FOR UPDATE',
                    [(int)$pid]
                );
                if (!$p || $p['status'] !== 'visible' || $p['cat_status'] !== 'visible') {
                    throw new AppException('Một sản phẩm trong giỏ không còn được bán. Vui lòng kiểm tra lại giỏ hàng.');
                }
                if ((int)$qty < 1) {
                    throw new AppException('Số lượng không hợp lệ.');
                }
                if ((int)$p['stock'] < (int)$qty) {
                    throw new AppException('«' . $p['name'] . '» chỉ còn ' . $p['stock'] . ' sản phẩm trong kho.');
                }
                $lines[] = ['id' => (int)$p['id'], 'name' => $p['name'], 'price' => (int)$p['price'], 'qty' => (int)$qty];
                $total += (int)$p['price'] * (int)$qty;
            }

            $code = self::nextCode();
            $orderId = Database::insert(
                'INSERT INTO orders (code, user_id, receiver_name, receiver_phone, receiver_address, note, payment_method, total_amount, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, \'pending\')',
                [$code, $userId, $receiver['name'], $receiver['phone'], $receiver['address'], $receiver['note'], $receiver['payment'], $total]
            );

            foreach ($lines as $l) {
                Database::insert(
                    'INSERT INTO order_details (order_id, product_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)',
                    [$orderId, $l['id'], $l['name'], $l['price'], $l['qty']]
                );
                $ok = Database::exec('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?', [$l['qty'], $l['id'], $l['qty']]);
                if ($ok !== 1) {
                    throw new AppException('Không đủ tồn kho cho «' . $l['name'] . '».');
                }
            }

            Database::insert(
                'INSERT INTO order_status_logs (order_id, old_status, new_status, changed_by) VALUES (?, NULL, \'pending\', ?)',
                [$orderId, $userId]
            );
            return ['id' => $orderId, 'code' => $code, 'total' => $total];
        });
    }

    /** Mã đơn duy nhất dạng LKyymmdd-NNN (NNN tăng dần trong ngày). Gọi bên trong transaction. */
    private static function nextCode(): string
    {
        $prefix = 'LK' . date('ymd') . '-';
        $max = Database::value('SELECT MAX(code) FROM orders WHERE code LIKE ? FOR UPDATE', [$prefix . '%']);
        $seq = $max ? ((int)substr((string)$max, -3)) + 1 : 1;
        return $prefix . str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
    }

    /* ---------------- Đọc dữ liệu ---------------- */

    public static function find(int $id): ?array
    {
        return Database::one(
            'SELECT o.*, u.name AS customer_name, u.email AS customer_email
               FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?',
            [$id]
        );
    }

    public static function items(int $orderId): array
    {
        return Database::all(
            'SELECT d.*, (SELECT image_path FROM product_images i WHERE i.product_id = d.product_id
                           ORDER BY i.is_primary DESC, i.sort_order, i.id LIMIT 1) AS image
               FROM order_details d WHERE d.order_id = ? ORDER BY d.id',
            [$orderId]
        );
    }

    public static function logs(int $orderId): array
    {
        return Database::all(
            'SELECT l.*, u.name AS by_name, u.role AS by_role
               FROM order_status_logs l JOIN users u ON u.id = l.changed_by
              WHERE l.order_id = ? ORDER BY l.changed_at, l.id',
            [$orderId]
        );
    }

    /** UC10 – đơn của một khách hàng (có lọc theo trạng thái, phân trang). */
    public static function forUser(int $userId, string $status, int $page, int $per): array
    {
        $where = 'o.user_id = :uid';
        $params = ['uid' => $userId];
        if (isset(self::STATUS[$status])) {
            $where .= ' AND o.status = :st';
            $params['st'] = $status;
        }
        $total = (int)Database::value("SELECT COUNT(*) FROM orders o WHERE $where", $params);
        $pg = paginate($total, $page, $per);
        $rows = Database::all(
            "SELECT o.*, (SELECT COUNT(*) FROM order_details d WHERE d.order_id = o.id) AS line_count,
                    (SELECT COALESCE(SUM(d.quantity),0) FROM order_details d WHERE d.order_id = o.id) AS item_count
               FROM orders o WHERE $where ORDER BY o.created_at DESC, o.id DESC
              LIMIT {$pg['per']} OFFSET {$pg['offset']}",
            $params
        );
        return ['rows' => $rows, 'pg' => $pg];
    }

    /** UC17 – danh sách đơn cho quản trị: tìm kiếm + lọc trạng thái/ngày/thanh toán. */
    public static function adminList(array $f, int $page, int $per): array
    {
        $w = ['1=1'];
        $p = [];
        if (($f['q'] ?? '') !== '') {
            $like = '%' . self::escapeLike($f['q']) . '%';
            $w[] = '(o.code LIKE :q1 OR o.receiver_name LIKE :q2 OR o.receiver_phone LIKE :q3 OR u.email LIKE :q4)';
            $p += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like];
        }
        if (isset(self::STATUS[$f['status'] ?? ''])) {
            $w[] = 'o.status = :st';
            $p['st'] = $f['status'];
        }
        if (isset(self::PAY[$f['payment'] ?? ''])) {
            $w[] = 'o.payment_method = :pay';
            $p['pay'] = $f['payment'];
        }
        if (self::validDate($f['from'] ?? '')) {
            $w[] = 'o.created_at >= :dfrom';
            $p['dfrom'] = $f['from'] . ' 00:00:00';
        }
        if (self::validDate($f['to'] ?? '')) {
            $w[] = 'o.created_at <= :dto';
            $p['dto'] = $f['to'] . ' 23:59:59';
        }
        $where = implode(' AND ', $w);
        $total = (int)Database::value("SELECT COUNT(*) FROM orders o JOIN users u ON u.id = o.user_id WHERE $where", $p);
        $pg = paginate($total, (int)($f['page'] ?? 1), $per);
        $rows = Database::all(
            "SELECT o.*, u.email AS customer_email FROM orders o JOIN users u ON u.id = o.user_id
              WHERE $where ORDER BY o.created_at DESC, o.id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}",
            $p
        );
        return ['rows' => $rows, 'pg' => $pg];
    }

    public static function countByStatus(): array
    {
        $out = array_fill_keys(array_keys(self::STATUS), 0);
        foreach (Database::all('SELECT status, COUNT(*) n FROM orders GROUP BY status') as $r) {
            $out[$r['status']] = (int)$r['n'];
        }
        return $out;
    }

    /* ---------------- Đổi trạng thái ---------------- */

    /** UC18 – quản trị cập nhật trạng thái (chỉ đi tiến theo NEXT; hủy thì hoàn kho). */
    public static function changeStatus(int $orderId, string $new, int $actorId): void
    {
        Database::transaction(function () use ($orderId, $new, $actorId) {
            $o = Database::one('SELECT id, status FROM orders WHERE id = ? FOR UPDATE', [$orderId]);
            if (!$o) {
                throw new AppException('Không tìm thấy đơn hàng.');
            }
            if (!in_array($new, self::NEXT[$o['status']] ?? [], true)) {
                throw new AppException('Không thể chuyển đơn từ «' . self::STATUS[$o['status']] . '» sang «' . (self::STATUS[$new] ?? $new) . '».');
            }
            self::apply($orderId, $o['status'], $new, $actorId);
        });
    }

    /** UC11 – khách tự hủy: chỉ đơn của chính mình và chỉ khi còn "Chờ xác nhận". */
    public static function cancelByCustomer(int $orderId, int $userId): void
    {
        Database::transaction(function () use ($orderId, $userId) {
            $o = Database::one('SELECT id, user_id, status FROM orders WHERE id = ? FOR UPDATE', [$orderId]);
            if (!$o || (int)$o['user_id'] !== $userId) {
                throw new AppException('Không tìm thấy đơn hàng.');
            }
            if ($o['status'] !== 'pending') {
                throw new AppException('Chỉ có thể hủy đơn khi đơn đang ở trạng thái «Chờ xác nhận».');
            }
            self::apply($orderId, 'pending', 'cancelled', $userId);
        });
    }

    private static function apply(int $orderId, string $old, string $new, int $actorId): void
    {
        if ($new === 'cancelled') {
            // hoàn lại tồn kho cho từng sản phẩm của đơn
            foreach (Database::all('SELECT product_id, quantity FROM order_details WHERE order_id = ?', [$orderId]) as $d) {
                Database::exec('UPDATE products SET stock = stock + ? WHERE id = ?', [(int)$d['quantity'], (int)$d['product_id']]);
            }
        }
        Database::exec('UPDATE orders SET status = ? WHERE id = ?', [$new, $orderId]);
        Database::insert(
            'INSERT INTO order_status_logs (order_id, old_status, new_status, changed_by) VALUES (?, ?, ?, ?)',
            [$orderId, $old, $new, $actorId]
        );
    }

    /* ---------------- Tiện ích ---------------- */

    public static function escapeLike(string $s): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $s);
    }

    public static function validDate(string $d): bool
    {
        return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && checkdate((int)substr($d, 5, 2), (int)substr($d, 8, 2), (int)substr($d, 0, 4));
    }
}
