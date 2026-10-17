<?php
declare(strict_types=1);

/**
 * UC21 – Thống kê. Quy tắc:
 *  - Doanh thu chỉ tính đơn "Hoàn thành".
 *  - Sản phẩm bán chạy xếp theo tổng số lượng bán (trong đơn Hoàn thành).
 */
final class Stats
{
    /** Thẻ tổng quan trong khoảng [from, to] (yyyy-mm-dd). */
    public static function summary(string $from, string $to): array
    {
        $r = ['f' => $from . ' 00:00:00', 't' => $to . ' 23:59:59'];
        return [
            'revenue' => (int)Database::value("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status='done' AND created_at BETWEEN :f AND :t", $r),
            'orders'  => (int)Database::value('SELECT COUNT(*) FROM orders WHERE created_at BETWEEN :f AND :t', $r),
            'done'    => (int)Database::value("SELECT COUNT(*) FROM orders WHERE status='done' AND created_at BETWEEN :f AND :t", $r),
            'cancelled' => (int)Database::value("SELECT COUNT(*) FROM orders WHERE status='cancelled' AND created_at BETWEEN :f AND :t", $r),
            'new_customers' => (int)Database::value("SELECT COUNT(*) FROM users WHERE role='customer' AND created_at BETWEEN :f AND :t", $r),
        ];
    }

    /** Doanh thu theo từng ngày (đủ cả những ngày không có đơn). */
    public static function revenueByDay(string $from, string $to): array
    {
        $rows = Database::all(
            "SELECT DATE(created_at) AS d, SUM(total_amount) AS revenue, COUNT(*) AS orders
               FROM orders WHERE status='done' AND created_at BETWEEN :f AND :t GROUP BY DATE(created_at)",
            ['f' => $from . ' 00:00:00', 't' => $to . ' 23:59:59']
        );
        $map = [];
        foreach ($rows as $r) {
            $map[$r['d']] = $r;
        }
        $out = [];
        for ($d = new DateTimeImmutable($from), $end = new DateTimeImmutable($to); $d <= $end; $d = $d->modify('+1 day')) {
            $k = $d->format('Y-m-d');
            $out[] = ['d' => $k, 'revenue' => (int)($map[$k]['revenue'] ?? 0), 'orders' => (int)($map[$k]['orders'] ?? 0)];
        }
        return $out;
    }

    public static function topProducts(string $from, string $to, int $limit = 5): array
    {
        return Database::all(
            "SELECT d.product_id, d.product_name, SUM(d.quantity) AS qty, SUM(d.quantity * d.unit_price) AS revenue
               FROM order_details d JOIN orders o ON o.id = d.order_id
              WHERE o.status = 'done' AND o.created_at BETWEEN :f AND :t
              GROUP BY d.product_id, d.product_name ORDER BY qty DESC, revenue DESC LIMIT " . (int)$limit,
            ['f' => $from . ' 00:00:00', 't' => $to . ' 23:59:59']
        );
    }

    public static function statusBreakdown(string $from, string $to): array
    {
        $out = array_fill_keys(array_keys(Order::STATUS), 0);
        $rows = Database::all(
            'SELECT status, COUNT(*) n FROM orders WHERE created_at BETWEEN :f AND :t GROUP BY status',
            ['f' => $from . ' 00:00:00', 't' => $to . ' 23:59:59']
        );
        foreach ($rows as $r) {
            $out[$r['status']] = (int)$r['n'];
        }
        return $out;
    }

    public static function lowStock(int $limit = 6): array
    {
        return Database::all(
            'SELECT id, code, name, stock FROM products WHERE stock <= ? ORDER BY stock, name LIMIT ' . (int)$limit,
            [(int)config('rules.low_stock')]
        );
    }

    public static function recentOrders(int $limit = 6): array
    {
        return Database::all('SELECT * FROM orders ORDER BY created_at DESC, id DESC LIMIT ' . (int)$limit);
    }
}
