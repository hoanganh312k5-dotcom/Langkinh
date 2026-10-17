<?php
declare(strict_types=1);

/** Sản phẩm: danh sách/tìm kiếm/lọc/sắp xếp (UC04–UC07) và quản trị (UC15–UC16). */
final class Product
{
    public const MATERIALS = ['Nhựa', 'Acetate', 'TR90', 'Kim loại', 'Titan'];
    public const SHAPES    = ['Vuông', 'Tròn', 'Mắt mèo', 'Phi công', 'Oval'];
    public const COLORS    = ['Đen', 'Nâu', 'Vàng đồng', 'Vàng', 'Bạc', 'Hồng', 'Xanh rêu', 'Đỏ rượu', 'Xám', 'Xanh navy', 'Trong suốt'];

    public const SORTS = [
        'newest'     => 'Mới nhất',
        'price_asc'  => 'Giá tăng dần',
        'price_desc' => 'Giá giảm dần',
        'name'       => 'Tên A → Z',
    ];

    private const SORT_SQL = [
        'newest'     => 'p.created_at DESC, p.id DESC',
        'price_asc'  => 'p.price ASC, p.id DESC',
        'price_desc' => 'p.price DESC, p.id DESC',
        'name'       => 'p.name ASC',
    ];

    private const SELECT = 'SELECT p.*, c.name AS category_name, b.name AS brand_name,
        (SELECT i.image_path FROM product_images i WHERE i.product_id = p.id ORDER BY i.is_primary DESC, i.sort_order, i.id LIMIT 1) AS image
        FROM products p JOIN categories c ON c.id = p.category_id JOIN brands b ON b.id = p.brand_id';

    /**
     * Danh sách có bộ lọc. $admin = true: thấy cả sản phẩm ẩn và danh mục ẩn.
     * Bộ lọc: q, category, brand, shape, material, color, min, max, stock ('in'|'out'|'low'), status, sort.
     */
    public static function listing(array $f, int $page, int $per, bool $admin = false): array
    {
        $w = [];
        $p = [];
        if (!$admin) {
            $w[] = "p.status = 'visible'";
            $w[] = "c.status = 'visible'";
        } elseif (in_array($f['status'] ?? '', ['visible', 'hidden'], true)) {
            $w[] = 'p.status = :status';
            $p['status'] = $f['status'];
        }
        if (($f['q'] ?? '') !== '') {
            // Tách từ khóa theo khoảng trắng: MỖI từ phải khớp ở ít nhất một cột (tên, mã, thương hiệu, màu, kiểu dáng, chất liệu).
            // Collation utf8mb4_unicode_520_ci nên gõ "gong" vẫn tìm ra "Gọng", "den" ra "Đen".
            $words = array_slice(preg_split('/\s+/u', trim($f['q'])), 0, 6);
            foreach ($words as $i => $word) {
                $like = '%' . Order::escapeLike($word) . '%';
                $parts = [];
                foreach (['p.name', 'p.code', 'b.name', 'p.color', 'p.shape', 'p.material'] as $j => $col) {
                    $parts[] = "$col LIKE :w{$i}_{$j}";      // mỗi vị trí một tên tham số riêng (PDO không cho dùng lại)
                    $p["w{$i}_{$j}"] = $like;
                }
                $w[] = '(' . implode(' OR ', $parts) . ')';
            }
        }
        foreach (['category' => 'p.category_id', 'brand' => 'p.brand_id'] as $key => $col) {
            if ((int)($f[$key] ?? 0) > 0) {
                $w[] = "$col = :$key";
                $p[$key] = (int)$f[$key];
            }
        }
        foreach (['shape' => 'p.shape', 'material' => 'p.material', 'color' => 'p.color'] as $key => $col) {
            if (($f[$key] ?? '') !== '') {
                $w[] = "$col = :$key";
                $p[$key] = $f[$key];
            }
        }
        if (($f['min'] ?? '') !== '' && ctype_digit((string)$f['min'])) {
            $w[] = 'p.price >= :pmin';
            $p['pmin'] = (int)$f['min'];
        }
        if (($f['max'] ?? '') !== '' && ctype_digit((string)$f['max'])) {
            $w[] = 'p.price <= :pmax';
            $p['pmax'] = (int)$f['max'];
        }
        $stock = $f['stock'] ?? '';
        if ($stock === 'in') {
            $w[] = 'p.stock > 0';
        } elseif ($stock === 'out') {
            $w[] = 'p.stock = 0';
        } elseif ($stock === 'low') {
            $w[] = 'p.stock > 0 AND p.stock <= ' . (int)config('rules.low_stock');
        }

        $where = $w ? ' WHERE ' . implode(' AND ', $w) : '';
        $order = self::SORT_SQL[$f['sort'] ?? 'newest'] ?? self::SORT_SQL['newest'];

        $total = (int)Database::value(
            'SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id JOIN brands b ON b.id = p.brand_id' . $where,
            $p
        );
        $pg = paginate($total, $page, $per);
        $rows = Database::all(self::SELECT . $where . " ORDER BY $order LIMIT {$pg['per']} OFFSET {$pg['offset']}", $p);
        return ['rows' => $rows, 'pg' => $pg];
    }

