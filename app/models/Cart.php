<?php
declare(strict_types=1);

/**
 * Giỏ hàng lưu trong SESSION: $_SESSION['cart'] = [product_id => số lượng].
 * Khách vãng lai dùng được giỏ; đăng nhập xong giỏ vẫn được giữ (session không bị xóa).
 * Giá và tồn kho luôn đọc mới từ DB mỗi lần hiển thị.
 */
final class Cart
{
    private static function raw(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    public static function count(): int
    {
        return (int)array_sum(self::raw());
    }

    public static function isEmpty(): bool
    {
        return !self::raw();
    }

    public static function raws(): array
    {
        return self::raw();
    }

    /** Thêm sản phẩm; số lượng bị chặn bởi tồn kho và giới hạn mỗi dòng. */
    public static function add(int $productId, int $qty): void
    {
        $p = Product::find($productId);
        if (!$p) {
            throw new AppException('Sản phẩm không tồn tại hoặc đã ngừng bán.');
        }
        if ((int)$p['stock'] < 1) {
            throw new AppException('«' . $p['name'] . '» hiện đã hết hàng.');
        }
        $cart = self::raw();
        $max = min((int)$p['stock'], (int)config('rules.max_qty_per_line'));
        $want = ($cart[$productId] ?? 0) + max(1, $qty);
        if ($want > $max) {
            $cart[$productId] = $max;
            $_SESSION['cart'] = $cart;
            throw new AppException('Đã đưa vào giỏ tối đa ' . $max . ' sản phẩm «' . $p['name'] . '» (theo tồn kho/giới hạn mỗi dòng).');
        }
        $cart[$productId] = $want;
        $_SESSION['cart'] = $cart;
    }

    public static function setQty(int $productId, int $qty): ?string
    {
        $cart = self::raw();
        if (!isset($cart[$productId])) {
            return null;
        }
        if ($qty <= 0) {
            unset($cart[$productId]);
            $_SESSION['cart'] = $cart;
            return null;
        }
        $p = Product::find($productId);
        $note = null;
        if (!$p) {
            unset($cart[$productId]);
            $_SESSION['cart'] = $cart;
            return 'Một sản phẩm đã ngừng bán và được gỡ khỏi giỏ.';
        }
        $max = min((int)$p['stock'], (int)config('rules.max_qty_per_line'));
        if ($qty > $max) {
            $qty = $max;
            $note = 'Số lượng «' . $p['name'] . '» được điều chỉnh về tối đa ' . $max . '.';
        }
        $cart[$productId] = max(1, $qty);
        $_SESSION['cart'] = $cart;
        return $note;
    }

    public static function remove(int $productId): void
    {
        $cart = self::raw();
        unset($cart[$productId]);
        $_SESSION['cart'] = $cart;
    }

    public static function clear(): void
    {
        unset($_SESSION['cart']);
    }

    /**
     * Các dòng giỏ hàng kèm thông tin mới nhất từ DB.
     * Mỗi dòng có 'problem' (null | chuỗi) nếu hết hàng / không đủ hàng / ngừng bán.
     */
    public static function lines(): array
    {
        $out = [];
        foreach (self::raw() as $pid => $qty) {
            $p = Product::find((int)$pid);
            if (!$p) {
                $out[] = ['id' => (int)$pid, 'product' => null, 'qty' => (int)$qty, 'total' => 0,
                          'problem' => 'Sản phẩm đã ngừng bán.'];
                continue;
            }
            $problem = null;
            if ((int)$p['stock'] < 1) {
                $problem = 'Đã hết hàng.';
            } elseif ((int)$p['stock'] < (int)$qty) {
                $problem = 'Chỉ còn ' . $p['stock'] . ' sản phẩm trong kho.';
            }
            $out[] = ['id' => (int)$pid, 'product' => $p, 'qty' => (int)$qty,
                      'total' => (int)$p['price'] * (int)$qty, 'problem' => $problem];
        }
        return $out;
    }

    public static function total(array $lines): int
    {
        return (int)array_sum(array_column($lines, 'total'));
    }

    public static function hasProblem(array $lines): bool
    {
        foreach ($lines as $l) {
            if ($l['problem']) {
                return true;
            }
        }
        return false;
    }
}
