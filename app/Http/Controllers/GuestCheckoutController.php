<?php

namespace App\Http\Controllers;

use App\Utility\PayfastUtility;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Cart;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\InstamojoController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\PublicSslCommerzPaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaytmController;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use App\Models\User;
use App\Models\Admin_setting;
use App\Models\Coupon;
use App\Models\SmsTemplate;
use App\Models\CouponUsage;
use App\Models\Address;
use App\Models\CombinedOrder;
use App\Models\Wallet;
use Session;
use App\Utility\PayhereUtility;
use App\Utility\NotificationUtility;
use App\Utility\SmsUtility;
use DB;

class GuestCheckoutController extends Controller
{
    public function __construct()
    {
        // Guest-specific initialization
    }

    // Handles the checkout process for guest users
    public function checkout(Request $request)
    {

        
        if ($request->payment_option != null) {

            (new OrderController)->gueststore($request);

            $request->session()->put('payment_type', 'cart_payment');

            $data['combined_order_id'] = $request->session()->get('combined_order_id');
            $request->session()->put('payment_data', $data);
            if ($request->session()->get('combined_order_id') != null) {
                if ($request->payment_option == 'paypal') {
                    $paypal = new PaypalController;
                    return $paypal->getCheckout();
                } elseif ($request->payment_option == 'stripe') {
                    $stripe = new StripePaymentController;
                    return $stripe->stripe();
                } elseif ($request->payment_option == 'mercadopago') {
                    $mercadopago = new MercadopagoController;
                    return $mercadopago->paybill();
                } elseif ($request->payment_option == 'sslcommerz') {
                    $sslcommerz = new PublicSslCommerzPaymentController;
                    return $sslcommerz->index($request);
                } elseif ($request->payment_option == 'instamojo') {
                    $instamojo = new InstamojoController;
                    return $instamojo->guestpay($request);
                } elseif ($request->payment_option == 'razorpay') {
                    $razorpay = new RazorpayController;
                    return $razorpay->payWithRazorpay($request);
                } elseif ($request->payment_option == 'payku') {
                    return (new PaykuController)->create($request);
                } elseif ($request->payment_option == 'voguepay') {
                    $voguePay = new VoguePayController;
                    return $voguePay->customer_showForm();
                } elseif ($request->payment_option == 'ngenius') {
                    $ngenius = new NgeniusController();
                    return $ngenius->pay();
                } elseif ($request->payment_option == 'iyzico') {
                    $iyzico = new IyzicoController();
                    return $iyzico->pay();
                } elseif ($request->payment_option == 'nagad') {
                    $nagad = new NagadController;
                    return $nagad->getSession();
                } elseif ($request->payment_option == 'bkash') {
                    $bkash = new BkashController;
                    return $bkash->pay();
                } elseif ($request->payment_option == 'aamarpay') {
                    $aamarpay = new AamarpayController;
                    return $aamarpay->index();
                } elseif ($request->payment_option == 'flutterwave') {
                    $flutterwave = new FlutterwaveController();
                    return $flutterwave->pay();
                } elseif ($request->payment_option == 'mpesa') {
                    $mpesa = new MpesaController();
                    return $mpesa->pay();
                } elseif ($request->payment_option == 'paystack') {
                    $paystack = new PaystackController;
                    return $paystack->redirectToGateway($request);
                } elseif ($request->payment_option == 'payhere') {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));

                    $combined_order_id = $combined_order->id;
                    $amount = $combined_order->grand_total;
                    $first_name = json_decode($combined_order->shipping_address)->name;
                    $last_name = 'X';
                    $phone = json_decode($combined_order->shipping_address)->phone;
                    $email = json_decode($combined_order->shipping_address)->email;
                    $address = json_decode($combined_order->shipping_address)->address;
                    $city = json_decode($combined_order->shipping_address)->city;

                    return PayhereUtility::create_checkout_form($combined_order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
                } elseif ($request->payment_option == 'payfast') {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));

                    $combined_order_id = $combined_order->id;
                    $amount = $combined_order->grand_total;

                    return PayfastUtility::create_checkout_form($combined_order_id, $amount);
                } elseif ($request->payment_option == 'paytm') {
                    
                    $paytm = new PaytmController;
                    return $paytm->guestpayment();
                } elseif ($request->payment_option == 'toyyibpay') {
                    return (new ToyyibpayController)->createbill();
                } else if ($request->payment_option == 'authorizenet') {
                    $authorize_net = new AuthorizeNetController();
                    return $authorize_net->pay();
                } elseif ($request->payment_option == 'cash_on_delivery') {

                    flash(translate("Your order has been placed successfully"))->success();
                    return redirect()->route('order_confirmed');
                } elseif ($request->payment_option == 'wallet') {
                    $user = Auth::user();
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                    if ($user->balance >= $combined_order->grand_total) {
                        $user->balance -= $combined_order->grand_total;
                        $user->save();
                        return $this->checkout_done($request->session()->get('combined_order_id'), null);
                    }
                } else {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                    foreach ($combined_order->orders as $order) {
                        $order->manual_payment = 1;
                        $order->save();
                    }
                    flash(translate('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
                    return redirect()->route('order_confirmed');
                }
            }
        } else {
            flash(translate('Select Payment Option.'))->warning();
            return back();
        }
    }
    public function codotp(Request $request)
    {
        $guest_user_id = session()->get('guest_user_id');
        $userdetails  = User::find($guest_user_id);
        $userid =  $userdetails->id;
        $userphn = $userdetails->phone;

        $rndmotp = random_int(100000, 999999);
        User::where('id', $userid)->update(['codotp' => $rndmotp]);

        if (SmsTemplate::where('identifier', 'phone_number_verification')->first()->status == 1) {

            SmsUtility::cod_otp_sms($rndmotp, $userphn);
        }
        print_r($rndmotp);
        //return response()->json(array('response_message' => $userphn));
    }
    public function otpverify(Request $request)
    {
        $guest_user_id = session()->get('guest_user_id');
        $userdetails  = User::find($guest_user_id);
        $userid =  $userdetails->id;
        $userotp = $request['1'] . $request['2'] . $request['3'] . $request['4'] . $request['5'] . $request['6'];
        $realotp = User::where('id', $userid)->first();
        if ($userotp == $realotp->codotp) {
            return response()->json(array('response_message' => 'success'));
        } else {
            return response()->json(array('response_message' => 'fail'));
        }
    }
    public function order_confirmed(){
        $guest_user_id = session()->get('guest_user_id');
        $temp_user_id = session()->get('temp_user_id');
        $userdetails  = User::find($guest_user_id);
        $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        // dd($carts);
        $cartprice = $carts[0]['price'];
        $carttax = $carts[0]['tax'];
        $cartship = $carts[0]['shipping_cost'];
        $carttotal = $cartprice + $carttax + $cartship;

        $getusr = Admin_setting::where('id', '1')->first();
        //    $getusr = Admin_setting::where('id', Auth::user()->id)->first();
        $percnt = $getusr->percent;

        $cashback = $carttotal * $percnt / 100;
        $csback = round($cashback, 2);

        $userid =  $userdetails->id;
        $updateblnc = User::find($userid);
        $updateblnc->balance += $csback;
        $updateblnc->save();

        $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));

        Cart::where('temp_user_id', $temp_user_id)
            ->delete();

        //Session::forget('club_point');
        //Session::forget('combined_order_id');

        foreach ($combined_order->orders as $order) {
            NotificationUtility::sendOrderPlacedNotification($order);
        }

        return view('frontend.guest_order_confirmed', compact('combined_order'));
    }
    // Handle payment gateway redirection
    protected function handlePaymentGateway($request)
    {
        if ($request->payment_option == 'paypal') {
            $paypal = new PaypalController;
            return $paypal->getCheckout();
        } elseif ($request->payment_option == 'stripe') {
            $stripe = new StripePaymentController;
            return $stripe->stripe();
        } elseif ($request->payment_option == 'mercadopago') {
            $mercadopago = new MercadopagoController;
            return $mercadopago->paybill();
        }
        // Add other payment gateway options here
        // ...
        elseif ($request->payment_option == 'cash_on_delivery') {
            flash(translate("Your order has been placed successfully"))->success();
            return redirect()->route('guest.order_confirmed');
        } else {
            $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
            foreach ($combined_order->orders as $order) {
                $order->manual_payment = 1;
                $order->save();
            }
            flash(translate('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
            return redirect()->route('guest.order_confirmed');
        }
    }

    // After successful checkout
    public function checkout_done($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::findOrFail($combined_order_id);

        foreach ($combined_order->orders as $key => $order) {
            $order = Order::findOrFail($order->id);
            $order->payment_status = 'paid';
            $order->payment_details = $payment;
            $order->save();

            calculateCommissionAffilationClubPoint($order);
        }

        Session::put('combined_order_id', $combined_order_id);
        return redirect()->route('guest.order_confirmed');
    }

    // Guest-specific shipping info
    public function get_shipping_info(Request $request)
    {
        $carts = Cart::where('guest_id', $request->session()->get('guest_id'))->get();
        if ($carts && count($carts) > 0) {
            $categories = Category::all();
            return view('frontend.shipping_info_guest', compact('categories', 'carts'));
        }
        flash(translate('Your cart is empty'))->success();
        return back();
    }

    // Store guest shipping info
    public function store_shipping_info(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'postal_code' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
        ]);
        $otheruser = User::where('email', $request->email)
            ->where('user_type', '!=','guest')
            ->first();
        if($otheruser){
            flash(translate('User Already exists, Please Login!'))->warning();
            return redirect()->back();
        }
        // Check if user with the provided email already exists
        $user = User::where('email', $request->email)
            ->where('user_type', 'guest')
            ->first();

        if (!$user) {
            // Create a new guest user record
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->user_type = 'guest';
            $user->password = Hash::make(uniqid()); // Generate a dummy password for guest users
            $user->address = $request->address;
            $user->country = $request->country_id;
            $user->state = $request->state_id;
            $user->city = $request->city_id;
            $user->postal_code = $request->postal_code;
            $user->phone = $request->phone;
            $user->save();
        }

        // Store the guest user's ID in the session
        Session::put('guest_user_id', $user->id);

        // Create a new address record (optional, if you need to store addresses separately)
        $address = new Address();
        $address->user_id = $user->id;
        $address->address = $request->address;
        $address->country_id = $request->country_id;
        $address->state_id = $request->state_id;
        $address->city_id = $request->city_id;
        $address->postal_code = $request->postal_code;
        $address->phone = $request->phone;
        $address->save();

        // Store the address ID in the session
        Session::put('guest_address_id', $address->id);

        // Redirect to the next step in the checkout process
        return redirect()->route('guest.checkout.delivery_info');
    }
    public function getDeliveryInfo()
    {
        // Retrieve cart items from the session
        $temp_user_id = session()->get('temp_user_id');
        $carts = Cart::where('temp_user_id', $temp_user_id)->get();
    
        // Check if there are items in the cart
        if (count($carts) > 0) {
            // Retrieve available shipping types
            $shipping_types = DB::table('shipping_types')->pluck('name');
            // Return the delivery information view
            return view('frontend.guest_delivery_info', compact('carts', 'shipping_types'));
        }
    
        // Redirect to home if the cart is empty
        flash(translate('Your cart is empty'))->success();
        return redirect()->route('home');
    }
    // Store guest delivery info
    public function store_delivery_info(Request $request)
    {
        $temp_user_id = session()->get('temp_user_id');
        $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }
        $address_id = session()->get('guest_address_id');
        $shipping_info = Address::where('id', $address_id)->first();
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;
        $coin = [];

        if ($carts && count($carts) > 0) {

            $shipping_charge = DB::table('shipping_types')->where('name', $request['shipping_type'])->pluck('price')->first();
            foreach ($carts as $key => $cartItem) {
                $product = \App\Models\Product::find($cartItem['product_id']);
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                $cartItem['shipping_cost'] = 0;
                $cartItem->save();
                if ($product->coin === 1)
                    array_push($coin, $product->coin);
            }
            $shipping = (($subtotal + $tax) >= get_setting('free_shipping_cost')) ? 0 : $shipping_charge;
            $shipping_final = $shipping;
            $shipping_type = $request['shipping_type'];
            $total = $subtotal + $tax + $shipping_charge;
            
            return view('frontend.guest_payment_select', compact('carts', 'shipping_info', 'total', 'coin', 'shipping_final', 'shipping_type'));
            
        } else {
            flash(translate('Your Cart was empty'))->warning();
            return redirect()->route('home');
        }
    }
    
}
