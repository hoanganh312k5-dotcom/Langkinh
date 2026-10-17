<?php
declare(strict_types=1);

/** UC01 đăng ký · UC02 đăng nhập / đăng xuất (khách hàng). */
final class AuthController extends Controller
{
    public function registerForm(): void
    {
        $this->render('auth/register', ['title' => 'Đăng ký']);
    }

    public function register(): void
    {
        $name = input('name');
        $email = mb_strtolower(input('email'));
        $phone = input('phone');
        $address = input('address');
        $pw = (string)($_POST['password'] ?? '');
        $pw2 = (string)($_POST['password_confirm'] ?? '');

        $err = null;
        if ($name === '' || mb_strlen($name) > 100) {
            $err = 'Vui lòng nhập họ tên (tối đa 100 ký tự).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $err = 'Email không hợp lệ.';
        } elseif (!User::phoneValid($phone)) {
            $err = 'Số điện thoại không hợp lệ (ví dụ 0912345678).';
        } elseif ($e = User::passwordError($pw)) {
            $err = $e;
        } elseif ($pw !== $pw2) {
            $err = 'Mật khẩu nhập lại không khớp.';
        } elseif (User::emailExists($email)) {
            $err = 'Email này đã được đăng ký. Hãy đăng nhập hoặc dùng email khác.';
        }
        if ($err) {
            $this->failWith('/dang-ky', $err);
        }

        $id = User::register($name, $email, preg_replace('/[\s.\-]/', '', $phone), $address, $pw);
        Auth::login(User::find($id));            // đăng nhập luôn sau khi đăng ký; giỏ hàng khách được giữ
        flash('success', 'Chào mừng ' . $name . '! Tài khoản đã được tạo.');
        redirect(Cart::isEmpty() ? '/' : '/gio-hang');
    }

    public function loginForm(): void
    {
        $this->render('auth/login', ['title' => 'Đăng nhập']);
    }

    public function login(): void
    {
        $email = mb_strtolower(input('email'));
        try {
            $u = Auth::attempt($email, (string)($_POST['password'] ?? ''), 'customer');
        } catch (AppException $e) {
            $this->failWith('/dang-nhap', $e->getMessage());
        }
        Auth::login($u);
        $to = $_SESSION['intended'] ?? '/';
        unset($_SESSION['intended']);
        flash('success', 'Đăng nhập thành công.');
        redirect(str_starts_with($to, '/') && !str_starts_with($to, '//') ? $to : '/');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'Bạn đã đăng xuất.');
        redirect('/');
    }
}
