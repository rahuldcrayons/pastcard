<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WholesaleProductController extends Controller
{
    // Admin routes
    public function all_wholesale_products() { return abort(404); }
    public function inhouse_wholesale_products() { return abort(404); }
    public function seller_wholesale_products() { return abort(404); }

    public function create() { return abort(404); }
    public function store(Request $request) { return abort(404); }
    public function edit($id) { return abort(404); }
    public function update(Request $request, $id) { return abort(404); }
    public function destroy($id) { return abort(404); }

    public function admin_wholesale_product_orders() { return abort(404); }

    // Seller routes
    public function wholesale_products_list_seller() { return abort(404); }
    public function seller_wholesale_product_orders() { return abort(404); }

    // Front routes
    public function wholesale_product_details($slug) { return abort(404); }
    public function all_wholesale_products_front() { return abort(404); }
}
