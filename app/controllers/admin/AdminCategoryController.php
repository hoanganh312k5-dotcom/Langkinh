<?php
declare(strict_types=1);

/** UC13 – Quản lý danh mục. */
final class AdminCategoryController extends Controller
{
    public function index(): void
    {
        $q = input('q');
        $this->render('admin/categories/index', [
            'title' => 'Danh mục',
            'rows'  => Category::withCounts($q),
            'q'     => $q,
        ], 'admin');
    }

    public function form(int $id = 0): void
    {
        $c = $id ? Category::find($id) : null;
        if ($id && !$c) {
            $this->notFound();
        }
        $this->render('admin/categories/form', ['title' => $c ? 'Sửa danh mục' : 'Thêm danh mục', 'c' => $c], 'admin');
    }

    public function save(): void
    {
        $id = input_int('id') ?: null;
        $back = $id ? "/admin/danh-muc/$id/sua" : '/admin/danh-muc/tao';
        try {
            Category::save($id, input('name'), input('description'), input('status', 'visible'));
        } catch (AppException $e) {
            $this->failWith($back, $e->getMessage());
        }
        flash('success', $id ? 'Đã cập nhật danh mục.' : 'Đã thêm danh mục.');
        redirect('/admin/danh-muc');
    }

    public function delete(int $id): void
    {
        try {
            Category::delete($id);
            flash('success', 'Đã xóa danh mục.');
        } catch (AppException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('/admin/danh-muc');
    }
}
