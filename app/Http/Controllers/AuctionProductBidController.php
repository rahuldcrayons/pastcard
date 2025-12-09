<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuctionProductBidController extends Controller
{
    // Admin
    public function product_bids_admin($id) { return abort(404); }
    public function bid_destroy_admin($id) { return abort(404); }

    // Seller
    public function product_bids_seller($id) { return abort(404); }
    public function bid_destroy_seller($id) { return abort(404); }

    // Resource stubs for Route::resource('auction_product_bids', ...)
    public function index() { return abort(404); }
    public function create() { return abort(404); }
    public function store(Request $request) { return abort(404); }
    public function show($id) { return abort(404); }
    public function edit($id) { return abort(404); }
    public function update(Request $request, $id) { return abort(404); }
    public function destroy($id) { return abort(404); }
}
