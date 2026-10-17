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

-- ============================================================
--  LĂNG KÍNH – Dữ liệu mẫu
--  Tài khoản quản trị : admin@langkinh.vn / Admin@123
--  Tài khoản khách    : khach@langkinh.vn / Khach@123  (và minhanh@, quochuy@, thuha@ – thuha bị khóa)
--  Ngày giờ trong dữ liệu mẫu tính lùi từ lúc import (NOW()).
-- ============================================================
USE langkinh;
SET NAMES utf8mb4;

INSERT INTO users (id,name,email,phone,address,password_hash,role,status,created_at) VALUES
(1,'Quản trị viên','admin@langkinh.vn','0243999018','','$2y$10$Fo8W8akmI7LtNum1gwi0cuCZ2QerZoGGDtRpPP/Xe/jaWIqAxIfTy','admin','active',DATE_SUB(NOW(), INTERVAL 90 DAY)),
(2,'Nguyễn Thị Lan','khach@langkinh.vn','0912345678','12 Ngõ 45 Kim Mã, Ba Đình, Hà Nội','$2y$10$jV.xJyYO.I8pfWzz2E8NWueq26FBcvptYE0zkDNuAfjMIH1i9XtoS','customer','active',DATE_SUB(NOW(), INTERVAL 60 DAY)),
(3,'Trần Minh Anh','minhanh@mail.vn','0987654321','88 Nguyễn Trãi, Thanh Xuân, Hà Nội','$2y$10$jV.xJyYO.I8pfWzz2E8NWueq26FBcvptYE0zkDNuAfjMIH1i9XtoS','customer','active',DATE_SUB(NOW(), INTERVAL 45 DAY)),
(4,'Lê Quốc Huy','quochuy@mail.vn','0936111222','25 Lý Thường Kiệt, Hoàn Kiếm, Hà Nội','$2y$10$jV.xJyYO.I8pfWzz2E8NWueq26FBcvptYE0zkDNuAfjMIH1i9XtoS','customer','active',DATE_SUB(NOW(), INTERVAL 40 DAY)),
(5,'Phạm Thu Hà','thuha@mail.vn','0903555666','7 Đội Cấn, Ba Đình, Hà Nội','$2y$10$jV.xJyYO.I8pfWzz2E8NWueq26FBcvptYE0zkDNuAfjMIH1i9XtoS','customer','locked',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO categories (id,name,description,status) VALUES
(1,'Gọng kính','Gọng kính cận, kính thời trang','visible'),
(2,'Kính râm','Kính mát chống tia UV','visible'),
(3,'Tròng kính','Tròng cận, chống ánh sáng xanh, đổi màu','visible'),
(4,'Phụ kiện','Hộp, khăn lau, nước rửa kính','visible');

INSERT INTO brands (id,name,description) VALUES
(1,'Aurel','Gọng titan nhẹ, phong cách tối giản'),
(2,'Nordik','Thiết kế Bắc Âu, acetate dày'),
(3,'Kiyo','Dáng mềm, màu pastel'),
(4,'Marlow','Bền, giá dễ tiếp cận'),
(5,'Vista Optic','Tròng kính và chăm sóc mắt');

INSERT INTO products (id,code,name,category_id,brand_id,price,stock,material,shape,color,size,description,status,created_at) VALUES
(1,'GK-AU101','Gọng tròn Aurel Lumen',1,1,1290000,14,'Titan','Tròn','Vàng đồng','49-21-140','Gọng titan siêu nhẹ, bản lề lò xo, phù hợp mặt dài và mặt vuông. Trọng lượng khoảng 11 g.','visible',DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2,'GK-NK204','Gọng vuông Nordik Slate',1,2,890000,22,'Acetate','Vuông','Đen','52-18-145','Gọng acetate dày 4 mm, mặt cắt vát cạnh. Bề mặt chống bám vân tay.','visible',DATE_SUB(NOW(), INTERVAL 9 DAY)),
(3,'GK-KY310','Gọng mắt mèo Kiyo Sakura',1,3,1450000,9,'Acetate','Mắt mèo','Hồng','51-19-140','Dáng mắt mèo nâng khóe, màu hồng khói. Hợp gương mặt tròn và trái xoan.','visible',DATE_SUB(NOW(), INTERVAL 6 DAY)),
(4,'GK-MW415','Gọng oval Marlow Pebble',1,4,760000,30,'TR90','Oval','Xám','50-19-142','Chất liệu TR90 dẻo, khó gãy, nhẹ 9 g. Có thể uốn nhẹ theo khuôn mặt.','visible',DATE_SUB(NOW(), INTERVAL 14 DAY)),
(5,'GK-VT522','Gọng bán viền Vista Edge',1,5,1090000,3,'Kim loại','Vuông','Bạc','53-17-143','Gọng bán viền dây cước, thích hợp người đi làm văn phòng.','visible',DATE_SUB(NOW(), INTERVAL 20 DAY)),
(6,'GK-NK207','Gọng phi công Nordik Pilot',1,2,980000,18,'Kim loại','Phi công','Vàng','55-16-140','Gọng kim loại mạ vàng, cầu mũi đôi, đệm mũi silicone.','visible',DATE_SUB(NOW(), INTERVAL 11 DAY)),
(7,'KR-AU601','Kính râm phi công Aurel Horizon',2,1,2190000,11,'Kim loại','Phi công','Vàng','58-14-140','Tròng phân cực chống tia UV400, chống chói khi lái xe.','visible',DATE_SUB(NOW(), INTERVAL 5 DAY)),
(8,'KR-KY612','Kính râm tròn Kiyo Moon',2,3,1650000,16,'Acetate','Tròn','Nâu','49-22-145','Gọng acetate màu đồi mồi, tròng nâu khói UV400.','visible',DATE_SUB(NOW(), INTERVAL 8 DAY)),
(9,'KR-MW620','Kính râm vuông Marlow Dune',2,4,1390000,0,'Nhựa','Vuông','Đen','54-18-145','Kính râm dáng vuông, tròng xanh đen chống tia UV400.','visible',DATE_SUB(NOW(), INTERVAL 17 DAY)),
(10,'KR-NK630','Kính râm mắt mèo Nordik Dusk',2,2,1520000,12,'Acetate','Mắt mèo','Đỏ rượu','52-20-140','Gọng đỏ rượu, tròng mận chống UV400.','visible',DATE_SUB(NOW(), INTERVAL 4 DAY)),
(11,'TK-VT701','Tròng chống ánh sáng xanh Vista Blue 1.60',3,5,650000,40,NULL,NULL,NULL,'Chiết suất 1.60','Lọc ánh sáng xanh từ màn hình, giảm mỏi mắt. Giá tính cho một cặp.','visible',DATE_SUB(NOW(), INTERVAL 12 DAY)),
(12,'TK-VT702','Tròng đổi màu Vista Photo 1.56',3,5,890000,25,NULL,NULL,NULL,'Chiết suất 1.56','Tự sẫm màu ngoài trời, trong suốt khi vào nhà. Giá tính cho một cặp.','visible',DATE_SUB(NOW(), INTERVAL 16 DAY)),
(13,'TK-AU703','Tròng chống lóa Aurel Clear 1.67',3,1,1350000,6,NULL,NULL,NULL,'Chiết suất 1.67','Tròng mỏng chiết suất cao, phủ chống lóa và chống bám nước. Giá tính cho một cặp.','visible',DATE_SUB(NOW(), INTERVAL 10 DAY)),
(14,'PK-MW801','Hộp đựng kính cứng Marlow',4,4,190000,60,NULL,NULL,'Xanh navy',NULL,'Hộp đựng cứng, lót nhung, chống va đập nhẹ.','visible',DATE_SUB(NOW(), INTERVAL 25 DAY)),
(15,'PK-KY802','Khăn lau microfiber Kiyo (bộ 3)',4,3,59000,120,NULL,NULL,NULL,NULL,'Bộ 3 khăn sợi siêu mịn, không để lại xơ trên tròng kính.','visible',DATE_SUB(NOW(), INTERVAL 25 DAY)),
(16,'PK-VT803','Nước rửa kính Vista 60 ml',4,5,79000,45,NULL,NULL,NULL,NULL,'Dạng xịt, không cồn, an toàn với lớp phủ chống lóa.','visible',DATE_SUB(NOW(), INTERVAL 22 DAY)),
(17,'KR-AU605','Kính râm vuông Aurel Terrace',2,1,1890000,7,'Titan','Vuông','Xanh rêu','56-17-145','Gọng titan màu xanh rêu, tròng phân cực.','visible',DATE_SUB(NOW(), INTERVAL 2 DAY)),
(18,'GK-MW499','Gọng Marlow Archive (ngừng nhập)',1,4,520000,4,'Nhựa','Oval','Nâu','50-20-140','Mẫu cũ đã ngừng nhập, chỉ còn một ít tồn kho.','hidden',DATE_SUB(NOW(), INTERVAL 70 DAY));

INSERT INTO product_images (product_id,image_path,is_primary,sort_order) VALUES
(1,'GK-AU101-1.svg',1,1),
(1,'GK-AU101-2.svg',0,2),
(1,'GK-AU101-3.svg',0,3),
(2,'GK-NK204-1.svg',1,1),
(2,'GK-NK204-2.svg',0,2),
(2,'GK-NK204-3.svg',0,3),
(3,'GK-KY310-1.svg',1,1),
(3,'GK-KY310-2.svg',0,2),
(3,'GK-KY310-3.svg',0,3),
(4,'GK-MW415-1.svg',1,1),
(4,'GK-MW415-2.svg',0,2),
(4,'GK-MW415-3.svg',0,3),
(5,'GK-VT522-1.svg',1,1),
(5,'GK-VT522-2.svg',0,2),
(5,'GK-VT522-3.svg',0,3),
(6,'GK-NK207-1.svg',1,1),
(6,'GK-NK207-2.svg',0,2),
(6,'GK-NK207-3.svg',0,3),
(7,'KR-AU601-1.svg',1,1),
(7,'KR-AU601-2.svg',0,2),
(7,'KR-AU601-3.svg',0,3),
(8,'KR-KY612-1.svg',1,1),
(8,'KR-KY612-2.svg',0,2),
(8,'KR-KY612-3.svg',0,3),
(9,'KR-MW620-1.svg',1,1),
(9,'KR-MW620-2.svg',0,2),
(9,'KR-MW620-3.svg',0,3),
(10,'KR-NK630-1.svg',1,1),
(10,'KR-NK630-2.svg',0,2),
(10,'KR-NK630-3.svg',0,3),
(11,'TK-VT701-1.svg',1,1),
(11,'TK-VT701-2.svg',0,2),
(11,'TK-VT701-3.svg',0,3),
(12,'TK-VT702-1.svg',1,1),
(12,'TK-VT702-2.svg',0,2),
(12,'TK-VT702-3.svg',0,3),
(13,'TK-AU703-1.svg',1,1),
(13,'TK-AU703-2.svg',0,2),
(13,'TK-AU703-3.svg',0,3),
(14,'PK-MW801-1.svg',1,1),
(14,'PK-MW801-2.svg',0,2),
(14,'PK-MW801-3.svg',0,3),
(15,'PK-KY802-1.svg',1,1),
(15,'PK-KY802-2.svg',0,2),
(15,'PK-KY802-3.svg',0,3),
(16,'PK-VT803-1.svg',1,1),
(16,'PK-VT803-2.svg',0,2),
(16,'PK-VT803-3.svg',0,3),
(17,'KR-AU605-1.svg',1,1),
(17,'KR-AU605-2.svg',0,2),
(17,'KR-AU605-3.svg',0,3),
(18,'GK-MW499-1.svg',1,1),
(18,'GK-MW499-2.svg',0,2),
(18,'GK-MW499-3.svg',0,3);

INSERT INTO orders (id,code,user_id,receiver_name,receiver_phone,receiver_address,note,payment_method,total_amount,status,created_at) VALUES
(1,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 39120 MINUTE),'%y%m%d'),'-001'),3,'Trần Minh Anh','0987654321','88 Nguyễn Trãi, Thanh Xuân, Hà Nội','','bank',2590000,'done',DATE_SUB(NOW(), INTERVAL 39120 MINUTE)),
(2,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 34800 MINUTE),'%y%m%d'),'-001'),4,'Lê Quốc Huy','0936111222','25 Lý Thường Kiệt, Hoàn Kiếm, Hà Nội','','cod',890000,'done',DATE_SUB(NOW(), INTERVAL 34800 MINUTE)),
(3,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 30480 MINUTE),'%y%m%d'),'-001'),2,'Nguyễn Thị Lan','0912345678','12 Ngõ 45 Kim Mã, Ba Đình, Hà Nội','','cod',2308000,'done',DATE_SUB(NOW(), INTERVAL 30480 MINUTE)),
(4,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 27600 MINUTE),'%y%m%d'),'-001'),3,'Trần Minh Anh','0987654321','88 Nguyễn Trãi, Thanh Xuân, Hà Nội','','cod',1450000,'done',DATE_SUB(NOW(), INTERVAL 27600 MINUTE)),
(5,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 23280 MINUTE),'%y%m%d'),'-001'),4,'Lê Quốc Huy','0936111222','25 Lý Thường Kiệt, Hoàn Kiếm, Hà Nội','','bank',1840000,'done',DATE_SUB(NOW(), INTERVAL 23280 MINUTE)),
(6,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 20400 MINUTE),'%y%m%d'),'-001'),5,'Phạm Thu Hà','0903555666','7 Đội Cấn, Ba Đình, Hà Nội','','cod',2540000,'done',DATE_SUB(NOW(), INTERVAL 20400 MINUTE)),
(7,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 17520 MINUTE),'%y%m%d'),'-001'),2,'Nguyễn Thị Lan','0912345678','12 Ngõ 45 Kim Mã, Ba Đình, Hà Nội','','cod',980000,'cancelled',DATE_SUB(NOW(), INTERVAL 17520 MINUTE)),
(8,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 14640 MINUTE),'%y%m%d'),'-001'),3,'Trần Minh Anh','0987654321','88 Nguyễn Trãi, Thanh Xuân, Hà Nội','','bank',1520000,'done',DATE_SUB(NOW(), INTERVAL 14640 MINUTE)),
(9,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 11760 MINUTE),'%y%m%d'),'-001'),4,'Lê Quốc Huy','0936111222','25 Lý Thường Kiệt, Hoàn Kiếm, Hà Nội','','cod',1969000,'done',DATE_SUB(NOW(), INTERVAL 11760 MINUTE)),
(10,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 8880 MINUTE),'%y%m%d'),'-001'),2,'Nguyễn Thị Lan','0912345678','12 Ngõ 45 Kim Mã, Ba Đình, Hà Nội','','cod',1839000,'done',DATE_SUB(NOW(), INTERVAL 8880 MINUTE)),
(11,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 7440 MINUTE),'%y%m%d'),'-001'),3,'Trần Minh Anh','0987654321','88 Nguyễn Trãi, Thanh Xuân, Hà Nội','','cod',1350000,'done',DATE_SUB(NOW(), INTERVAL 7440 MINUTE)),
(12,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 4560 MINUTE),'%y%m%d'),'-001'),4,'Lê Quốc Huy','0936111222','25 Lý Thường Kiệt, Hoàn Kiếm, Hà Nội','','bank',1810000,'shipping',DATE_SUB(NOW(), INTERVAL 4560 MINUTE)),
(13,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 3120 MINUTE),'%y%m%d'),'-001'),2,'Nguyễn Thị Lan','0912345678','12 Ngõ 45 Kim Mã, Ba Đình, Hà Nội','','cod',2190000,'confirmed',DATE_SUB(NOW(), INTERVAL 3120 MINUTE)),
(14,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1680 MINUTE),'%y%m%d'),'-001'),3,'Trần Minh Anh','0987654321','88 Nguyễn Trãi, Thanh Xuân, Hà Nội','','bank',1740000,'pending',DATE_SUB(NOW(), INTERVAL 1680 MINUTE)),
(15,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 240 MINUTE),'%y%m%d'),'-001'),4,'Lê Quốc Huy','0936111222','25 Lý Thường Kiệt, Hoàn Kiếm, Hà Nội','','cod',1138000,'pending',DATE_SUB(NOW(), INTERVAL 240 MINUTE)),
(16,CONCAT('LK',DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 240 MINUTE),'%y%m%d'),'-002'),3,'Trần Minh Anh','0987654321','88 Nguyễn Trãi, Thanh Xuân, Hà Nội','','cod',177000,'done',DATE_SUB(NOW(), INTERVAL 240 MINUTE));