    /** UC07 – chi tiết. Khách chỉ xem được sản phẩm đang hiển thị. */
    public static function find(int $id, bool $admin = false): ?array
    {
        $sql = self::SELECT . ' WHERE p.id = ?' . ($admin ? '' : " AND p.status = 'visible' AND c.status = 'visible'");
        return Database::one($sql, [$id]);
    }

    public static function images(int $productId): array
    {
        return Database::all('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order, id', [$productId]);
    }

    /** Sản phẩm liên quan: cùng danh mục (ưu tiên cùng thương hiệu). */
    public static function related(array $product, int $limit = 4): array
    {
        return Database::all(
            self::SELECT . " WHERE p.status = 'visible' AND c.status = 'visible' AND p.category_id = :c AND p.id <> :id
              ORDER BY (p.brand_id = :b) DESC, p.created_at DESC LIMIT " . (int)$limit,
            ['c' => $product['category_id'], 'id' => $product['id'], 'b' => $product['brand_id']]
        );
    }

    public static function newest(int $limit = 8): array
    {
        return self::listing(['sort' => 'newest'], 1, $limit)['rows'];
    }

    /** Bán chạy: xếp theo tổng số lượng bán trong các đơn đã Hoàn thành. */
    public static function bestSellers(int $limit = 4): array
    {
        return Database::all(
            "SELECT p.*, c.name AS category_name, b.name AS brand_name,
                    (SELECT i.image_path FROM product_images i WHERE i.product_id = p.id ORDER BY i.is_primary DESC, i.sort_order, i.id LIMIT 1) AS image,
                    SUM(d.quantity) AS sold
               FROM order_details d
               JOIN orders o ON o.id = d.order_id AND o.status = 'done'
               JOIN products p ON p.id = d.product_id
               JOIN categories c ON c.id = p.category_id
               JOIN brands b ON b.id = p.brand_id
              WHERE p.status = 'visible' AND c.status = 'visible'
              GROUP BY p.id ORDER BY sold DESC, p.id LIMIT " . (int)$limit
        );
    }

    /** Giá trị có thật trong CSDL cho bộ lọc (chỉ sản phẩm đang bán). */
    public static function facet(string $column): array
    {
        if (!in_array($column, ['shape', 'material', 'color'], true)) {
            return [];
        }
        return array_column(Database::all(
            "SELECT DISTINCT p.$column AS v FROM products p JOIN categories c ON c.id = p.category_id
              WHERE p.status = 'visible' AND c.status = 'visible' AND p.$column IS NOT NULL AND p.$column <> '' ORDER BY v"
        ), 'v');
    }

    /* ---------------- Quản trị ---------------- */

    public static function codeExists(string $code, int $exceptId = 0): bool
    {
        return (bool)Database::value('SELECT id FROM products WHERE code = ? AND id <> ?', [$code, $exceptId]);
    }

    public static function create(array $d): int
    {
        return Database::insert(
            'INSERT INTO products (code, name, category_id, brand_id, price, stock, material, shape, color, size, description, status)
             VALUES (:code, :name, :category_id, :brand_id, :price, :stock, :material, :shape, :color, :size, :description, :status)',
            self::bind($d)
        );
    }

