# Lăng Kính – Website thương mại điện tử kính mắt

Đồ án: website bán kính mắt (B2C) viết bằng **PHP 8 (mô hình MVC tự xây) + MySQL (PDO) + Bootstrap 5 + jQuery**.
Gồm **khu cửa hàng** cho khách và **khu quản trị** cho chủ shop, làm theo 21 use case trong báo cáo.

---

## 1. Yêu cầu

| Thành phần | Phiên bản |
|---|---|
| PHP | 8.1 trở lên (XAMPP 8.x đã có sẵn) – cần extension `pdo_mysql`, `mbstring`, `fileinfo` (XAMPP bật mặc định) |
| MySQL / MariaDB | MySQL 5.7+ / MariaDB 10.3+ |
| Trình duyệt | Chrome, Edge, Firefox bản mới |

Không cần cài Composer hay npm: Bootstrap và jQuery đã nằm sẵn trong `public/assets/vendor/`.

---

## 2. Cài đặt với XAMPP (cách dễ nhất)

1. Giải nén thư mục `langkinh` vào **`C:\xampp\htdocs\`** → có đường dẫn `C:\xampp\htdocs\langkinh\`.
2. Mở **XAMPP Control Panel**, bấm **Start** cho **Apache** và **MySQL**.
3. Mở **http://localhost/phpmyadmin** → tab **Import** → chọn file **`database/langkinh.sql`** → **Import**.
   (File này tự tạo database `langkinh`, 8 bảng và dữ liệu mẫu.)
4. Mở trình duyệt: **http://localhost/langkinh/** (tự chuyển tới `/langkinh/public/`).

Xong. Nếu XAMPP của bạn đặt mật khẩu cho `root`, xem mục *Cấu hình* bên dưới.

### Tài khoản dùng thử

| Vai trò | Email | Mật khẩu | Link đăng nhập |
|---|---|---|---|
| Quản trị viên | `admin@langkinh.vn` | `Admin@123` | `/admin/dang-nhap` |
| Khách hàng | `khach@langkinh.vn` | `Khach@123` | `/dang-nhap` |
| Khách hàng | `minhanh@mail.vn`, `quochuy@mail.vn` | `Khach@123` | `/dang-nhap` |
| Khách bị khóa (để thử) | `thuha@mail.vn` | `Khach@123` | không đăng nhập được |

---

## 3. Mở và chạy bằng Visual Studio Code

1. **File → Open Folder…** → chọn thư mục `langkinh`.
2. VS Code sẽ gợi ý cài extension (có trong `.vscode/extensions.json`): *PHP Intelephense* (gợi ý code), *MySQL* (xem database ngay trong VS Code).
3. Có 2 cách chạy:
   - **Dùng Apache của XAMPP** như mục 2 (copy dự án vào `htdocs`), hoặc
   - **Dùng server có sẵn của PHP** (không cần Apache, chỉ cần bật MySQL):
     - Nhấn `Ctrl+Shift+B` (task *Chạy website*), hoặc gõ trong Terminal của VS Code:
       ```
       php -S localhost:8000 -t public
       ```
       (Windows có thể bấm đúp `serve.bat`; macOS/Linux chạy `./serve.sh`.)
     - Mở **http://localhost:8000**
4. Muốn xem lỗi chi tiết khi đang code: sao chép `app/config/config.local.example.php` thành `app/config/config.local.php` và để `'debug' => true`.

> Nếu `php` không nhận lệnh trong Terminal: thêm `C:\xampp\php` vào biến môi trường **PATH**, hoặc dùng `serve.bat`.

---

## 4. Cấu hình

File `app/config/config.php` chứa tất cả thiết lập. Để ghi đè mà không sửa file gốc, tạo `app/config/config.local.php` (xem `config.local.example.php`).

* **Kết nối DB**: `db.host / port / name / user / pass` (mặc định `127.0.0.1`, `root`, mật khẩu rỗng, database `langkinh`).
* **Thông tin cửa hàng + tài khoản chuyển khoản** hiển thị trên đơn: mục `shop` (hãy thay số tài khoản mẫu bằng thông tin thật).
* **Quy tắc**: ngưỡng cảnh báo sắp hết hàng (`low_stock` = 5), số sản phẩm mỗi trang, độ dài mật khẩu tối thiểu (8), v.v.
* **Upload ảnh**: tối đa 2 MB, chỉ JPG/PNG/WEBP.

---

## 5. Cấu trúc thư mục (MVC)

```
langkinh/
├── index.php                  # mở /langkinh/ → chuyển sang public/
├── serve.bat | serve.sh       # chạy bằng php -S
├── database/
│   ├── langkinh.sql           # ★ import file này (schema + dữ liệu mẫu)
│   ├── schema.sql             # chỉ cấu trúc 8 bảng
│   └── seed.sql               # chỉ dữ liệu mẫu
├── public/                    # ← thư mục web gốc (document root)
│   ├── index.php              # Front controller: mọi request đi qua đây
│   ├── .htaccess              # URL đẹp (mod_rewrite)
│   ├── assets/ (css, js, img, vendor/bootstrap + jquery)
│   └── uploads/products/      # ảnh sản phẩm (ảnh mẫu .svg + ảnh bạn upload)
└── app/                       # mã nguồn (không truy cập trực tiếp từ web)
    ├── config/config.php
    ├── routes.php             # bảng URL → Controller@action
    ├── core/                  # Router, Database (PDO), Auth, Controller, helpers
    ├── models/                # Product, Order, Cart, User, Category, Brand, Stats, ImageUploader
    ├── controllers/           # khách hàng + controllers/admin/
    └── views/                 # giao diện PHP: layouts/, partials/, product/, cart/, admin/…
