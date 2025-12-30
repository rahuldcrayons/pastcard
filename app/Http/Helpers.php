<?php

// use App\Http\Controllers\ClubPointController; // Commented out - addon not installed
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\CommissionController;
use App\Models\Currency;
use App\Models\BusinessSetting;
use App\Models\ProductStock;
use App\Models\Address;
use App\Models\CustomerPackage;
use App\Models\Upload;
use App\Models\Translation;
use App\Models\City;
use App\Utility\CategoryUtility;
use App\Models\Wallet;
use App\Models\CombinedOrder;
use App\Models\User;
use App\Models\Addon;
use App\Models\Product;
use App\Models\Shop;
use App\Utility\SendSMSUtility;
use App\Utility\NotificationUtility;
use Illuminate\Support\Facades\URL;

//sensSMS function for OTP
if (!function_exists('sendSMS')) {
    function sendSMS($to, $from, $text, $template_id)
    {
        return SendSMSUtility::sendSMS($to, $from, $text, $template_id);
    }
}

//highlights the selected navigation on admin panel
if (!function_exists('areActiveRoutes')) {
    function areActiveRoutes(array $routes, $output = "active")
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) return $output;
        }
    }
}

//highlights the selected navigation on frontend
if (!function_exists('areActiveRoutesHome')) {
    function areActiveRoutesHome(array $routes, $output = "active")
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) return $output;
        }
    }
}

//highlights the selected navigation on frontend
if (!function_exists('default_language')) {
    function default_language()
    {
        return env("DEFAULT_LANGUAGE");
    }
}
function thmx_currency_convert($amount){
    $url = 'https://api.exchangerate-api.com/v4/latest/INR';
    $json = file_get_contents($url);
    $exp = json_decode($json);

    $convert = $exp->rates->USD;

    return $convert * $amount;
}
function updatecurrency_convert(){
    $url = 'https://api.exchangerate-api.com/v4/latest/USD';
    $json = file_get_contents($url);
    $exp = json_decode($json);
	$currency = Currency::get();
	foreach($currency as $curre){
		$code = $curre->code;
		$convert = $exp->rates->$code;
		Currency::where('id',$curre->id)->update(['exchange_rate'=>$convert]);
	}
}
/**
 * Save JSON File
 * @return Response
 */
if (!function_exists('convert_to_usd')) {
    function convert_to_usd($amount)
    {
        $currency = get_system_default_currency();
        $usdCurrency = get_currency_by_code('USD');
        if (!$usdCurrency) return $amount;
        return (floatval($amount) / floatval($currency->exchange_rate)) * $usdCurrency->exchange_rate;
    }
}

if (!function_exists('convert_to_kes')) {
    function convert_to_kes($amount)
    {
        $currency = get_system_default_currency();
        $kesCurrency = get_currency_by_code('KES');
        if (!$kesCurrency) return $amount;
        return (floatval($amount) / floatval($currency->exchange_rate)) * $kesCurrency->exchange_rate;
    }
}

//filter products based on vendor activation system
if (!function_exists('filter_products')) {
    function filter_products($products)
    {
        $verified_sellers = verified_sellers_id();
        if (get_setting('vendor_system_activation') == 1) {
            return $products->where('approved', '1')->where('published', '1')->where('auction_product', 0)->orderBy('created_at', 'desc')->where(function ($p) use ($verified_sellers) {
                $p->where('added_by', 'admin')->orWhere(function ($q) use ($verified_sellers) {
                    $q->whereIn('user_id', $verified_sellers);
                });
            });
        } else {
            return $products->where('published', '1')->where('auction_product', 0)->where('added_by', 'admin');
        }
    }
}

