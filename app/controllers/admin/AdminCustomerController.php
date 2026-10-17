<?php
declare(strict_types=1);

/** UC20 – Quản lý khách hàng: xem, khóa/mở khóa; xóa chỉ khi chưa có đơn. */
final class AdminCustomerController extends Controller
{
    public function index(): void
    {
        $f = ['q' => input('q'), 'status' => input('status'), 'page' => input_int('page', 1)];
        $res = User::adminList($f, $f['page'], (int)config('rules.per_page_admin'));
        $this->render('admin/customers/index', ['title' => 'Khách hàng', 'f' => $f, 'rows' => $res['rows'], 'pg' => $res['pg']], 'admin');
    }

    public function show(int $id): void
    {
        $u = User::find($id);
        if (!$u || $u['role'] !== 'customer') {
            $this->notFound();
        }
        $orders = Database::all('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC', [$id]);
        $this->render('admin/customers/show', ['title' => $u['name'], 'u' => $u, 'orders' => $orders], 'admin');
    }

    public function lock(int $id): void
    {
        $this->setStatus($id, 'locked', 'Đã khóa tài khoản. Khách không thể đăng nhập hay đặt hàng.');
    }

    public function unlock(int $id): void
    {
        $this->setStatus($id, 'active', 'Đã mở khóa tài khoản.');
    }

    private function setStatus(int $id, string $status, string $msg): void
    {
        if ($id === Auth::id()) {
            flash('danger', 'Bạn không thể khóa chính tài khoản của mình.');
            redirect('/admin/khach-hang');
        }
        $u = User::find($id);
        if (!$u || $u['role'] !== 'customer') {
            $this->notFound();
        }
        User::setStatus($id, $status);
        flash('success', $msg);
        redirect(input('back') === 'detail' ? "/admin/khach-hang/$id" : '/admin/khach-hang');
    }

    public function delete(int $id): void
    {
        if ($id === Auth::id()) {
            flash('danger', 'Bạn không thể xóa chính tài khoản của mình.');
            redirect('/admin/khach-hang');
        }
        try {
            User::delete($id);
            flash('success', 'Đã xóa khách hàng.');
        } catch (AppException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('/admin/khach-hang');
    }
}
