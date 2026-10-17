<?php
/**
 * Cấu hình chung của website Lăng Kính.
 *
 * XAMPP mặc định: user "root", mật khẩu rỗng  -> không cần sửa gì.
 * Nếu máy bạn khác, sao chép file này thành config.local.php và sửa giá trị
 * (config.local.php sẽ ghi đè lên các giá trị ở đây, và không bị commit lên git).
 */
return [
    'app' => [
        'name'  => 'Lăng Kính',
        'debug' => false,          // true: hiện chi tiết lỗi (chỉ dùng khi đang học/làm bài)
        'base_path' => null,       // null = tự nhận diện (vd. /langkinh/public). Chỉ đặt khi tự nhận diện sai.
        'timezone'  => 'Asia/Ho_Chi_Minh',
    ],

    'db' => [
        'host'    => getenv('LK_DB_HOST') ?: '127.0.0.1',
        'port'    => getenv('LK_DB_PORT') ?: '3306',
        'name'    => getenv('LK_DB_NAME') ?: 'langkinh',
        'user'    => getenv('LK_DB_USER') ?: 'root',
        'pass'    => getenv('LK_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],

    'shop' => [
        'name'    => 'Lăng Kính',
        'address' => 'Số 18 Phố Huế, Hai Bà Trưng, Hà Nội',
        'phone'   => '024 3999 0188',
        'email'   => 'lienhe@langkinh.vn',
        // Thông tin chuyển khoản hiển thị cho đơn "Chuyển khoản ngân hàng" – hãy sửa thành tài khoản thật.
        'bank' => [
            'name'   => 'Ngân hàng (ví dụ) – chi nhánh Hà Nội',
            'number' => '0000 0000 0000',
            'holder' => 'LANG KINH',
        ],
    ],

    'rules' => [
        'low_stock'      => 5,        // tồn kho <= ngưỡng này thì cảnh báo "sắp hết"
        'per_page_shop'  => 8,        // số sản phẩm / trang ở cửa hàng
        'per_page_admin' => 10,       // số dòng / trang ở trang quản trị
        'min_password'   => 8,
        'max_qty_per_line' => 10,     // tối đa mỗi sản phẩm trong 1 dòng giỏ hàng
    ],

    'upload' => [
        'max_bytes' => 2 * 1024 * 1024,                       // 2 MB
        'mimes'     => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'],
        'dir'       => 'uploads/products',                    // nằm trong thư mục public/
    ],
];