//cache products based on category using HYBRID approach:
// - Products WITH pivot entries: use pivot table
// - Products WITHOUT pivot entries: use category_id column
if (!function_exists('get_cached_products')) {
    function get_cached_products($category_id = null)
    {
        if ($category_id != null) {
            return Cache::remember('products-category-v2-' . $category_id, 3600, function () use ($category_id) {
                // Include the selected category and all its children
                $categoryIds = [$category_id];
                try {
                    $children = CategoryUtility::children_ids($category_id, false);
                    if (!empty($children)) {
                        $categoryIds = array_merge($categoryIds, $children);
                    }
                } catch (\Throwable $e) {
                    // Fallback gracefully
                }

                // HYBRID: Get products from pivot table
                $pivotProductIds = \DB::table('category_product')
                    ->whereIn('category_id', $categoryIds)
                    ->pluck('product_id')
                    ->unique()
                    ->toArray();

                // HYBRID: Also get products without pivot entries (using category_id)
                $directProductIds = \DB::table('products')
                    ->leftJoin('category_product', 'products.id', '=', 'category_product.product_id')
                    ->where('products.published', 1)
                    ->whereNull('category_product.product_id')
                    ->whereIn('products.category_id', $categoryIds)
                    ->pluck('products.id')
                    ->toArray();

                $allProductIds = array_unique(array_merge($pivotProductIds, $directProductIds));

                return Product::with('stocks')->whereIn('id', $allProductIds)
                    ->where('published', 1)
                    ->where('unit_price', '>', 0)
                    ->orderBy('id', 'desc')
                    ->limit(10)
                    ->get();
            });
        } else {
            return Cache::remember('products-all-v2', 3600, function () {
                return Product::with('stocks')->where('published', 1)
                    ->where('unit_price', '>', 0)
                    ->orderBy('id', 'desc')
                    ->limit(12)
                    ->get();
            });
        }
    }
}

//cache product counts per category using HYBRID approach:
// - Products WITH pivot entries: use pivot table (original WordPress assignments)
// - Products WITHOUT pivot entries: use category_id column (newer products)
if (!function_exists('category_product_counts')) {
    function category_product_counts()
    {
        return Cache::remember('category_product_counts', 600, function () {
            // 1. Counts from pivot table (original WordPress data)
            $pivotCounts = \DB::table('category_product')
                ->join('products', 'category_product.product_id', '=', 'products.id')
                ->where('products.published', 1)
                ->select('category_product.category_id', \DB::raw('COUNT(DISTINCT category_product.product_id) as aggregate'))
                ->groupBy('category_product.category_id')
                ->pluck('aggregate', 'category_id')
                ->toArray();

            // 2. Counts for products WITHOUT pivot entries (use category_id)
            $directCounts = \DB::table('products')
                ->leftJoin('category_product', 'products.id', '=', 'category_product.product_id')
                ->where('products.published', 1)
                ->whereNull('category_product.product_id')
                ->select('products.category_id', \DB::raw('COUNT(*) as aggregate'))
                ->groupBy('products.category_id')
                ->pluck('aggregate', 'category_id')
                ->toArray();

            // 3. Merge counts (pivot takes precedence, add non-pivot counts)
            $merged = $pivotCounts;
            foreach ($directCounts as $catId => $count) {
                if (isset($merged[$catId])) {
                    $merged[$catId] += $count;
                } else {
                    $merged[$catId] = $count;
                }
            }

            return collect($merged);
        });
    }
}

// Get product counts including all child/sub-child categories
if (!function_exists('category_with_children_product_counts')) {
    function category_with_children_product_counts()
    {
        return Cache::remember('category_with_children_product_counts', 600, function () {
            // Get direct counts per category
            $directCounts = category_product_counts();

            // Get all categories with their parent relationships
            $categories = \App\Models\Category::select('id', 'parent_id')->get();

            // Build a map of category_id => array of all descendant IDs
            $childrenMap = [];
            foreach ($categories as $cat) {
                $childrenMap[$cat->id] = CategoryUtility::children_ids($cat->id);
            }

            // Calculate total counts (self + all descendants)
            $totalCounts = [];
            foreach ($categories as $cat) {
                $total = isset($directCounts[$cat->id]) ? $directCounts[$cat->id] : 0;

                // Add counts from all child categories
                foreach ($childrenMap[$cat->id] as $childId) {
                    if (isset($directCounts[$childId])) {
                        $total += $directCounts[$childId];
                    }
                }

                $totalCounts[$cat->id] = $total;
            }

            return collect($totalCounts);
        });
    }
}

if (!function_exists('category_product_count')) {
    function category_product_count($category_id)
    {
        // Use counts that include child categories
        $counts = category_with_children_product_counts();
        return isset($counts[$category_id]) ? $counts[$category_id] : 0;
    }
}

