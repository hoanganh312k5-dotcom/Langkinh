<?php
declare(strict_types=1);

/**
 * Kết nối MySQL bằng PDO. Mọi truy vấn đều dùng prepared statement
 * (tham số truyền riêng, không nối chuỗi) để chống SQL Injection.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = config('db');
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $c['host'], $c['port'], $c['name'], $c['charset']);
            try {
                self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4, time_zone = '" . date('P') . "'",
                ]);
            } catch (PDOException $e) {
                throw new RuntimeException(
                    'Không kết nối được cơ sở dữ liệu. Hãy bật MySQL trong XAMPP và import database/langkinh.sql. (' . $e->getMessage() . ')'
                );
            }
        }
        return self::$pdo;
    }

    private static function run(string $sql, array $params = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        $i = 1;
        foreach ($params as $k => $v) {
            $name = is_int($k) ? $i++ : (str_starts_with((string)$k, ':') ? $k : ':' . $k);
            $type = is_int($v) ? PDO::PARAM_INT : (is_null($v) ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $st->bindValue($name, $v, $type);
        }
        $st->execute();
        return $st;
    }

    /** Nhiều dòng. */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** Một dòng (hoặc null). */
    public static function one(string $sql, array $params = []): ?array
    {
        $r = self::run($sql, $params)->fetch();
        return $r === false ? null : $r;
    }

    /** Một giá trị (ô đầu tiên). */
    public static function value(string $sql, array $params = [])
    {
        $r = self::run($sql, $params)->fetchColumn();
        return $r === false ? null : $r;
    }

    /** INSERT/UPDATE/DELETE – trả về số dòng bị ảnh hưởng. */
    public static function exec(string $sql, array $params = []): int
    {
        return self::run($sql, $params)->rowCount();
    }

    /** INSERT – trả về id vừa tạo. */
    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int)self::pdo()->lastInsertId();
    }

    /**
     * Chạy một nhóm thao tác trong TRANSACTION:
     * có lỗi (exception) ở bất kỳ bước nào thì ROLLBACK toàn bộ.
     */
    public static function transaction(callable $fn)
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $result = $fn();
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
