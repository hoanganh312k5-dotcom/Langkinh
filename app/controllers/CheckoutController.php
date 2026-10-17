<?php
declare(strict_types=1);

/** UC09 – Đặt hàng (bắt buộc đăng nhập). */
final class CheckoutController extends Controller
{
    public function form(): void
    {
        $lines = Cart::lines();
        if (!$lines) {
            flash('warn', 'Giỏ hàng của bạn đang trống.');
            redirect('/san-pham');
        }
        if (Cart::hasProblem($lines)) {
            flash('warn', 'Một số sản phẩm trong giỏ không còn đủ hàng. Vui lòng điều chỉnh trước khi đặt.');
            redirect('/gio-hang');
        }
        $u = Auth::user();
        $this->render('checkout/form', [
            'title' => 'Đặt hàng',
            'lines' => $lines,
            'total' => Cart::total($lines),
            'u'     => $u,
        ]);
    }

    public function store(): void
    {
        $name    = input('receiver_name');
        $phone   = input('receiver_phone');
        $address = input('receiver_address');
        $note    = input('note');
        $payment = input('payment');

        $err = null;
        if ($name === '' || mb_strlen($name) > 100) {
            $err = 'Vui lòng nhập họ tên người nhận (tối đa 100 ký tự).';
        } elseif (!User::phoneValid($phone)) {
            $err = 'Số điện thoại người nhận không hợp lệ (ví dụ 0912345678).';
        } elseif (mb_strlen($address) < 10 || mb_strlen($address) > 255) {
            $err = 'Vui lòng nhập địa chỉ giao hàng đầy đủ (số nhà, đường, quận/huyện, tỉnh/thành).';
        } elseif (!isset(Order::PAY[$payment])) {
            $err = 'Vui lòng chọn phương thức thanh toán.';
        } elseif (mb_strlen($note) > 500) {
            $err = 'Ghi chú tối đa 500 ký tự.';
        }
        if ($err) {
            $this->failWith('/dat-hang', $err);
        }

        try {
            $res = Order::create((int)Auth::id(), [
                'name' => $name, 'phone' => preg_replace('/[\s.\-]/', '', $phone),
                'address' => $address, 'note' => $note, 'payment' => $payment,
            ], Cart::raws());
        } catch (AppException $e) {
            flash('danger', $e->getMessage());
            redirect('/gio-hang');
        }

        Cart::clear();
        flash('success', 'Đặt hàng thành công! Mã đơn của bạn là ' . $res['code'] . '.');
        redirect('/don-hang/' . $res['id']);
    }
}