if (!function_exists('verified_sellers_id')) {
    function verified_sellers_id()
    {
        return Cache::rememberForever('verified_sellers_id', function () {
            return App\Models\Seller::where('verification_status', 1)->pluck('user_id')->toArray();
        });
    }
}

if (!function_exists('get_system_default_currency')) {
    function get_system_default_currency()
    {
        return Cache::remember('system_default_currency', 86400, function () {
            return Currency::findOrFail(get_setting('system_default_currency'));
        });
    }
}

// Get all active currencies (cached)
if (!function_exists('get_all_active_currencies')) {
    function get_all_active_currencies()
    {
        return Cache::remember('active_currencies', 86400, function () {
            return Currency::where('status', 1)->get();
        });
    }
}

// Get currency by code (cached)
if (!function_exists('get_currency_by_code')) {
    function get_currency_by_code($code)
    {
        $currencies = Cache::remember('all_currencies_by_code', 86400, function () {
            return Currency::all()->keyBy('code');
        });
        return $currencies->get($code);
    }
}

// Get all active languages (cached)
if (!function_exists('get_all_active_languages')) {
    function get_all_active_languages()
    {
        return Cache::remember('active_languages', 86400, function () {
            return \App\Models\Language::where('status', 1)->get();
        });
    }
}

// Get language by code (cached)
if (!function_exists('get_language_by_code')) {
    function get_language_by_code($code)
    {
        $languages = Cache::remember('all_languages_by_code', 86400, function () {
            return \App\Models\Language::all()->keyBy('code');
        });
        return $languages->get($code);
    }
}

//converts currency to home default currency
if (!function_exists('convert_price')) {
    function convert_price($price)
    {
        if (Session::has('currency_code') && (Session::get('currency_code') != get_system_default_currency()->code)) {
            $price = floatval($price) / floatval(get_system_default_currency()->exchange_rate);
            $price = floatval($price) * floatval(Session::get('currency_exchange_rate'));
        }
        return $price;
    }
}

//gets currency symbol
if (!function_exists('currency_symbol')) {
    function currency_symbol()
    {
        if (Session::has('currency_symbol')) {
            return Session::get('currency_symbol');
        }
        return get_system_default_currency()->symbol;
    }
}

//formats currency
if (!function_exists('format_price')) {
    function format_price($price)
    {
        if (get_setting('decimal_separator') == 1) {
            $fomated_price = number_format($price, get_setting('no_of_decimals'));
        } else {
            $fomated_price = number_format($price, get_setting('no_of_decimals'), ',', ' ');
        }

        if (get_setting('symbol_format') == 1) {
            return currency_symbol() . ' ' . $fomated_price;
        } else if (get_setting('symbol_format') == 3) {
            return currency_symbol() . ' ' . $fomated_price;
        } else if (get_setting('symbol_format') == 4) {
            return $fomated_price . ' ' . currency_symbol();
        }
        return $fomated_price . currency_symbol();
    }
}

//formats price to home default price with convertion
if (!function_exists('single_price')) {
    function single_price($price)
    {
        return format_price(convert_price($price));
    }
}

if (! function_exists('discount_in_percentage')) {
    function discount_in_percentage($product)
    {
        try {
            $base = home_base_price($product, false);
            $reduced = home_discounted_base_price($product, false);
            
            // Enhanced validation to prevent division by zero
            if (!is_numeric($base) || !is_numeric($reduced) || $base <= 0 || $reduced === '' || $reduced === null) {
                return 0;
            }
            
            $discount = $base - $reduced;
            
            // Additional check to ensure base is not zero before division
            if ($base == 0) {
                return 0;
            }
            
            $dp = ($discount * 100) / $base;
            return round($dp);
        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('Division by zero in discount_in_percentage: ' . $e->getMessage());
            return 0;
        }
        return 0;
    }
}

//Shows Price on page based on low to high
if (!function_exists('home_price')) {
    function home_price($product, $formatted = true)
    {
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;

        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                if ($lowest_price > $stock->price) {
                    $lowest_price = $stock->price;
                }
                if ($highest_price < $stock->price) {
                    $highest_price = $stock->price;
                }
            }
        }

        // foreach ($product->taxes as $product_tax) {
        //     if ($product_tax->tax_type == 'percent') {
        //         $lowest_price += ($lowest_price * $product_tax->tax) / 100;
        //         $highest_price += ($highest_price * $product_tax->tax) / 100;
        //     } elseif ($product_tax->tax_type == 'amount') {
        //         $lowest_price += $product_tax->tax;
        //         $highest_price += $product_tax->tax;
        //     }
        // }

        if ($formatted) {
            if ($lowest_price == $highest_price) {
                return format_price(convert_price($lowest_price));
            } else {
                return format_price(convert_price($lowest_price)) . ' - ' . format_price(convert_price($highest_price));
            }
        } else {
            return $lowest_price . ' - ' . $highest_price;
        }
    }
}

