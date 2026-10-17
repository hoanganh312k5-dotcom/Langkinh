<?php
/**
 * Sao chép file này thành config.local.php rồi sửa những gì cần thiết.
 * Chỉ cần khai báo các khóa muốn ghi đè.
 */
return [
    'app' => [
        'debug' => true,   // bật khi đang học để xem lỗi chi tiết
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'langkinh',
        'user' => 'root',
        'pass' => '',      // XAMPP mặc định để trống
    ],
];
