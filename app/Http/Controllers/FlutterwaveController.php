<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FlutterwaveController extends Controller
{
    public function callback(Request $request)
    {
        return abort(404);
    }
}