//Shows Price on page based on low to high with discount
if (!function_exists('home_discounted_price')) {
    function home_discounted_price($product, $formatted = true)
    {
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;

        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                if ($lowest_price > $stock->price) {
                    $lowest_price = $stock->price;
                }
                if ($highest_price < $stock->price) {
                    $highest_price = $stock->price;
                }
            }
        }

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $lowest_price -= ($lowest_price * $product->discount) / 100;
                $highest_price -= ($highest_price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $lowest_price -= $product->discount;
                $highest_price -= $product->discount;
            }
        }

        // foreach ($product->taxes as $product_tax) {
        //     if ($product_tax->tax_type == 'percent') {
        //         $lowest_price += ($lowest_price * $product_tax->tax) / 100;
        //         $highest_price += ($highest_price * $product_tax->tax) / 100;
        //     } elseif ($product_tax->tax_type == 'amount') {
        //         $lowest_price += $product_tax->tax;
        //         $highest_price += $product_tax->tax;
        //     }
        // }

        if ($formatted) {
            if ($lowest_price == $highest_price) {
                return format_price(convert_price($lowest_price));
            } else {
                return format_price(convert_price($lowest_price)) . ' - ' . format_price(convert_price($highest_price));
            }
        } else {
            return $lowest_price . ' - ' . $highest_price;
        }
    }
}

//Shows Base Price
if (!function_exists('home_base_price_by_stock_id')) {
    function home_base_price_by_stock_id($id)
    {
        $product_stock = ProductStock::findOrFail($id);
        $price = $product_stock->price;
        $tax = 0;

        // foreach ($product_stock->product->taxes as $product_tax) {
        //     if ($product_tax->tax_type == 'percent') {
        //         $tax += ($price * $product_tax->tax) / 100;
        //     } elseif ($product_tax->tax_type == 'amount') {
        //         $tax += $product_tax->tax;
        //     }
        // }
        // $price += $tax;
        return format_price(convert_price($price));
    }
}
if (!function_exists('home_base_price')) {
    function home_base_price($product, $formatted = true)
    {
        $price = $product->unit_price;
        $tax = 0;

        // foreach ($product->taxes as $product_tax) {
        //     if ($product_tax->tax_type == 'percent') {
        //         $tax += ($price * $product_tax->tax) / 100;
        //     } elseif ($product_tax->tax_type == 'amount') {
        //         $tax += $product_tax->tax;
        //     }
        // }
        // $price += $tax;
        return $formatted ? format_price(convert_price($price)) : $price;
    }
}

//Shows Base Price with discount
if (!function_exists('home_discounted_base_price_by_stock_id')) {
    function home_discounted_base_price_by_stock_id($id)
    {
        $product_stock = ProductStock::findOrFail($id);
        $product = $product_stock->product;
        $price = $product_stock->price;
        $tax = 0;

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent' && $product->discount > 0) {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount' && $product->discount > 0) {
                $price -= $product->discount;
            }
        }

        // foreach ($product->taxes as $product_tax) {
        //     if ($product_tax->tax_type == 'percent') {
        //         $tax += ($price * $product_tax->tax) / 100;
        //     } elseif ($product_tax->tax_type == 'amount') {
        //         $tax += $product_tax->tax;
        //     }
        // }
        // $price += $tax;

        return format_price(convert_price($price));
    }
}

//Shows Base Price with discount
if (!function_exists('home_discounted_base_price')) {
    function home_discounted_base_price($product, $formatted = true)
    {
        $price = $product->unit_price;
        $tax = 0;

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }
        if ($discount_applicable) {
            if ($product->discount_type == 'percent' && $product->discount > 0) {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount' && $product->discount > 0) {
                $price -= $product->discount;
            }
        }

        // foreach ($product->taxes as $product_tax) {
        //     if ($product_tax->tax_type == 'percent') {
        //         $tax += ($price * $product_tax->tax) / 100;
        //     } elseif ($product_tax->tax_type == 'amount') {
        //         $tax += $product_tax->tax;
        //     }
        // }
        // $price += $tax;

        return $formatted ? format_price(convert_price($price)) : $price;
    }
}

