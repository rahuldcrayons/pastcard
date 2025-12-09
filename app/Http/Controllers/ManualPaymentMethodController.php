<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManualPaymentMethodController extends Controller
{
    // Resource-style methods for admin manual_payment_methods
    public function index()
    {
        return abort(404);
    }

    public function create()
    {
        return abort(404);
    }

    public function store(Request $request)
    {
        return abort(404);
    }

    public function show($id)
    {
        return abort(404);
    }

    public function edit($id)
    {
        return abort(404);
    }

    public function update(Request $request, $id)
    {
        return abort(404);
    }

    public function destroy($id)
    {
        return abort(404);
    }

    // Frontend / offline payment helpers
    public function show_payment_modal(Request $request)
    {
        return abort(404);
    }

    public function submit_offline_payment(Request $request)
    {
        return abort(404);
    }

    public function offline_recharge_modal(Request $request)
    {
        return abort(404);
    }

    public function offline_customer_package_purchase_modal(Request $request)
    {
        return abort(404);
    }

    public function offline_seller_package_purchase_modal(Request $request)
    {
        return abort(404);
    }
}
