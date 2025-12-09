<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index')->name('home');

// Product routes
Route::get('/product/{slug}', 'HomeController@product')->name('product');
Route::get('/category/{category_slug}', 'SearchController@index')->name('products.category');
Route::get('/brand/{brand_slug}', 'SearchController@index')->name('products.brand');

// Category and Brand listing routes  
Route::get('/all-categories', 'HomeController@all_categories')->name('categories.all');
Route::get('/all-brands', 'HomeController@all_brands')->name('brands.all');

// Customer product routes
Route::get('/customer-products', 'CustomerProductController@customer_products_listing')->name('customer.products');
Route::get('/customer-product/{slug}', 'CustomerProductController@customer_product')->name('customer.product');

// Product related routes
Route::post('/products/variant-price', 'ProductController@variant_price')->name('products.variant_price');

// Additional frontend routes
Route::get('/search', 'SearchController@index')->name('search');
Route::post('/search/ajax', 'SearchController@ajax_search')->name('search.ajax');
Route::get('/shops/{slug}', 'HomeController@shop')->name('shop.visit');

// AJAX section routes for homepage
Route::post('/home/section/featured', 'HomeController@load_featured_section')->name('home.section.featured');
Route::post('/home/section/best_selling', 'HomeController@load_best_selling_section')->name('home.section.best_selling');
Route::post('/home/section/auction_products', 'HomeController@load_auction_products_section')->name('home.section.auction_products');
Route::post('/home/section/home_categories', 'HomeController@load_home_categories_section')->name('home.section.home_categories');
Route::post('/home/section/best_sellers', 'HomeController@load_best_sellers_section')->name('home.section.best_sellers');

// User authentication routes
Route::get('/users/login', 'HomeController@login')->name('user.login');
Route::get('/users/registration', 'HomeController@registration')->name('user.registration');
Route::post('/register', 'Auth\RegisterController@register')->name('register');
Route::post('/login', 'Auth\LoginController@login')->name('login');

// Password reset routes
Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');

// Social authentication routes  
Route::get('/social-login/{provider}', 'Auth\LoginController@redirectToProvider')->name('social.login');

// Checkout routes
Route::get('/checkout/shipping_info', 'CheckoutController@get_shipping_info')->name('checkout.shipping_info');
Route::get('/guest/checkout/shipping_info', 'CheckoutController@get_shipping_info_guest')->name('guest.checkout.shipping_info');
Route::get('/checkout/membership-info', 'CheckoutController@get_membership_info')->name('checkout.membership_info');

// Compare functionality
Route::get('/compare/reset', 'CompareController@reset')->name('compare.reset');

// Shop management routes
Route::get('/shops/create', 'ShopController@create')->name('shops.create');
Route::post('/shops', 'ShopController@store')->name('shops.store');

// Order tracking
Route::get('/orders/track', 'HomeController@trackOrder')->name('orders.track');
Route::post('/orders/track', 'HomeController@trackOrder')->name('orders.track.post');

// Cart routes
Route::get('/cart', 'CartController@index')->name('cart');
Route::post('/cart/nav-cart', 'CartController@updateNavCart')->name('cart.nav_cart');
Route::post('/cart/show-cart-modal', 'CartController@showCartModal')->name('cart.showCartModal');
Route::post('/cart/add', 'CartController@addToCart')->name('cart.addToCart');
Route::post('/cart/remove', 'CartController@removeFromCart')->name('cart.removeFromCart');
Route::post('/cart/update', 'CartController@updateQuantity')->name('cart.updateQuantity');
Route::post('/cart/login/submit', 'CartController@cart_login')->name('cart.login.submit');

// Compare routes
Route::get('/compare', 'CompareController@index')->name('compare');
Route::post('/compare/add', 'CompareController@addToCompare')->name('compare.addToCompare');
Route::post('/compare/remove', 'CompareController@removeFromCompare')->name('compare.removeFromCompare');

// Address management routes (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/addresses', 'AddressController@store')->name('addresses.store');
    Route::get('/addresses/{id}/edit', 'AddressController@edit')->name('addresses.edit');
    Route::put('/addresses/{id}', 'AddressController@update')->name('addresses.update');
    Route::delete('/addresses/{id}', 'AddressController@destroy')->name('addresses.destroy');
});

