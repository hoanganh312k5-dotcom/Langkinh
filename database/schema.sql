-- ============================================================
--  LĂNG KÍNH – Cơ sở dữ liệu (8 bảng)  |  MySQL / MariaDB, InnoDB, utf8mb4
--  Chạy file này trước, sau đó chạy seed.sql để có dữ liệu mẫu.
--  (Hoặc import một lần file langkinh.sql đã gộp sẵn.)
-- ============================================================

CREATE DATABASE IF NOT EXISTS langkinh
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
USE langkinh;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_status_logs, order_details, orders, product_images, products, brands, categories, users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Người dùng (khách hàng + quản trị viên)
CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  phone         VARCHAR(20)  NOT NULL DEFAULT '',
  address       VARCHAR(255) NOT NULL DEFAULT '',
  password_hash VARCHAR(255) NOT NULL,                       -- password_hash() của PHP (bcrypt)
  role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  status        ENUM('active','locked')  NOT NULL DEFAULT 'active',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- 2. Danh mục
CREATE TABLE categories (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  status      ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- 3. Thương hiệu
CREATE TABLE brands (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_brands_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- 4. Sản phẩm
CREATE TABLE products (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code        VARCHAR(30)  NOT NULL,                          -- mã sản phẩm, duy nhất
  name        VARCHAR(200) NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  brand_id    INT UNSIGNED NOT NULL,
  price       DECIMAL(12,0) UNSIGNED NOT NULL,                -- VND, >= 0
  stock       INT UNSIGNED NOT NULL DEFAULT 0,                -- tồn kho, >= 0
  material    VARCHAR(50)  NULL,                              -- Nhựa, Acetate, TR90, Kim loại, Titan
  shape       VARCHAR(50)  NULL,                              -- Vuông, Tròn, Mắt mèo, Phi công, Oval
  color       VARCHAR(50)  NULL,
  size        VARCHAR(50)  NULL,                              -- vd. 49-21-140 hoặc "Chiết suất 1.60"
  description TEXT         NULL,
  status      ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_products_code (code),
  KEY idx_products_category (category_id),
  KEY idx_products_brand (brand_id),
  KEY idx_products_status (status),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT,
  CONSTRAINT fk_products_brand    FOREIGN KEY (brand_id)    REFERENCES brands (id)     ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- 5. Ảnh sản phẩm (xóa sản phẩm -> xóa luôn ảnh)
CREATE TABLE product_images (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,                           -- tên file trong public/uploads/products/
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_images_product (product_id),
  CONSTRAINT fk_images_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- 6. Đơn hàng
CREATE TABLE orders (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code             VARCHAR(20)  NOT NULL,                     -- LKyymmdd-NNN, tự sinh, duy nhất
  user_id          INT UNSIGNED NOT NULL,
  receiver_name    VARCHAR(100) NOT NULL,
  receiver_phone   VARCHAR(20)  NOT NULL,
  receiver_address VARCHAR(255) NOT NULL,
  note             VARCHAR(500) NOT NULL DEFAULT '',
  payment_method   ENUM('cod','bank') NOT NULL DEFAULT 'cod',
  total_amount     DECIMAL(14,0) UNSIGNED NOT NULL,
  status           ENUM('pending','confirmed','shipping','done','cancelled') NOT NULL DEFAULT 'pending',
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_code (code),
  KEY idx_orders_user (user_id),
  KEY idx_orders_status_created (status, created_at),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- 7. Chi tiết đơn hàng (lưu "ảnh chụp" tên + giá tại thời điểm đặt)
CREATE TABLE order_details (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id     INT UNSIGNED NOT NULL,
  product_id   INT UNSIGNED NOT NULL,
  product_name VARCHAR(200) NOT NULL,
  unit_price   DECIMAL(12,0) UNSIGNED NOT NULL,
  quantity     INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_details_order (order_id),
  KEY idx_details_product (product_id),
  CONSTRAINT fk_details_order   FOREIGN KEY (order_id)   REFERENCES orders (id)   ON DELETE RESTRICT,
  CONSTRAINT fk_details_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- 8. Lịch sử đổi trạng thái đơn hàng
CREATE TABLE order_status_logs (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id   INT UNSIGNED NOT NULL,
  old_status ENUM('pending','confirmed','shipping','done','cancelled') NULL,
  new_status ENUM('pending','confirmed','shipping','done','cancelled') NOT NULL,
  changed_by INT UNSIGNED NOT NULL,
  changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_logs_order (order_id),
  CONSTRAINT fk_logs_order FOREIGN KEY (order_id)   REFERENCES orders (id) ON DELETE RESTRICT,
  CONSTRAINT fk_logs_user  FOREIGN KEY (changed_by) REFERENCES users (id)  ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
