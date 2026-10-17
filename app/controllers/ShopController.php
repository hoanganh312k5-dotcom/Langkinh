<?php
declare(strict_types=1);

/** UC04 danh sách · UC05 tìm kiếm · UC06 lọc/sắp xếp · UC07 chi tiết. */
final class ShopController extends Controller
{
    public function index(): void
    {
        $f = [
            'q'        => input('q'),
            'category' => input_int('category'),
            'brand'    => input_int('brand'),
            'shape'    => input('shape'),
            'material' => input('material'),
            'color'    => input('color'),
            'min'      => input('min'),
            'max'      => input('max'),
            'stock'    => input('stock'),
            'sort'     => input('sort', 'newest'),
        ];
        $res = Product::listing($f, input_int('page', 1), (int)config('rules.per_page_shop'));

        $this->render('product/index', [
            'title'      => 'Sản phẩm',
            'f'          => $f,
            'rows'       => $res['rows'],
            'pg'         => $res['pg'],
            'categories' => Category::visible(),
            'brands'     => Brand::all(),
            'shapes'     => Product::facet('shape'),
            'materials'  => Product::facet('material'),
            'colors'     => Product::facet('color'),
            'activeCat'  => $f['category'] ? Category::find($f['category']) : null,
        ]);
    }

    public function show(int $id): void
    {
        $p = Product::find($id);
        if (!$p) {
            $this->notFound();
        }
        $this->render('product/show', [
            'title'   => $p['name'],
            'p'       => $p,
            'images'  => Product::images($id),
            'related' => Product::related($p),
        ]);
    }
}
