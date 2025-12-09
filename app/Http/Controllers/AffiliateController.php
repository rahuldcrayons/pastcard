<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    // Admin methods
    public function index()
    {
        return abort(404);
    }

    public function affiliate_option_store(Request $request)
    {
        return abort(404);
    }

    public function configs()
    {
        return abort(404);
    }

    public function config_store(Request $request)
    {
        return abort(404);
    }

    public function users()
    {
        return abort(404);
    }

    public function show_verification_request($id)
    {
        return abort(404);
    }

    public function approve_user($id)
    {
        return abort(404);
    }

    public function reject_user($id)
    {
        return abort(404);
    }

    public function updateApproved(Request $request)
    {
        return abort(404);
    }

    public function payment_modal(Request $request)
    {
        return abort(404);
    }

    public function payment_store(Request $request)
    {
        return abort(404);
    }

    public function payment_history($id)
    {
        return abort(404);
    }

    public function refferal_users()
    {
        return abort(404);
    }

    public function affiliate_withdraw_requests()
    {
        return abort(404);
    }

    public function affiliate_withdraw_modal(Request $request)
    {
        return abort(404);
    }

    public function withdraw_request_payment_store(Request $request)
    {
        return abort(404);
    }

    public function reject_withdraw_request($id)
    {
        return abort(404);
    }

    public function affiliate_logs_admin()
    {
        return abort(404);
    }

    // Frontend / user methods
    public function apply_for_affiliate()
    {
        return abort(404);
    }

    public function store_affiliate_user(Request $request)
    {
        return abort(404);
    }

    public function user_index()
    {
        return abort(404);
    }

    public function user_payment_history()
    {
        return abort(404);
    }

    public function user_withdraw_request_history()
    {
        return abort(404);
    }

    public function payment_settings()
    {
        return abort(404);
    }

    public function payment_settings_store(Request $request)
    {
        return abort(404);
    }

    public function withdraw_request_store(Request $request)
    {
        return abort(404);
    }
}