INSERT INTO order_details (order_id,product_id,product_name,unit_price,quantity) VALUES
(1,1,'Gọng tròn Aurel Lumen',1290000,1),
(1,11,'Tròng chống ánh sáng xanh Vista Blue 1.60',650000,2),
(2,2,'Gọng vuông Nordik Slate',890000,1),
(3,7,'Kính râm phi công Aurel Horizon',2190000,1),
(3,15,'Khăn lau microfiber Kiyo (bộ 3)',59000,2),
(4,3,'Gọng mắt mèo Kiyo Sakura',1450000,1),
(5,8,'Kính râm tròn Kiyo Moon',1650000,1),
(5,14,'Hộp đựng kính cứng Marlow',190000,1),
(6,4,'Gọng oval Marlow Pebble',760000,1),
(6,12,'Tròng đổi màu Vista Photo 1.56',890000,2),
(7,6,'Gọng phi công Nordik Pilot',980000,1),
(8,10,'Kính râm mắt mèo Nordik Dusk',1520000,1),
(9,17,'Kính râm vuông Aurel Terrace',1890000,1),
(9,16,'Nước rửa kính Vista 60 ml',79000,1),
(10,2,'Gọng vuông Nordik Slate',890000,2),
(10,15,'Khăn lau microfiber Kiyo (bộ 3)',59000,1),
(11,13,'Tròng chống lóa Aurel Clear 1.67',1350000,1),
(12,1,'Gọng tròn Aurel Lumen',1290000,1),
(12,18,'Gọng Marlow Archive (ngừng nhập)',520000,1),
(13,7,'Kính râm phi công Aurel Horizon',2190000,1),
(14,5,'Gọng bán viền Vista Edge',1090000,1),
(14,11,'Tròng chống ánh sáng xanh Vista Blue 1.60',650000,1),
(15,6,'Gọng phi công Nordik Pilot',980000,1),
(15,16,'Nước rửa kính Vista 60 ml',79000,2),
(16,15,'Khăn lau microfiber Kiyo (bộ 3)',59000,3);

