<?php
declare(strict_types=1);

/** UC13 – Danh mục sản phẩm. */
final class Category
{
    /** Danh mục đang hiển thị (cho menu / bộ lọc của khách). */
    public static function visible(): array
    {
        return Database::all("SELECT * FROM categories WHERE status = 'visible' ORDER BY id");
    }

    public static function all(): array
    {
        return Database::all('SELECT * FROM categories ORDER BY id');
    }

    /** Danh sách kèm số sản phẩm (trang quản trị). */
    public static function withCounts(string $q = ''): array
    {
        $w = '';
        $p = [];
        if ($q !== '') {
            $w = 'WHERE c.name LIKE ?';
            $p[] = '%' . Order::escapeLike($q) . '%';
        }
        return Database::all(
            "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
               FROM categories c $w ORDER BY c.id",
            $p
        );
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public static function save(?int $id, string $name, string $description, string $status): int
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 100) {
            throw new AppException('Tên danh mục là bắt buộc và tối đa 100 ký tự.');
        }
        if (!in_array($status, ['visible', 'hidden'], true)) {
            $status = 'visible';
        }
        if (Database::value('SELECT id FROM categories WHERE name = ? AND id <> ?', [$name, (int)$id])) {
            throw new AppException('Tên danh mục «' . $name . '» đã tồn tại.');
        }
        if ($id) {
            Database::exec('UPDATE categories SET name = ?, description = ?, status = ? WHERE id = ?', [$name, mb_substr($description, 0, 255), $status, $id]);
            return $id;
        }
        return Database::insert('INSERT INTO categories (name, description, status) VALUES (?, ?, ?)', [$name, mb_substr($description, 0, 255), $status]);
    }

    public static function delete(int $id): void
    {
        $n = (int)Database::value('SELECT COUNT(*) FROM products WHERE category_id = ?', [$id]);
        if ($n > 0) {
            throw new AppException("Không thể xóa danh mục đang có $n sản phẩm. Hãy chuyển sản phẩm sang danh mục khác hoặc chuyển danh mục sang «Ẩn».");
        }
        Database::exec('DELETE FROM categories WHERE id = ?', [$id]);
    }
}
