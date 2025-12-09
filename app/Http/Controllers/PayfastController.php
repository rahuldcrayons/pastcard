<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PayfastController extends Controller
{
    public function checkout_notify(Request $request)
    {
        return abort(404);
    }

    public function checkout_return(Request $request)
    {
        return abort(404);
    }

    public function checkout_cancel(Request $request)
    {
        return abort(404);
    }

    public function wallet_notify(Request $request)
    {
        return abort(404);
    }

    public function wallet_return(Request $request)
    {
        return abort(404);
    }

    public function wallet_cancel(Request $request)
    {
        return abort(404);
    }

    public function seller_package_notify(Request $request)
    {
        return abort(404);
    }

    public function seller_package_payment_return(Request $request)
    {
        return abort(404);
    }

    public function seller_package_payment_cancel(Request $request)
    {
        return abort(404);
    }

    public function customer_package_notify(Request $request)
    {
        return abort(404);
    }

    public function customer_package_return(Request $request)
    {
        return abort(404);
    }

    public function customer_package_cancel(Request $request)
    {
        return abort(404);
    }
}
