<?php
declare(strict_types=1);

/** UC14 – Quản lý thương hiệu. */
final class AdminBrandController extends Controller
{
    public function index(): void
    {
        $q = input('q');
        $this->render('admin/brands/index', [
            'title' => 'Thương hiệu',
            'rows'  => Brand::withCounts($q),
            'q'     => $q,
        ], 'admin');
    }

    public function form(int $id = 0): void
    {
        $b = $id ? Brand::find($id) : null;
        if ($id && !$b) {
            $this->notFound();
        }
        $this->render('admin/brands/form', ['title' => $b ? 'Sửa thương hiệu' : 'Thêm thương hiệu', 'b' => $b], 'admin');
    }

    public function save(): void
    {
        $id = input_int('id') ?: null;
        $back = $id ? "/admin/thuong-hieu/$id/sua" : '/admin/thuong-hieu/tao';
        try {
            Brand::save($id, input('name'), input('description'));
        } catch (AppException $e) {
            $this->failWith($back, $e->getMessage());
        }
        flash('success', $id ? 'Đã cập nhật thương hiệu.' : 'Đã thêm thương hiệu.');
        redirect('/admin/thuong-hieu');
    }

    public function delete(int $id): void
    {
        try {
            Brand::delete($id);
            flash('success', 'Đã xóa thương hiệu.');
        } catch (AppException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('/admin/thuong-hieu');
    }
}
