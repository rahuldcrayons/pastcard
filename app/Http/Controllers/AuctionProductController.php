<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuctionProductController extends Controller
{
    // Admin listing
    public function all_auction_product_list() { return abort(404); }
    public function inhouse_auction_products() { return abort(404); }
    public function seller_auction_products() { return abort(404); }

    // Admin CRUD
    public function product_create_admin() { return abort(404); }
    public function product_store_admin(Request $request) { return abort(404); }
    public function product_edit_admin($id) { return abort(404); }
    public function product_update_admin(Request $request, $id) { return abort(404); }
    public function product_destroy_admin($id) { return abort(404); }

    // Admin sales
    public function admin_auction_product_orders() { return abort(404); }

    // Seller side
    public function auction_product_list_seller() { return abort(404); }
    public function product_create_seller() { return abort(404); }
    public function product_store_seller(Request $request) { return abort(404); }
    public function product_edit_seller($id) { return abort(404); }
    public function product_update_seller(Request $request, $id) { return abort(404); }
    public function product_destroy_seller($id) { return abort(404); }
    public function seller_auction_product_orders() { return abort(404); }

    // User side
    public function purchase_history_user() { return abort(404); }
    public function auction_product_details($slug) { return abort(404); }
    public function all_auction_products() { return abort(404); }
}