    public static function update(int $id, array $d): void
    {
        Database::exec(
            'UPDATE products SET code=:code, name=:name, category_id=:category_id, brand_id=:brand_id, price=:price, stock=:stock,
                    material=:material, shape=:shape, color=:color, size=:size, description=:description, status=:status
              WHERE id = :id',
            self::bind($d) + ['id' => $id]
        );
    }

    private static function bind(array $d): array
    {
        $nul = fn($v) => ($v === '' || $v === null) ? null : $v;
        return [
            'code' => $d['code'], 'name' => $d['name'],
            'category_id' => (int)$d['category_id'], 'brand_id' => (int)$d['brand_id'],
            'price' => (int)$d['price'], 'stock' => (int)$d['stock'],
            'material' => $nul($d['material']), 'shape' => $nul($d['shape']), 'color' => $nul($d['color']),
            'size' => $nul($d['size']), 'description' => $nul($d['description']), 'status' => $d['status'],
        ];
    }

    /** Sản phẩm đã nằm trong đơn hàng thì không được xóa (chỉ được ẩn) để giữ lịch sử. */
    public static function inOrders(int $id): bool
    {
        return (bool)Database::value('SELECT id FROM order_details WHERE product_id = ? LIMIT 1', [$id]);
    }

    public static function delete(int $id): array
    {
        if (self::inOrders($id)) {
            throw new AppException('Sản phẩm đã xuất hiện trong đơn hàng nên không thể xóa. Hãy chuyển sang trạng thái «Ẩn» để ngừng bán.');
        }
        $files = array_column(self::images($id), 'image_path');
        Database::exec('DELETE FROM products WHERE id = ?', [$id]);   // ảnh bị xóa theo (ON DELETE CASCADE)
        return $files;
    }

    public static function setStock(int $id, int $stock): void
    {
        if ($stock < 0) {
            throw new AppException('Tồn kho không được âm.');
        }
        Database::exec('UPDATE products SET stock = ? WHERE id = ?', [$stock, $id]);
    }

    public static function adjustStock(int $id, int $delta): void
    {
        $affected = Database::exec('UPDATE products SET stock = stock + ? WHERE id = ? AND CAST(stock AS SIGNED) + ? >= 0', [$delta, $id, $delta]);
        if ($affected !== 1) {
            throw new AppException('Không thể trừ quá số lượng đang tồn kho.');
        }
    }

    public static function lowStockCount(): int
    {
        return (int)Database::value('SELECT COUNT(*) FROM products WHERE stock <= ?', [(int)config('rules.low_stock')]);
    }

    /* ---------------- Ảnh ---------------- */

    public static function addImage(int $productId, string $file): void
    {
        $has = (int)Database::value('SELECT COUNT(*) FROM product_images WHERE product_id = ?', [$productId]);
        $sort = (int)Database::value('SELECT COALESCE(MAX(sort_order),0) + 1 FROM product_images WHERE product_id = ?', [$productId]);
        Database::insert(
            'INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)',
            [$productId, $file, $has === 0 ? 1 : 0, $sort]
        );
    }

    public static function deleteImage(int $productId, int $imageId): ?string
    {
        $img = Database::one('SELECT * FROM product_images WHERE id = ? AND product_id = ?', [$imageId, $productId]);
        if (!$img) {
            return null;
        }
        Database::exec('DELETE FROM product_images WHERE id = ?', [$imageId]);
        if ((int)$img['is_primary'] === 1) {   // ảnh đại diện bị xóa -> chọn ảnh đầu tiên còn lại
            Database::exec('UPDATE product_images SET is_primary = 1 WHERE product_id = ? ORDER BY sort_order, id LIMIT 1', [$productId]);
        }
        return $img['image_path'];
    }

    public static function setPrimaryImage(int $productId, int $imageId): void
    {
        Database::transaction(function () use ($productId, $imageId) {
            if (!Database::value('SELECT id FROM product_images WHERE id = ? AND product_id = ?', [$imageId, $productId])) {
                throw new AppException('Không tìm thấy ảnh.');
            }
            Database::exec('UPDATE product_images SET is_primary = 0 WHERE product_id = ?', [$productId]);
            Database::exec('UPDATE product_images SET is_primary = 1 WHERE id = ?', [$imageId]);
        });
    }
}
