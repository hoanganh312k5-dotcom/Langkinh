<?php
declare(strict_types=1);

/** UC21 – Thống kê & tổng quan. */
final class AdminDashboardController extends Controller
{
    /** Trang tổng quan: số liệu 30 ngày gần nhất + việc cần làm. */
    public function index(): void
    {
        $to = date('Y-m-d');
        $from = date('Y-m-d', strtotime('-29 days'));
        $this->render('admin/dashboard', [
            'title'   => 'Tổng quan',
            'from'    => $from,
            'to'      => $to,
            'sum'     => Stats::summary($from, $to),
            'days'    => Stats::revenueByDay($from, $to),
            'status'  => Order::countByStatus(),
            'low'     => Stats::lowStock(6),
            'lowCount' => Product::lowStockCount(),
            'recent'  => Stats::recentOrders(6),
        ], 'admin');
    }

    /** Thống kê chi tiết theo khoảng ngày tự chọn. */
    public function stats(): void
    {
        $range = input('range', '30');
        $from = input('from');
        $to = input('to');
        if (!Order::validDate($from) || !Order::validDate($to)) {
            $days = in_array($range, ['7', '30', '90'], true) ? (int)$range : 30;
            $to = date('Y-m-d');
            $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
            $range = (string)$days;
        } else {
            $range = 'custom';
            if ($from > $to) {
                [$from, $to] = [$to, $from];
            }
            if ((strtotime($to) - strtotime($from)) / 86400 > 366) {   // giới hạn 1 năm cho biểu đồ
                $from = date('Y-m-d', strtotime($to . ' -365 days'));
                flash('warn', 'Khoảng thống kê tối đa 1 năm; đã tự điều chỉnh ngày bắt đầu.');
            }
        }
        $this->render('admin/stats', [
            'title'  => 'Thống kê',
            'range'  => $range,
            'from'   => $from,
            'to'     => $to,
            'sum'    => Stats::summary($from, $to),
            'days'   => Stats::revenueByDay($from, $to),
            'status' => Stats::statusBreakdown($from, $to),
            'top'    => Stats::topProducts($from, $to, 10),
        ], 'admin');
    }
}
