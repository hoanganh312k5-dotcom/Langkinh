<?php
declare(strict_types=1);

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home/index', [
            'title'      => 'Kính mắt chính hãng',
            'categories' => Category::visible(),
            'newest'     => Product::newest(8),
            'best'       => Product::bestSellers(4),
            'catCounts'  => array_column(Database::all(
                "SELECT category_id, COUNT(*) n FROM products WHERE status='visible' GROUP BY category_id"
            ), 'n', 'category_id'),
        ]);
    }
}