if (!function_exists('renderStarRating')) {
    function renderStarRating($rating, $maxRating = 5)
    {
        $fullStar = "<i class = 'las la-star active'></i>";
        $halfStar = "<i class = 'las la-star half'></i>";
        $emptyStar = "<i class = 'las la-star'></i>";
        $rating = $rating <= $maxRating ? $rating : $maxRating;

        $fullStarCount = (int)$rating;
        $halfStarCount = ceil($rating) - $fullStarCount;
        $emptyStarCount = $maxRating - $fullStarCount - $halfStarCount;

        $html = str_repeat($fullStar, $fullStarCount);
        $html .= str_repeat($halfStar, $halfStarCount);
        $html .= str_repeat($emptyStar, $emptyStarCount);
        echo $html;
    }
}

function translate($key, $lang = null, $addslashes = false)
{
    if($lang == null){
        $lang = App::getLocale();
    }

    $lang_key = preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', strtolower($key)));

    $translations_en = Cache::rememberForever('translations-en', function () {
        return Translation::where('lang', 'en')->pluck('lang_value', 'lang_key')->toArray();
    });

    if (!isset($translations_en[$lang_key])) {
        $translation_def = new Translation;
        $translation_def->lang = 'en';
        $translation_def->lang_key = $lang_key;
        $translation_def->lang_value = str_replace(array("\r", "\n", "\r\n"), "", $key);
        $translation_def->save();
        Cache::forget('translations-en');
    }

    // return user session lang
    $translation_locale = Cache::rememberForever("translations-{$lang}", function () use ($lang) {
        return Translation::where('lang', $lang)->pluck('lang_value', 'lang_key')->toArray();
    });
    if (isset($translation_locale[$lang_key])) {
        return trim($translation_locale[$lang_key]);
    }

    // return default lang if session lang not found
    $translations_default = Cache::rememberForever('translations-' . env('DEFAULT_LANGUAGE', 'en'), function () {
        return Translation::where('lang', env('DEFAULT_LANGUAGE', 'en'))->pluck('lang_value', 'lang_key')->toArray();
    });
    if (isset($translations_default[$lang_key])) {
        return trim($translations_default[$lang_key]);
    }

    // fallback to en lang
    if (!isset($translations_en[$lang_key])) {
        return trim($key);
    }
    return trim($translations_en[$lang_key]);
}

function remove_invalid_charcaters($str)
{
    $str = str_ireplace(array("\\"), '', $str);
    return str_ireplace(array('"'), '\"', $str);
}

function getShippingCost($carts, $index)
{
    $admin_products = array();
    $seller_products = array();

    $cartItem = $carts[$index];
    $product = Product::find($cartItem['product_id']);

    if ($product->digital == 1) {
        return 0;
    }

    foreach ($carts as $key => $cart_item) {
        $item_product = Product::find($cart_item['product_id']);
        if ($item_product->added_by == 'admin') {
            array_push($admin_products, $cart_item['product_id']);
        } else {
            $product_ids = array();
            if (isset($seller_products[$item_product->user_id])) {
                $product_ids = $seller_products[$item_product->user_id];
            }
            array_push($product_ids, $cart_item['product_id']);
            $seller_products[$item_product->user_id] = $product_ids;
        }
    }

    if (get_setting('shipping_type') == 'flat_rate') {
        return get_setting('flat_rate_shipping_cost') / count($carts);
    }
    elseif (get_setting('shipping_type') == 'seller_wise_shipping') {
        if ($product->added_by == 'admin') {
            return get_setting('shipping_cost_admin') / count($admin_products);
        } else {
            return Shop::where('user_id', $product->user_id)->first()->shipping_cost / count($seller_products[$product->user_id]);
        }
    }
    elseif (get_setting('shipping_type') == 'area_wise_shipping') {
        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        $city = City::where('id', $shipping_info->city_id)->first();
        if ($city != null) {
            if ($product->added_by == 'admin') {
                return $city->cost / count($admin_products);
            } else {
                return $city->cost / count($seller_products[$product->user_id]);
            }
        }
        return 0;
    }
    else {
        if($product->is_quantity_multiplied && get_setting('shipping_type') == 'product_wise_shipping') {
            return  $product->shipping_cost * $cartItem['quantity'];
        }
        return $product->shipping_cost;
    }
}

