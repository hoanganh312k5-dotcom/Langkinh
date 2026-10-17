<?php
declare(strict_types=1);

/** UC17 danh sách đơn · UC18 cập nhật trạng thái · UC19 in hóa đơn. */
final class AdminOrderController extends Controller
{
    public function index(): void
    {
        $f = [
            'q' => input('q'), 'status' => input('status'), 'payment' => input('payment'),
            'from' => input('from'), 'to' => input('to'), 'page' => input_int('page', 1),
        ];
        $res = Order::adminList($f, $f['page'], (int)config('rules.per_page_admin'));
        $this->render('admin/orders/index', [
            'title' => 'Đơn hàng', 'f' => $f, 'rows' => $res['rows'], 'pg' => $res['pg'],
            'counts' => Order::countByStatus(),
        ], 'admin');
    }

    public function show(int $id): void
    {
        $o = Order::find($id) ?? $this->notFound();
        $this->render('admin/orders/show', [
            'title' => 'Đơn ' . $o['code'], 'o' => $o,
            'items' => Order::items($id), 'logs' => Order::logs($id),
        ], 'admin');
    }

    public function status(int $id): void
    {
        try {
            Order::changeStatus($id, input('status'), (int)Auth::id());
            flash('success', 'Đã cập nhật trạng thái đơn hàng.');
        } catch (AppException $e) {
            flash('danger', $e->getMessage());
        }
        redirect("/admin/don-hang/$id");
    }

    public function invoice(int $id): void
    {
        $o = Order::find($id) ?? $this->notFound();
        $this->render('admin/orders/invoice', [
            'title' => 'Hóa đơn ' . $o['code'], 'o' => $o, 'items' => Order::items($id),
        ], 'plain');
    }
}