// Conversation routes (disabled by default - enable when conversation system is activated)
Route::middleware('auth')->prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('dashboard')->with('error', 'Conversation feature is not enabled');
    })->name('index');
    Route::post('/', function() {
        return redirect()->back()->with('error', 'Conversation feature is not enabled');
    })->name('store');
    Route::get('/{id}', function() {
        return redirect()->route('dashboard')->with('error', 'Conversation feature is not enabled');
    })->name('show');
});

// AJAX utility routes
Route::post('/get-state', 'HomeController@getState')->name('get-state');
Route::post('/get-city', 'HomeController@getCity')->name('get-city');
Route::post('/category/elements', 'HomeController@get_category_items')->name('category.elements');

// Language and currency switching
Route::post('/language/change', 'HomeController@change_language')->name('language.change');
Route::post('/currency/change', 'HomeController@change_currency')->name('currency.change');

// Newsletter subscription
Route::post('/subscribers', 'NewsletterController@store')->name('subscribers.store');

// Order related routes
Route::post('/orders/details', 'OrderController@show')->name('orders.details');
Route::post('/purchase-history/details', 'OrderController@purchase_history_details')->name('purchase_history.details');

// User dashboard and authentication routes (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', 'HomeController@dashboard')->name('dashboard');
    Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
    
    // Admin dashboard (admin users only)
    Route::get('/admin/dashboard', 'AdminController@index')->name('admin.dashboard')->middleware('admin');
    
    // Club point routes (if club point addon is active)
    Route::post('/checkout/apply-club-point', 'CheckoutController@apply_club_point')->name('checkout.apply_club_point');
    Route::post('/checkout/remove-club-point', 'CheckoutController@remove_club_point')->name('checkout.remove_club_point');
});

// Conversation routes (disabled by default - enable when conversation system is activated)
Route::middleware('auth')->prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('dashboard')->with('error', 'Conversation feature is not enabled');
    })->name('index');
    Route::post('/', function() {
        return redirect()->back()->with('error', 'Conversation feature is not enabled');
    })->name('store');
    Route::get('/{id}', function() {
        return redirect()->route('dashboard')->with('error', 'Conversation feature is not enabled');
    })->name('show');
});

// Seller routes
Route::get('/sellers', 'HomeController@all_sellers')->name('sellers');

// Legal/Policy pages
Route::get('/privacy-policy', 'HomeController@privacypolicy')->name('privacypolicy');
Route::get('/terms-conditions', 'HomeController@terms')->name('terms');
Route::get('/support-policy', 'HomeController@supportpolicy')->name('supportpolicy');
Route::get('/return-policy', 'HomeController@returnpolicy')->name('returnpolicy');
Route::get('/about-us', 'HomeController@about_us')->name('aboutus');
Route::get('/about', 'HomeController@about_us')->name('about');
Route::get('/contact-us', 'HomeController@contact_us')->name('contactus');
Route::get('/contact', 'HomeController@contact_us')->name('contact');
Route::get('/faqs', 'HomeController@faqs')->name('faqs');

// Additional frontend pages
Route::get('/be-a-seller', 'HomeController@be_a_seller')->name('beaseller');
Route::get('/notifications', 'NotificationController@index')->name('all-notifications')->middleware('auth');

// Wallet routes (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/wallet/recharge', 'WalletController@recharge')->name('wallet.recharge');
    Route::post('/wallet/offline-recharge/make-payment', 'WalletController@make_payment')->name('wallet_recharge.make_payment');
});

// Conversation routes (disabled by default - enable when conversation system is activated)
Route::middleware('auth')->prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('dashboard')->with('error', 'Conversation feature is not enabled');
    })->name('index');
    Route::post('/', function() {
        return redirect()->back()->with('error', 'Conversation feature is not enabled');
    })->name('store');
    Route::get('/{id}', function() {
        return redirect()->route('dashboard')->with('error', 'Conversation feature is not enabled');
    })->name('show');
});

// Wishlist routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    Route::get('/wishlists', 'WishlistController@index')->name('wishlists.index');
    Route::post('/wishlists', 'WishlistController@store')->name('wishlists.store');
    Route::post('/wishlists/remove', 'WishlistController@remove')->name('wishlists.remove');
});

// Conversation routes (disabled by default - enable when conversation system is activated)
Route::middleware('auth')->prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('dashboard')->with('error', 'Conversation feature is not enabled');
    })->name('index');
    Route::post('/', function() {
        return redirect()->back()->with('error', 'Conversation feature is not enabled');
    })->name('store');
    Route::get('/{id}', function() {
        return redirect()->route('dashboard')->with('error', 'Conversation feature is not enabled');
    })->name('show');
});
