<?php
declare(strict_types=1);

/** UC03 – Thông tin cá nhân & đổi mật khẩu. */
final class AccountController extends Controller
{
    public function index(): void
    {
        $this->render('account/profile', ['title' => 'Tài khoản của tôi', 'u' => Auth::user()]);
    }

    public function update(): void
    {
        $name = input('name');
        $phone = input('phone');
        $address = input('address');
        if ($name === '' || mb_strlen($name) > 100) {
            $this->failWith('/tai-khoan', 'Vui lòng nhập họ tên (tối đa 100 ký tự).');
        }
        if (!User::phoneValid($phone)) {
            $this->failWith('/tai-khoan', 'Số điện thoại không hợp lệ (ví dụ 0912345678).');
        }
        User::updateProfile((int)Auth::id(), $name, preg_replace('/[\s.\-]/', '', $phone), $address);
        flash('success', 'Đã cập nhật thông tin cá nhân.');
        redirect('/tai-khoan');
    }

    public function password(): void
    {
        $new = (string)($_POST['new_password'] ?? '');
        if ($new !== (string)($_POST['new_password_confirm'] ?? '')) {
            $this->failWith('/tai-khoan', 'Mật khẩu mới nhập lại không khớp.', []);
        }
        try {
            User::changePassword((int)Auth::id(), (string)($_POST['current_password'] ?? ''), $new);
        } catch (AppException $e) {
            $this->failWith('/tai-khoan', $e->getMessage(), []);
        }
        flash('success', 'Đã đổi mật khẩu.');
        redirect('/tai-khoan');
    }
}
