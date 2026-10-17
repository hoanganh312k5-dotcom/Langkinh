<?php
declare(strict_types=1);

/** UC12 – Đăng nhập quản trị (tách khỏi đăng nhập khách hàng). */
final class AdminAuthController extends Controller
{
    public function form(): void
    {
        $this->render('admin/login', ['title' => 'Đăng nhập quản trị'], 'plain');
    }

    public function login(): void
    {
        try {
            $u = Auth::attempt(mb_strtolower(input('email')), (string)($_POST['password'] ?? ''), 'admin');
        } catch (AppException $e) {
            $this->failWith('/admin/dang-nhap', $e->getMessage());
        }
        Auth::login($u);
        redirect('/admin');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'Bạn đã đăng xuất khỏi khu vực quản trị.');
        redirect('/admin/dang-nhap');
    }
}
