<?php
declare(strict_types=1);

/** UC10 theo dõi đơn · UC11 hủy đơn (khách hàng chỉ thấy đơn của chính mình). */
final class OrderController extends Controller
{
    public function index(): void
    {
        $status = input('status');
        $res = Order::forUser((int)Auth::id(), $status, input_int('page', 1), 8);
        $this->render('account/orders', [
            'title'  => 'Đơn hàng của tôi',
            'rows'   => $res['rows'],
            'pg'     => $res['pg'],
            'status' => $status,
        ]);
    }

    public function show(int $id): void
    {
        $o = Order::find($id);
        if (!$o || (int)$o['user_id'] !== Auth::id()) {
            $this->notFound();   // không lộ việc đơn của người khác có tồn tại
        }
        $this->render('account/order_show', [
            'title' => 'Đơn ' . $o['code'],
            'o'     => $o,
            'items' => Order::items($id),
            'logs'  => Order::logs($id),
        ]);
    }

    public function cancel(int $id): void
    {
        try {
            Order::cancelByCustomer($id, (int)Auth::id());
            flash('success', 'Đã hủy đơn hàng. Sản phẩm đã được hoàn lại kho.');
        } catch (AppException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('/don-hang/' . $id);
    }
}
