<?php
declare(strict_types=1);

/** UC16 – Quản lý tồn kho. */
final class AdminStockController extends Controller
{
    public function index(): void
    {
        $f = ['q' => input('q'), 'stock' => input('stock', ''), 'category' => input_int('category')];
        $res = Product::listing($f, input_int('page', 1), (int)config('rules.per_page_admin'), true);
        $this->render('admin/stock', [
            'title' => 'Tồn kho', 'f' => $f, 'rows' => $res['rows'], 'pg' => $res['pg'],
            'categories' => Category::all(),
            'low' => (int)config('rules.low_stock'),
        ], 'admin');
    }

    /** Cập nhật: mode=set (đặt số lượng) hoặc mode=add (nhập thêm / điều chỉnh +/-). */
    public function update(int $id): void
    {
        $back = '/admin/ton-kho' . (($_POST['return'] ?? '') !== '' ? '?' . ltrim((string)$_POST['return'], '?') : '');
        if (!preg_match('#^/admin/ton-kho(\?[\w=&%.\-+]*)?$#', $back)) {
            $back = '/admin/ton-kho';
        }
        $p = Product::find($id, true);
        if (!$p) {
            $this->notFound();
        }
        $mode = input('mode', 'set');
        $raw = input('amount');
        if (!preg_match('/^-?\d{1,7}$/', $raw)) {
            flash('danger', 'Vui lòng nhập số lượng hợp lệ.');
            header('Location: ' . base_path() . $back);
            exit;
        }
        try {
            if ($mode === 'add') {
                Product::adjustStock($id, (int)$raw);
            } else {
                Product::setStock($id, (int)$raw);
            }
            flash('success', 'Đã cập nhật tồn kho «' . $p['name'] . '».');
        } catch (AppException $e) {
            flash('danger', $e->getMessage());
        }
        header('Location: ' . base_path() . $back);
        exit;
    }
}
