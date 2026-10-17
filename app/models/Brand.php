<?php
declare(strict_types=1);

/** UC14 – Thương hiệu. */
final class Brand
{
    public static function all(): array
    {
        return Database::all('SELECT * FROM brands ORDER BY name');
    }

    public static function withCounts(string $q = ''): array
    {
        $w = '';
        $p = [];
        if ($q !== '') {
            $w = 'WHERE b.name LIKE ?';
            $p[] = '%' . Order::escapeLike($q) . '%';
        }
        return Database::all(
            "SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id) AS product_count
               FROM brands b $w ORDER BY b.id",
            $p
        );
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM brands WHERE id = ?', [$id]);
    }

    public static function save(?int $id, string $name, string $description): int
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 100) {
            throw new AppException('Tên thương hiệu là bắt buộc và tối đa 100 ký tự.');
        }
        if (Database::value('SELECT id FROM brands WHERE name = ? AND id <> ?', [$name, (int)$id])) {
            throw new AppException('Thương hiệu «' . $name . '» đã tồn tại.');
        }
        if ($id) {
            Database::exec('UPDATE brands SET name = ?, description = ? WHERE id = ?', [$name, mb_substr($description, 0, 255), $id]);
            return $id;
        }
        return Database::insert('INSERT INTO brands (name, description) VALUES (?, ?)', [$name, mb_substr($description, 0, 255)]);
    }

    public static function delete(int $id): void
    {
        $n = (int)Database::value('SELECT COUNT(*) FROM products WHERE brand_id = ?', [$id]);
        if ($n > 0) {
            throw new AppException("Không thể xóa thương hiệu đang có $n sản phẩm. Hãy chuyển các sản phẩm sang thương hiệu khác trước.");
        }
        Database::exec('DELETE FROM brands WHERE id = ?', [$id]);
    }
}
