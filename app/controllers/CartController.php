<?php
declare(strict_types=1);

/** UC08 – Giỏ hàng (khách vãng lai dùng được, không cần đăng nhập). */
final class CartController extends Controller
{
    public function index(): void
    {
        $lines = Cart::lines();
        $this->render('cart/index', [
            'title'   => 'Giỏ hàng',
            'lines'   => $lines,
            'total'   => Cart::total($lines),
            'problem' => Cart::hasProblem($lines),
        ]);
    }

    public function add(): void
    {
        $id = input_int('product_id');
        $qty = max(1, input_int('qty', 1));
        $buyNow = input('buy_now') === '1';
        try {
            Cart::add($id, $qty);
            flash('success', 'Đã thêm vào giỏ hàng.');
        } catch (AppException $e) {
            flash('warn', $e->getMessage());
        }
        redirect($buyNow ? '/gio-hang' : (input('back') && str_starts_with(input('back'), '/') && !str_starts_with(input('back'), '//') ? input('back') : '/gio-hang'));
    }

    public function update(): void
    {
        foreach ((array)($_POST['qty'] ?? []) as $pid => $qty) {
            if (is_numeric($pid) && is_numeric($qty)) {
                if ($note = Cart::setQty((int)$pid, (int)$qty)) {
                    flash('warn', $note);
                }
            }
        }
        flash('success', 'Đã cập nhật giỏ hàng.');
        redirect('/gio-hang');
    }

    public function remove(): void
    {
        Cart::remove(input_int('product_id'));
        flash('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
        redirect('/gio-hang');
    }
}
