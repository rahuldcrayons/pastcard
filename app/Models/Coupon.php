<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
class Coupon extends Model
{
    public function user(){
    	return $this->belongsTo(User::class);
    }
    public static function getList_admin() {

		$query = DB::table('coupons')

						->join('users', 'users.id', '=', 'coupons.user_id')
						->select('coupons.*')
						->where('users.user_type', 'admin');

		$result = $query->get();

		return $result;
	}
    public static function getList_seller($seller) {

		$query = DB::table('coupons')

						->join('users', 'users.id', '=', 'coupons.user_id')
						->select('coupons.*')
						->where('users.id', $seller);

		$result = $query->get();

		return $result;
	}
}
