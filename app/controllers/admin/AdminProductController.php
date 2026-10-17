<?php
declare(strict_types=1);

/** UC15 – Quản lý sản phẩm (thêm/sửa/xóa/ẩn, nhiều ảnh). */
final class AdminProductController extends Controller
{
    public function index(): void
    {
        $f = [
            'q' => input('q'), 'category' => input_int('category'), 'brand' => input_int('brand'),
            'status' => input('status'), 'stock' => input('stock'),
        ];
        $res = Product::listing($f, input_int('page', 1), (int)config('rules.per_page_admin'), true);
        $this->render('admin/products/index', [
            'title' => 'Sản phẩm', 'f' => $f, 'rows' => $res['rows'], 'pg' => $res['pg'],
            'categories' => Category::all(), 'brands' => Brand::all(),
        ], 'admin');
    }

    public function form(int $id = 0): void
    {
        $p = $id ? Product::find($id, true) : null;
        if ($id && !$p) {
            $this->notFound();
        }
        $this->render('admin/products/form', [
            'title' => $p ? 'Sửa sản phẩm' : 'Thêm sản phẩm',
            'p' => $p,
            'images' => $p ? Product::images($id) : [],
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'inOrders' => $p ? Product::inOrders($id) : false,
        ], 'admin');
    }

    public function save(): void
    {
        $id = input_int('id') ?: 0;
        $back = $id ? "/admin/san-pham/$id/sua" : '/admin/san-pham/tao';

        $d = [
            'code' => strtoupper(input('code')), 'name' => input('name'),
            'category_id' => input_int('category_id'), 'brand_id' => input_int('brand_id'),
            'price' => input('price'), 'stock' => input('stock'),
            'material' => input('material'), 'shape' => input('shape'), 'color' => input('color'),
            'size' => input('size'), 'description' => input('description'),
            'status' => input('status') === 'hidden' ? 'hidden' : 'visible',
        ];

        // ----- Kiểm tra dữ liệu -----
        $err = null;
        if (!preg_match('/^[A-Z0-9][A-Z0-9\-_]{1,29}$/', $d['code'])) {
            $err = 'Mã sản phẩm chỉ gồm chữ, số, dấu gạch (2–30 ký tự), ví dụ GK-AU101.';
        } elseif (Product::codeExists($d['code'], $id)) {
            $err = 'Mã sản phẩm «' . $d['code'] . '» đã tồn tại.';
        } elseif ($d['name'] === '' || mb_strlen($d['name']) > 200) {
            $err = 'Tên sản phẩm là bắt buộc (tối đa 200 ký tự).';
        } elseif (!Category::find($d['category_id'])) {
            $err = 'Vui lòng chọn danh mục.';
        } elseif (!Brand::find($d['brand_id'])) {
            $err = 'Vui lòng chọn thương hiệu.';
        } elseif (!ctype_digit($d['price']) || strlen($d['price']) > 10) {
            $err = 'Giá phải là số nguyên không âm (VND).';
        } elseif (!ctype_digit($d['stock']) || strlen($d['stock']) > 7) {
            $err = 'Tồn kho phải là số nguyên không âm.';
        } elseif ($d['material'] !== '' && !in_array($d['material'], Product::MATERIALS, true)) {
            $err = 'Chất liệu không hợp lệ.';
        } elseif ($d['shape'] !== '' && !in_array($d['shape'], Product::SHAPES, true)) {
            $err = 'Kiểu dáng không hợp lệ.';
        }
        if ($err) {
            $this->failWith($back, $err);
        }

        // ----- Kiểm tra ảnh trước khi ghi DB -----
        $files = ImageUploader::normalize($_FILES['images'] ?? null);
        $saved = [];
        try {
            foreach ($files as $f) {
                $saved[] = ImageUploader::save($f);
            }
        } catch (AppException $e) {
            foreach ($saved as $s) {
                ImageUploader::remove($s);
            }
            $this->failWith($back, $e->getMessage());
        }

        try {
            if ($id) {
                if (!Product::find($id, true)) {
                    $this->notFound();
                }
                Product::update($id, $d);
            } else {
                $id = Product::create($d);
            }
            foreach ($saved as $s) {
                Product::addImage($id, $s);
            }
        } catch (Throwable $e) {
            foreach ($saved as $s) {
                ImageUploader::remove($s);
            }
            throw $e;
        }
        flash('success', input_int('id') ? 'Đã cập nhật sản phẩm.' : 'Đã thêm sản phẩm.');
        redirect(input_int('id') ? '/admin/san-pham' : "/admin/san-pham/$id/sua");
    }

    public function delete(int $id): void
    {
        try {
            foreach (Product::delete($id) as $file) {
                ImageUploader::remove($file);
            }
            flash('success', 'Đã xóa sản phẩm.');
        } catch (AppException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('/admin/san-pham');
    }

    /** Ẩn / hiện nhanh một sản phẩm. */
    public function toggle(int $id): void
    {
        $p = Product::find($id, true);
        if (!$p) {
            $this->notFound();
        }
        $new = $p['status'] === 'visible' ? 'hidden' : 'visible';
        Database::exec('UPDATE products SET status = ? WHERE id = ?', [$new, $id]);
        flash('success', $new === 'hidden' ? 'Đã ẩn sản phẩm khỏi cửa hàng.' : 'Sản phẩm đã hiển thị trở lại.');
        redirect('/admin/san-pham');
    }

    public function deleteImage(int $id): void
    {
        $file = Product::deleteImage($id, input_int('image_id'));
        if ($file) {
            ImageUploader::remove($file);
            flash('success', 'Đã xóa ảnh.');
        }
        redirect("/admin/san-pham/$id/sua");
    }

    public function primaryImage(int $id): void
    {
        try {
            Product::setPrimaryImage($id, input_int('image_id'));
            flash('success', 'Đã đặt ảnh đại diện.');
        } catch (AppException $e) {
            flash('danger', $e->getMessage());
        }
        redirect("/admin/san-pham/$id/sua");
    }
}
