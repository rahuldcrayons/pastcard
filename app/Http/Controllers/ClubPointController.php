<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClubPointController extends Controller
{
    // Admin methods
    public function configure_index()
    {
        return abort(404);
    }

    public function index()
    {
        return abort(404);
    }

    public function set_point()
    {
        return abort(404);
    }

    public function set_products_point(Request $request)
    {
        return abort(404);
    }

    public function set_all_products_point(Request $request)
    {
        return abort(404);
    }

    public function set_point_edit($id)
    {
        return abort(404);
    }

    public function club_point_detail($id)
    {
        return abort(404);
    }

    public function update_product_point(Request $request, $id)
    {
        return abort(404);
    }

    public function convert_rate_store(Request $request)
    {
        return abort(404);
    }

    // Frontend user methods
    public function userpoint_index()
    {
        return abort(404);
    }

    public function convert_point_into_wallet(Request $request)
    {
        return abort(404);
    }
}