INSERT INTO order_status_logs (order_id,old_status,new_status,changed_by,changed_at) VALUES
(1,NULL,'pending',3,DATE_SUB(NOW(), INTERVAL 39120 MINUTE)),
(1,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 39000 MINUTE)),
(1,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 37848 MINUTE)),
(1,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 35544 MINUTE)),
(2,NULL,'pending',4,DATE_SUB(NOW(), INTERVAL 34800 MINUTE)),
(2,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 34680 MINUTE)),
(2,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 33528 MINUTE)),
(2,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 31224 MINUTE)),
(3,NULL,'pending',2,DATE_SUB(NOW(), INTERVAL 30480 MINUTE)),
(3,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 30360 MINUTE)),
(3,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 29208 MINUTE)),
(3,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 26904 MINUTE)),
(4,NULL,'pending',3,DATE_SUB(NOW(), INTERVAL 27600 MINUTE)),
(4,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 27480 MINUTE)),
(4,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 26328 MINUTE)),
(4,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 24024 MINUTE)),
(5,NULL,'pending',4,DATE_SUB(NOW(), INTERVAL 23280 MINUTE)),
(5,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 23160 MINUTE)),
(5,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 22008 MINUTE)),
(5,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 19704 MINUTE)),
(6,NULL,'pending',5,DATE_SUB(NOW(), INTERVAL 20400 MINUTE)),
(6,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 20280 MINUTE)),
(6,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 19128 MINUTE)),
(6,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 16824 MINUTE)),
(7,NULL,'pending',2,DATE_SUB(NOW(), INTERVAL 17520 MINUTE)),
(7,'pending','cancelled',1,DATE_SUB(NOW(), INTERVAL 17400 MINUTE)),
(8,NULL,'pending',3,DATE_SUB(NOW(), INTERVAL 14640 MINUTE)),
(8,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 14520 MINUTE)),
(8,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 13368 MINUTE)),
(8,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 11064 MINUTE)),
(9,NULL,'pending',4,DATE_SUB(NOW(), INTERVAL 11760 MINUTE)),
(9,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 11640 MINUTE)),
(9,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 10488 MINUTE)),
(9,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 8184 MINUTE)),
(10,NULL,'pending',2,DATE_SUB(NOW(), INTERVAL 8880 MINUTE)),
(10,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 8760 MINUTE)),
(10,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 7608 MINUTE)),
(10,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 5304 MINUTE)),
(11,NULL,'pending',3,DATE_SUB(NOW(), INTERVAL 7440 MINUTE)),
(11,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 7320 MINUTE)),
(11,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 6168 MINUTE)),
(11,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 3864 MINUTE)),
(12,NULL,'pending',4,DATE_SUB(NOW(), INTERVAL 4560 MINUTE)),
(12,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 4440 MINUTE)),
(12,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 3288 MINUTE)),
(13,NULL,'pending',2,DATE_SUB(NOW(), INTERVAL 3120 MINUTE)),
(13,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 3000 MINUTE)),
(14,NULL,'pending',3,DATE_SUB(NOW(), INTERVAL 1680 MINUTE)),
(15,NULL,'pending',4,DATE_SUB(NOW(), INTERVAL 240 MINUTE)),
(16,NULL,'pending',3,DATE_SUB(NOW(), INTERVAL 240 MINUTE)),
(16,'pending','confirmed',1,DATE_SUB(NOW(), INTERVAL 120 MINUTE)),
(16,'confirmed','shipping',1,DATE_SUB(NOW(), INTERVAL 1 MINUTE)),
(16,'shipping','done',1,DATE_SUB(NOW(), INTERVAL 1 MINUTE));