```

**Luồng một request:** `public/index.php` → `Router` tìm route khớp → kiểm tra CSRF (nếu POST) và quyền (middleware `auth`/`admin`) → `Controller` gọi `Model` (truy vấn PDO) → `View` hiển thị trong layout.

---

## 6. Use case ↔ nơi cài đặt

| UC | Chức năng | URL | Controller / Model |
|---|---|---|---|
| UC01 | Đăng ký | `/dang-ky` | `AuthController`, `User::register` |
| UC02 | Đăng nhập / đăng xuất | `/dang-nhap` | `AuthController`, `Auth::attempt` |
| UC03 | Thông tin cá nhân, đổi mật khẩu | `/tai-khoan` | `AccountController`, `User` |
| UC04 | Danh sách sản phẩm (phân trang) | `/san-pham` | `ShopController::index`, `Product::listing` |
| UC05 | Tìm kiếm (không phân biệt dấu) | `/san-pham?q=…` | `Product::listing` |
| UC06 | Lọc & sắp xếp | `/san-pham?brand=…&sort=…` | `Product::listing` |
| UC07 | Chi tiết sản phẩm | `/san-pham/{id}` | `ShopController::show` |
| UC08 | Giỏ hàng (khách vãng lai) | `/gio-hang` | `CartController`, `Cart` (session) |
| UC09 | Đặt hàng | `/dat-hang` | `CheckoutController`, `Order::create` |
| UC10 | Theo dõi đơn hàng | `/don-hang` | `OrderController` |
| UC11 | Hủy đơn | `/don-hang/{id}/huy` | `Order::cancelByCustomer` |
| UC12 | Đăng nhập quản trị | `/admin/dang-nhap` | `AdminAuthController` |
| UC13 | Quản lý danh mục | `/admin/danh-muc` | `AdminCategoryController`, `Category` |
| UC14 | Quản lý thương hiệu | `/admin/thuong-hieu` | `AdminBrandController`, `Brand` |
| UC15 | Quản lý sản phẩm + ảnh | `/admin/san-pham` | `AdminProductController`, `Product`, `ImageUploader` |
| UC16 | Quản lý tồn kho | `/admin/ton-kho` | `AdminStockController` |
| UC17 | Quản lý đơn hàng | `/admin/don-hang` | `AdminOrderController`, `Order::adminList` |
| UC18 | Cập nhật trạng thái đơn | `/admin/don-hang/{id}` | `Order::changeStatus` |
| UC19 | In hóa đơn | `/admin/don-hang/{id}/hoa-don` | `AdminOrderController::invoice` |
| UC20 | Quản lý khách hàng (khóa/mở khóa) | `/admin/khach-hang` | `AdminCustomerController`, `User` |
| UC21 | Thống kê doanh thu, bán chạy | `/admin`, `/admin/thong-ke` | `Stats` |

---

## 7. Quy tắc nghiệp vụ đã cài đặt

**Đơn hàng** – vòng đời: `Chờ xác nhận → Đã xác nhận → Đang giao → Hoàn thành`; có thể `Đã hủy` trước khi Hoàn thành. Hai trạng thái cuối không đổi được và không đi lùi (`Order::NEXT`).

* **Tạo đơn nằm trong một transaction** (`Order::create`): ghi `orders` + `order_details` + **trừ tồn kho** + ghi `order_status_logs`. Lỗi ở bước nào thì hoàn tác toàn bộ. Sản phẩm được khóa dòng (`SELECT … FOR UPDATE`) nên hai người mua cùng lúc không thể bán vượt tồn kho.
* **Hủy đơn hoàn lại tồn kho.** Khách chỉ hủy được đơn của chính mình khi còn «Chờ xác nhận»; quản trị hủy được trước khi Hoàn thành.
* Tên và đơn giá sản phẩm được **lưu cố định** trong `order_details` – đổi giá sau này không ảnh hưởng đơn cũ.
* Mã đơn tự sinh, duy nhất: `LKyymmdd-NNN`.
* **Doanh thu chỉ tính đơn Hoàn thành**; sản phẩm bán chạy xếp theo số lượng bán.

**Tài khoản & phân quyền** – khách vãng lai xem hàng và dùng giỏ; phải đăng nhập mới đặt hàng, giỏ hàng được giữ sau khi đăng nhập. Tài khoản bị khóa không đăng nhập/đặt hàng được (hiệu lực ngay). Quản trị không tự khóa mình; khách đã có đơn chỉ khóa, không xóa.

**Dữ liệu** – không xóa danh mục/thương hiệu đang có sản phẩm; không xóa sản phẩm đã nằm trong đơn (chỉ ẩn). Mã sản phẩm duy nhất; giá, tồn kho ≥ 0.

**Bảo mật** – PDO prepared statements (chống SQL Injection) · `htmlspecialchars` mọi dữ liệu xuất ra (chống XSS) · CSRF token cho mọi form POST · mật khẩu băm `password_hash` (bcrypt) · session id được tạo lại khi đăng nhập · chặn dò mật khẩu (sai 5 lần khóa 60 giây) · thông báo đăng nhập chung «Email hoặc mật khẩu không đúng» · upload ảnh kiểm tra nội dung thật của file (không tin đuôi file), đổi tên ngẫu nhiên, thư mục upload không cho chạy PHP.

---

## 8. Cơ sở dữ liệu (8 bảng, InnoDB, utf8mb4)

`users` · `categories` · `brands` · `products` · `product_images` · `orders` · `order_details` · `order_status_logs`

* Khóa ngoại `ON DELETE RESTRICT` (không cho xóa dữ liệu đang được tham chiếu), riêng `product_images` là `CASCADE`.
* Chỉ mục: `products(category_id)`, `products(brand_id)`, `products(status)`, `orders(user_id)`, `orders(status, created_at)`, `order_details(order_id)`.
* Collation `utf8mb4_unicode_520_ci` nên tìm “gong” vẫn ra “Gọng”, “den” ra “Đen”.

---

## 9. Ảnh sản phẩm

Dữ liệu mẫu dùng ảnh minh họa dạng `.svg` trong `public/uploads/products/`. Để dùng ảnh thật: vào **Quản trị → Sản phẩm → Sửa → Thêm ảnh** (JPG/PNG/WEBP ≤ 2 MB, chọn được nhiều ảnh), rồi **Đặt làm đại diện** hoặc xóa ảnh mẫu.

---

## 10. Xử lý sự cố

| Triệu chứng | Cách xử lý |
|---|---|
| “Không kết nối được cơ sở dữ liệu” | Chưa bật MySQL trong XAMPP, chưa import `langkinh.sql`, hoặc sai user/mật khẩu trong `config.php`. |
| Vào `/langkinh/` được nhưng bấm link bị 404 | Apache chưa bật `mod_rewrite` hoặc không cho `.htaccess`. Trong `httpd.conf`: bật `LoadModule rewrite_module …` và đặt `AllowOverride All` cho `htdocs`. Hoặc chạy bằng `php -S` (mục 3). |
| Mất giao diện (không có CSS) | Mở đúng địa chỉ `http://localhost/langkinh/` (hoặc `…/langkinh/public/`). Nếu đặt thư mục tên khác `langkinh`, đặt `'base_path'` trong `config.local.php`. |
| Không upload được ảnh | Kiểm tra `upload_max_filesize` trong `php.ini` ≥ 2M và thư mục `public/uploads/products` có quyền ghi. |
| Import SQL báo lỗi `utf8mb4_unicode_520_ci` | Bản MySQL quá cũ (< 5.6); nâng cấp XAMPP hoặc đổi collation thành `utf8mb4_unicode_ci` trong `database/langkinh.sql`. |
| Muốn làm lại dữ liệu mẫu | Import lại `database/langkinh.sql` (xóa và tạo lại toàn bộ bảng). |

> Dữ liệu mẫu (sản phẩm, thương hiệu, khách hàng, đơn hàng) là **hư cấu** phục vụ demo. Ngày giờ trong đơn mẫu tính lùi từ lúc bạn import.
