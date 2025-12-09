<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SellerPackagePaymentController extends Controller
{
    public function offline_payment_request()
    {
        return abort(404);
    }

    public function offline_payment_approval(Request $request)
    {
        return abort(404);
    }
}
