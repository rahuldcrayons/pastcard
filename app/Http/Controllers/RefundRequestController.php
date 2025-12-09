<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RefundRequestController extends Controller
{
    // Admin Panel methods
    public function admin_index()
    {
        return abort(404);
    }

    public function refund_config()
    {
        return abort(404);
    }

    public function paid_index()
    {
        return abort(404);
    }

    public function rejected_index()
    {
        return abort(404);
    }

    public function refund_pay(Request $request)
    {
        return abort(404);
    }

    public function refund_time_update(Request $request)
    {
        return abort(404);
    }

    public function refund_sticker_update(Request $request)
    {
        return abort(404);
    }

    // FrontEnd User panel methods
    public function request_store(Request $request, $id)
    {
        return abort(404);
    }

    public function vendor_index()
    {
        return abort(404);
    }

    public function customer_index()
    {
        return abort(404);
    }

    public function request_approval_vendor(Request $request)
    {
        return abort(404);
    }

    public function refund_request_send_page($id)
    {
        return abort(404);
    }

    public function reject_refund_request(Request $request)
    {
        return abort(404);
    }

    public function reason_view($id)
    {
        return abort(404);
    }

    public function reject_reason_view($id)
    {
        return abort(404);
    }
}