function timezones()
{
    return Timezones::timezonesToArray();
}

if (!function_exists('app_timezone')) {
    function app_timezone()
    {
        return config('app.timezone');
    }
}

if (!function_exists('api_asset')) {
    function api_asset($id)
    {
        if (($asset = \App\Models\Upload::find($id)) != null) {
            return $asset->file_name;
        }
        return "";
    }
}

//return file uploaded via uploader
if (!function_exists('uploaded_asset')) {
    function uploaded_asset($id)
    {
        if (empty($id)) {
            return null;
        }

        // Handle WordPress URL format: "url ! alt : ... | url2"
        // For any URL strings, return placeholder - don't try to load external images
        if (is_string($id) && strpos($id, 'http') === 0) {
            // Return placeholder for all URL-based images
            return static_asset('assets/img/placeholder.jpg');
        }

        // Handle upload ID
        if (($asset = \App\Models\Upload::find($id)) != null) {
            return $asset->external_link == null ? my_asset($asset->file_name) : $asset->external_link;
        }
        return null;
    }
}

if (!function_exists('my_asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    function my_asset($path, $secure = null)
    {
        if (env('FILESYSTEM_DRIVER') == 's3') {
            return Storage::disk('s3')->url($path);
        } else {
            // In local env (php artisan serve), the document root is already /public,
            // so prepending another 'public/' causes 404 (public/public/...).
            if (env('APP_ENV') === 'local') {
                return app('url')->asset($path, $secure);
            }

            return app('url')->asset('public/' . $path, $secure);
        }
    }
}

if (!function_exists('static_asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    function static_asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}


// if (!function_exists('isHttps')) {
//     function isHttps()
//     {
//         return !empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS']);
//     }
// }

if (!function_exists('getBaseURL')) {
    function getBaseURL()
    {
		if (!empty($_SERVER['HTTP_HOST'])){
			$root = '//' . $_SERVER['HTTP_HOST'];
			$root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
		}else{
			$root = URL::to('/public/');
		}
        return $root;
    }
}


if (!function_exists('getFileBaseURL')) {
    function getFileBaseURL()
    {
        if (env('FILESYSTEM_DRIVER') == 's3') {
            return env('AWS_URL') . '/';
        } else {
            // Match my_asset(): in local (php artisan serve) the document root is already
            // /public, so adding another /public prefix would break URLs like
            // /public/uploads/all/... (they would become /public/public/uploads/all/...).
            if (env('APP_ENV') === 'local') {
                $base = app('url')->asset('', null); // e.g. http://127.0.0.1:8000
                return rtrim($base, '/') . '/';
            }

            return getBaseURL() . 'public/';
        }
    }
}


if (!function_exists('localize_internal_url')) {
    function localize_internal_url($url)
    {
        if (empty($url)) {
            return '#';
        }

        // If URL is not absolute (no scheme), return as-is
        if (!preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $parsed = parse_url($url);
        if ($parsed === false) {
            return $url;
        }

        $host = isset($parsed['host']) ? strtolower($parsed['host']) : '';

        // Known internal domains for PastCart/PastCard
        $internalHosts = [
            'pastcard.shop',
            'www.pastcard.shop',
            'pastcart.shop',
            'www.pastcart.shop',
        ];

        // Leave truly external links untouched
        if (!in_array($host, $internalHosts, true)) {
            return $url;
        }

        $path = isset($parsed['path']) ? $parsed['path'] : '/';
        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

        $base = request()->getSchemeAndHttpHost();

        return $base . $path . $query . $fragment;
    }
}


if (!function_exists('isUnique')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    function isUnique($email)
    {
        $user = \App\Models\User::where('email', $email)->first();

        if ($user == null) {
            return '1'; // $user = null means we did not get any match with the email provided by the user inside the database
        } else {
            return '0';
        }
    }
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = null, $lang = false)
    {
        $settings = Cache::remember('business_settings', 86400, function () {
            return BusinessSetting::all();
        });

        if ($lang == false) {
            $setting = $settings->where('type', $key)->first();
        } else {
            $setting = $settings->where('type', $key)->where('lang', $lang)->first();
            $setting = !$setting ? $settings->where('type', $key)->first() : $setting;
        }
        return $setting == null ? $default : $setting->value;
    }
}

function hex2rgba($color, $opacity = false)
{
    return Colorcodeconverter::convertHexToRgba($color, $opacity);
}

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        if (Auth::check() && (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff')) {
            return true;
        }
        return false;
    }
}

if (!function_exists('isSeller')) {
    function isSeller()
    {
        if (Auth::check() && Auth::user()->user_type == 'seller') {
            return true;
        }
        return false;
    }
}

if (!function_exists('isCustomer')) {
    function isCustomer()
    {
        if (Auth::check() && Auth::user()->user_type == 'customer') {
            return true;
        }
        return false;
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// duplicates m$ excel's ceiling function
if (!function_exists('ceiling')) {
    function ceiling($number, $significance = 1)
    {
        return (is_numeric($number) && is_numeric($significance)) ? (ceil($number / $significance) * $significance) : false;
    }
}

if (!function_exists('get_images')) {
    function get_images($given_ids, $with_trashed = false)
    {
        if (is_array($given_ids)) {
            $ids = $given_ids;
        } elseif ($given_ids == null) {
            $ids = [];
        } else {
            $ids = explode(",", $given_ids);
        }


        return $with_trashed
            ? Upload::withTrashed()->whereIn('id', $ids)->get()
            : Upload::whereIn('id', $ids)->get();
    }
}

//for api
if (!function_exists('get_images_path')) {
    function get_images_path($given_ids, $with_trashed = false)
    {
        $paths = [];
        $images = get_images($given_ids, $with_trashed);
        if (!$images->isEmpty()) {
            foreach ($images as $image) {
                $paths[] = !is_null($image) ? $image->file_name : "";
            }
        }

        return $paths;
    }
}

//for api
if (!function_exists('checkout_done')) {
    function checkout_done($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::find($combined_order_id);

        foreach ($combined_order->orders as $key => $order) {
            $order->payment_status = 'paid';
            $order->payment_details = $payment;
            $order->save();

            try {
                NotificationUtility::sendOrderPlacedNotification($order);
                calculateCommissionAffilationClubPoint($order);
            } catch (\Exception $e) {

            }
        }
    }
}

//for api
if (!function_exists('wallet_payment_done')) {
    function wallet_payment_done($user_id, $amount, $payment_method, $payment_details)
    {
        $user = \App\Models\User::find($user_id);
        $user->balance = $user->balance + $amount;
        $user->save();

        $wallet = new Wallet;
        $wallet->user_id = $user->id;
        $wallet->amount = $amount;
        $wallet->payment_method = $payment_method;
        $wallet->payment_details = $payment_details;
        $wallet->save();
    }
}

if (!function_exists('purchase_payment_done')) {
    function purchase_payment_done($user_id, $package_id)
    {
        $user = User::findOrFail($user_id);
        $user->customer_package_id = $package_id;
        $customer_package = CustomerPackage::findOrFail($package_id);
        $user->remaining_uploads += $customer_package->product_upload;
        $user->save();

        return 'success';
    }
}

//Commission Calculation
if (!function_exists('calculateCommissionAffilationClubPoint')) {
    function calculateCommissionAffilationClubPoint($order)
    {
        (new CommissionController)->calculateCommission($order);

        if (addon_is_activated('affiliate_system')) {
            // (new AffiliateController)->processAffiliatePoints($order); // Commented out - addon not installed
        }

        if (addon_is_activated('club_point')) {
            if ($order->user != null) {
                // (new ClubPointController)->processClubPoints($order); // Commented out - addon not installed
            }
        }

        $order->commission_calculated = 1;
        $order->save();
    }
}

// Addon Activation Check
if (!function_exists('addon_is_activated')) {
    function addon_is_activated($identifier, $default = null)
    {
        $addons = Cache::remember('addons', 86400, function () {
            return Addon::all();
        });

        $activation = $addons->where('unique_identifier', $identifier)->where('activated', 1)->first();
        return $activation == null ? false : true;
    }
}
