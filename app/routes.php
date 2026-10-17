<?php
declare(strict_types=1);

/**
 * Bản đồ URL -> Controller@action.   {id} là số nguyên.
 * Middleware: 'auth' = khách hàng đã đăng nhập, 'admin' = quản trị, 'guest' = chưa đăng nhập.
 */
$r = new Router();

/* ------------------------- KHÁCH HÀNG ------------------------- */
$r->get('/',                    'HomeController@index');
$r->get('/san-pham',            'ShopController@index');            // UC04, UC05, UC06
$r->get('/san-pham/{id}',       'ShopController@show');             // UC07

$r->get('/gio-hang',            'CartController@index');            // UC08
$r->post('/gio-hang/them',      'CartController@add');
$r->post('/gio-hang/cap-nhat',  'CartController@update');
$r->post('/gio-hang/xoa',       'CartController@remove');

$r->get('/dat-hang',            'CheckoutController@form',  ['auth']);   // UC09
$r->post('/dat-hang',           'CheckoutController@store', ['auth']);

$r->get('/don-hang',            'OrderController@index',    ['auth']);   // UC10
$r->get('/don-hang/{id}',       'OrderController@show',     ['auth']);
$r->post('/don-hang/{id}/huy',  'OrderController@cancel',   ['auth']);   // UC11

$r->get('/dang-ky',             'AuthController@registerForm', ['guest']);   // UC01
$r->post('/dang-ky',            'AuthController@register',     ['guest']);
$r->get('/dang-nhap',           'AuthController@loginForm',    ['guest']);   // UC02
$r->post('/dang-nhap',          'AuthController@login',        ['guest']);
$r->post('/dang-xuat',          'AuthController@logout');

$r->get('/tai-khoan',           'AccountController@index',    ['auth']);     // UC03
$r->post('/tai-khoan',          'AccountController@update',   ['auth']);
$r->post('/tai-khoan/mat-khau', 'AccountController@password', ['auth']);

/* ------------------------- QUẢN TRỊ ------------------------- */
$r->get('/admin/dang-nhap',     'AdminAuthController@form',   ['guest']);    // UC12
$r->post('/admin/dang-nhap',    'AdminAuthController@login',  ['guest']);
$r->post('/admin/dang-xuat',    'AdminAuthController@logout');

$r->get('/admin',               'AdminDashboardController@index', ['admin']);           // UC21
$r->get('/admin/thong-ke',      'AdminDashboardController@stats', ['admin']);

$r->get('/admin/danh-muc',                 'AdminCategoryController@index',  ['admin']);   // UC13
$r->get('/admin/danh-muc/tao',             'AdminCategoryController@form',   ['admin']);
$r->get('/admin/danh-muc/{id}/sua',        'AdminCategoryController@form',   ['admin']);
$r->post('/admin/danh-muc/luu',            'AdminCategoryController@save',   ['admin']);
$r->post('/admin/danh-muc/{id}/xoa',       'AdminCategoryController@delete', ['admin']);

$r->get('/admin/thuong-hieu',              'AdminBrandController@index',  ['admin']);      // UC14
$r->get('/admin/thuong-hieu/tao',          'AdminBrandController@form',   ['admin']);
$r->get('/admin/thuong-hieu/{id}/sua',     'AdminBrandController@form',   ['admin']);
$r->post('/admin/thuong-hieu/luu',         'AdminBrandController@save',   ['admin']);
$r->post('/admin/thuong-hieu/{id}/xoa',    'AdminBrandController@delete', ['admin']);

$r->get('/admin/san-pham',                 'AdminProductController@index',  ['admin']);    // UC15
$r->get('/admin/san-pham/tao',             'AdminProductController@form',   ['admin']);
$r->get('/admin/san-pham/{id}/sua',        'AdminProductController@form',   ['admin']);
$r->post('/admin/san-pham/luu',            'AdminProductController@save',   ['admin']);
$r->post('/admin/san-pham/{id}/xoa',       'AdminProductController@delete', ['admin']);
$r->post('/admin/san-pham/{id}/trang-thai','AdminProductController@toggle', ['admin']);
$r->post('/admin/san-pham/{id}/anh-xoa',   'AdminProductController@deleteImage', ['admin']);
$r->post('/admin/san-pham/{id}/anh-chinh', 'AdminProductController@primaryImage', ['admin']);

$r->get('/admin/ton-kho',                  'AdminStockController@index',  ['admin']);      // UC16
$r->post('/admin/ton-kho/{id}',            'AdminStockController@update', ['admin']);

$r->get('/admin/don-hang',                 'AdminOrderController@index',  ['admin']);      // UC17
$r->get('/admin/don-hang/{id}',            'AdminOrderController@show',   ['admin']);
$r->post('/admin/don-hang/{id}/trang-thai','AdminOrderController@status', ['admin']);      // UC18
$r->get('/admin/don-hang/{id}/hoa-don',    'AdminOrderController@invoice', ['admin']);     // UC19

$r->get('/admin/khach-hang',               'AdminCustomerController@index',  ['admin']);   // UC20
$r->get('/admin/khach-hang/{id}',          'AdminCustomerController@show',   ['admin']);
$r->post('/admin/khach-hang/{id}/khoa',    'AdminCustomerController@lock',   ['admin']);
$r->post('/admin/khach-hang/{id}/mo-khoa', 'AdminCustomerController@unlock', ['admin']);
$r->post('/admin/khach-hang/{id}/xoa',     'AdminCustomerController@delete', ['admin']);

return $r;
