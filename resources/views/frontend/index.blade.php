@extends('frontend.layouts.app')

@section('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .category-image-placeholder {
            width: 100%;
            padding-top: 62.5%;
            background-color: #e0e0e0;
            border-radius: 4px;
        }
    </style>
@endsection

@section('content')

    @if (get_setting('home_slider_images') != null)
        <div class="slider-show hover-to-show nav-style-1 nav-position-center">
            <div>
                <div class="owl-carousel home-owl-carousel owl-theme">
                    @php $slider_images = json_decode(get_setting('home_slider_images'), true);  @endphp
                    @foreach ($slider_images as $key => $value)
                        <div class="item">
                            <a title="{{ env('APP_NAME')}}" href="{{ json_decode(get_setting('home_slider_links'), true)[$key] }}">
                                <img class="img-fluid" src="{{ uploaded_asset($slider_images[$key]) }}" alt="{{ env('APP_NAME')}}">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="title-block-1">
        <div class="block-home categories-slider background-gray">
            <div class="container">
                <div class="hover-to-show nav-style-2 nav-position-center">
                    <div class="block-categories">
                        <div class="block-title">
                            <strong>Popular Categories</strong>
                        </div>
                        <div class="block-content" data-owl="owl-slider">
                            <div class="owl-carousel popular-owl-carousel owl-theme">
                                @php
                                    $popularRootCategories = \App\Models\Category::where('level', 0)->get()
                                        ->map(function ($category) {
                                            $category->product_count = category_product_count($category->id);
                                            return $category;
                                        })
                                        ->filter(function ($category) {
                                            return $category->product_count > 0;
                                        })
                                        ->sortByDesc('product_count');
                                @endphp
                                @foreach ($popularRootCategories as $key => $category)
                                <div class="item">
                                    <div class="content-box">
                                        <div class="image-cat">
                                            <a href="{{ route('products.category', $category->slug) }}" title="{{ $category->getTranslation('name') }}">
                                                @if (uploaded_asset($category->banner) != null)
                                                    <img src="{{ uploaded_asset($category->banner) }}" alt="{{ $category->getTranslation('name') }}">
                                                @else
                                                    <div class="category-image-placeholder"></div>
                                                @endif
                                            </a>
                                        </div>
                                        <div class="child-cat">
                                            <div class="cat-title">
                                                <a href="{{ route('products.category', $category->slug) }}" title="{{ $category->getTranslation('name') }}">{{ $category->getTranslation('name') }}</a>
                                            </div>
                                            <ul class="sub-cats">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Banner section 1 --}}
        @php
            $banner_1_imags = json_decode(get_setting('home_banner1_images'));
            $banner_1_link = json_decode(get_setting('home_banner1_links'), true);
            $banner_1_imags = is_array($banner_1_imags) ? $banner_1_imags : [];
            $banner_1_link = is_array($banner_1_link) ? $banner_1_link : [];

            $banner1_img0_id = $banner_1_imags[0] ?? null;
            $banner1_img1_id = $banner_1_imags[1] ?? null;
            $banner1_img2_id = $banner_1_imags[2] ?? null;
            $banner1_img3_id = $banner_1_imags[3] ?? null;
            $banner1_img4_id = $banner_1_imags[4] ?? null;

            $banner1_img0 = $banner1_img0_id ? uploaded_asset($banner1_img0_id) : null;
            $banner1_img1 = $banner1_img1_id ? uploaded_asset($banner1_img1_id) : null;
            $banner1_img2 = $banner1_img2_id ? uploaded_asset($banner1_img2_id) : null;
            $banner1_img3 = $banner1_img3_id ? uploaded_asset($banner1_img3_id) : null;
            $banner1_img4 = $banner1_img4_id ? uploaded_asset($banner1_img4_id) : null;

            $hasAnyBanner1 = !empty($banner_1_imags);
        @endphp
        @if($hasAnyBanner1)
        <div class="block-home product-slider-deal bg-white">
            <div class="container">
                <div class="row">
                    @if(isset($banner_1_imags[0]))
                    <div class="col-sm-4">
                        <div class="banner-image effect-1">
                            <a href="{{ $banner_1_link[0] ?? '#' }}">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" @if($banner1_img0) data-src="{{ $banner1_img0 }}" @endif alt="{{ env('APP_NAME') }}" class="img-fluid lazyload w-100"/>
                            </a>
                        </div>
                    </div>
                    @endif

                    @if(isset($banner_1_imags[1]) || isset($banner_1_imags[2]))
                    <div class="col-sm-4">
                        @if(isset($banner_1_imags[1]))
                        <div class="banner-image effect-1">
                            <a href="{{ $banner_1_link[1] ?? '#' }}">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" @if($banner1_img1) data-src="{{ $banner1_img1 }}" @endif alt="{{ env('APP_NAME') }}" class="img-fluid lazyload w-100"/>
                            </a>
                        </div>
                        @endif

                        @if(isset($banner_1_imags[2]))
                        <div class="banner-image effect-1">
                            <a href="{{ $banner_1_link[2] ?? '#' }}">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" @if($banner1_img2) data-src="{{ $banner1_img2 }}" @endif alt="{{ env('APP_NAME') }}" class="img-fluid lazyload w-100"/>
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if(isset($banner_1_imags[3]) || isset($banner_1_imags[4]))
                    <div class="col-sm-4">
                        @if(isset($banner_1_imags[3]))
                        <div class="banner-image effect-1">
                            <a href="{{ $banner_1_link[3] ?? '#' }}">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" @if($banner1_img3) data-src="{{ $banner1_img3 }}" @endif alt="{{ env('APP_NAME') }}" class="img-fluid lazyload w-100"/>
                            </a>
                        </div>
                        @endif

                        @if(isset($banner_1_imags[4]))
                        <div class="banner-image effect-1">
                            <a href="{{ $banner_1_link[4] ?? '#' }}">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" @if($banner1_img4) data-src="{{ $banner1_img4 }}" @endif alt="{{ env('APP_NAME') }}" class="img-fluid lazyload w-100"/>
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Home category Top --}}
        @if(get_setting('home_categories_top') != null)
            @php $home_categories_top = json_decode(get_setting('home_categories_top')); @endphp
            @foreach ($home_categories_top as $key => $value)
                @php $category = \App\Models\Category::find($value); @endphp
                <div class="block-home categories-slider background-gray">
                    <div class="container">
                        <div class="hover-to-show nav-style-2 nav-position-center">
                            <div class="block-categories">
                                <div class="block-title d-flex align-items-center justify-content-between">
                                    <strong>{{ $category->getTranslation('name') }}</strong>
                                    <a href="{{ route('products.category', $category->slug) }}" class="btn btn-primary btn-sm">{{ translate('View All') }} <i class="klbth-icon-right-arrow"></i></a>
                                </div>
                                <div class="block-content products">
                                    <div class="owl-carousel popular-owl-carousel owl-theme">
                                        @foreach (get_cached_products($category->id) as $key => $product)
                                        <div class="item">
                                            @include('frontend.partials.product_box_2',['product' => $product])
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Flash Deal --}}
        @php
            $flash_deal = \App\Models\FlashDeal::where('status', 1)->where('featured', 1)->first();
        @endphp
        @if($flash_deal != null && strtotime(date('Y-m-d H:i:s')) >= $flash_deal->start_date && strtotime(date('Y-m-d H:i:s')) <= $flash_deal->end_date)
        <div class="block-home product-slider-deal bg-white">
            <div class="container">
                <div class="hover-to-show nav-style-2 nav-position-center">
                    <div class="block block-filterproducts products-btn">
                        <div id="filterproducts_0" class="grid products-grid">
                            <div class="row">
                                <div class="col-lg-3 col-sm-12 col-xs-12">
                                    <div class="title-countdown-slider ">
                                        <div class="block-title">
                                            <strong>{{ translate('Hot Deals') }}</strong>
                                            <p class="posttext"></p>
                                        </div>
                                        <div class="title-deals">Hurry Up! Offer End In:</div>
                                        <div class="aiz-count-down deals-countdown d-flex justify-content-sm-around justify-content-md-start" data-date="{{ date('Y/m/d H:i:s', $flash_deal->end_date) }}"></div>
                                    </div>
                                </div>
                                <div class="col-lg-9 col-sm-12 col-xs-12">
                                    <div class="elementor-widget">
                                        <div class="elementor-element elementor-element-ce97da5 elementor-widget elementor-widget-machic-product-grid">
                                            <div class="elementor-widget-container">
                                                <div class="slider-content products">
                                                    <div class="owl-carousel flash-owl-carousel owl-theme list items product-items filterproducts">
                                                        @foreach ($flash_deal->flash_deal_products->take(20) as $key => $flash_deal_product)
                                                            @php
                                                                $product = \App\Models\Product::find($flash_deal_product->product_id);
                                                            @endphp
                                                            @if($product != null && $product->published != 0)
                                                                <div class="carousel-box">
                                                                    @include('frontend.partials.product_box_2',['product' => $product])
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Home category Bottom --}}
        @if(get_setting('home_categories_bottom') != null)
            @php $home_categories_bottom = json_decode(get_setting('home_categories_bottom')); @endphp
            @foreach ($home_categories_bottom as $key => $value)
                @php $category = \App\Models\Category::find($value); @endphp
                <div class="block-home bottom-categories background-gray">
                    <div class="container">
                        <div class="hover-to-show nav-style-2 nav-position-center">
                            <div class="block-categories">
                                <div class="block-title d-flex align-items-center justify-content-between">
                                    <strong>{{ $category->getTranslation('name') }}</strong>
                                    <a href="{{ route('products.category', $category->slug) }}" class="btn btn-primary btn-sm">{{ translate('View All') }} <i class="klbth-icon-right-arrow"></i></a>
                                </div>
                                <div class="block-content products">
                                    <div class="owl-carousel popular-owl-carousel owl-theme">
                                        @foreach (get_cached_products($category->id) as $key => $product)
                                        <div class="item">
                                            @include('frontend.partials.product_box_2',['product' => $product])
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
            <div class="block-home coupon-slider">
                <div class="container">
                    <div class="hover-to-show nav-style-2 nav-position-center">
                        <div class="block-categories">

                            <div class="block-content products">
                                <div class="owl-carousel coupon-owl-carousel owl-theme">
                                @php
                                    $coupons = \App\Models\Coupon::getList_admin();

                                @endphp
                                @foreach ($coupons as $key => $coupon)
                                    <div class="item coupon-item">
                                        <div class="coupon-holder">
                                            <div class="coupon-detail d-flex justify-content-between">
                                                <div class="text">
                                                    <h4 class="text-light mt-2 fw-600 fs-24">{{ $coupon->title }}</h4>
                                                    <div class="text-light">{{ $coupon->sub_title }}</div>
                                                </div>
                                                <div class="entry-coupon align-self-center text-light">
                                                    <i class="klbth-icon-ticket"></i> <strong>{{ $coupon->code }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @if (count($newest_products) > 0)
            <div class="block-home categories-slider">
                <div class="container">
                    <div class="hover-to-show nav-style-2 nav-position-center">
                        <div class="block-categories">
                            <div class="block-title d-flex align-items-center justify-content-between">
                                <strong>{{ translate('Recently published') }}</strong>
                            </div>
                            <div class="block-content products">
                                <div class="owl-carousel popular-owl-carousel owl-theme">
                                    @foreach ($newest_products as $key => $new_product)

                                    <div class="item">
                                        @include('frontend.partials.product_box_2', ['product' => $new_product])
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>


    {{-- <div class="home-banner-area mb-4 pt-3">
        <div class="container">
            <div class="row gutters-10 position-relative">
                <div class="col-lg-3 position-static d-none d-lg-block">
                    @include('frontend.partials.category_menu')
                </div>

                @php
                    $num_todays_deal = count($todays_deal_products);
                @endphp

                @if($num_todays_deal > 0)
                <div class="col-lg-2 order-3 mt-3 mt-lg-0">
                    <div class="bg-white rounded shadow-sm">
                        <div class="bg-soft-primary rounded-top p-3 d-flex align-items-center justify-content-center">
                            <span class="fw-600 fs-16 mr-2 text-truncate">
                                {{ translate('Todays Deal') }}
                            </span>
                            <span class="badge badge-primary badge-inline">{{ translate('Hot') }}</span>
                        </div>
                        <div class="c-scrollbar-light overflow-auto h-lg-400px p-2 bg-primary rounded-bottom">
                            <div class="gutters-5 lg-no-gutters row row-cols-2 row-cols-lg-1">
                            @foreach ($todays_deal_products as $key => $product)
                                @if ($product != null)
                                <div class="col mb-2">
                                    <a href="{{ route('product', $product->slug) }}" class="d-block p-2 text-reset bg-white h-100 rounded">
                                        <div class="row gutters-5 align-items-center">
                                            <div class="col-xxl">
                                                <div class="img">
                                                    <img
                                                        class="lazyload img-fit h-140px h-lg-80px"
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                                                        alt="{{ $product->getTranslation('name') }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-xxl">
                                                <div class="fs-16">
                                                    <span class="d-block text-primary fw-600">{{ home_discounted_base_price($product) }}</span>
                                                    @if(home_base_price($product) != home_discounted_base_price($product))
                                                        <del class="d-block opacity-70">{{ home_base_price($product) }}</del>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endif
                            @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div> --}}

    {{-- Featured Section --}}
    <div id="section_featured"></div>

    {{-- Best Selling  --}}
    <div id="section_best_selling"></div>

    <!-- Auction Product -->
    @if(addon_is_activated('auction'))
        <div id="auction_products"></div>
    @endif

    {{-- Banner Section 2 --}}
    {{--@if (get_setting('home_banner2_images') != null)
    <div class="mb-4">
        <div class="container">
            <div class="row gutters-10">
                @php $banner_2_imags = json_decode(get_setting('home_banner2_images')); @endphp
                @foreach ($banner_2_imags as $key => $value)
                    <div class="col-xl col-md-6">
                        <div class="mb-3 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner2_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_2_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif--}}

    {{-- Classified Product --}}
    @if(get_setting('classified_product') == 1)
        @php
            $classified_products = \App\Models\CustomerProduct::where('status', '1')->where('published', '1')->take(10)->get();
        @endphp
           @if (count($classified_products) > 0)
               <section class="mb-4">
                   <div class="container">
                       <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                            <div class="d-flex mb-3 align-items-baseline border-bottom">
                                <h3 class="h5 fw-700 mb-0">
                                    <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Classified Ads') }}</span>
                                </h3>
                                <a href="{{ route('customer.products') }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ translate('View More') }}</a>
                            </div>
                           <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                               @foreach ($classified_products as $key => $classified_product)
                                   <div class="carousel-box">
                                        <div class="aiz-card-box border border-light rounded hov-shadow-md my-2 has-transition">
                                            <div class="position-relative">
                                                <a href="{{ route('customer.product', $classified_product->slug) }}" class="d-block">
                                                    <img
                                                        class="img-fit lazyload mx-auto h-140px h-md-210px"
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($classified_product->thumbnail_img) }}"
                                                        alt="{{ $classified_product->getTranslation('name') }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                    >
                                                </a>
                                                <div class="absolute-top-left pt-2 pl-2">
                                                    @if($classified_product->conditon == 'new')
                                                       <span class="badge badge-inline badge-success">{{translate('new')}}</span>
                                                    @elseif($classified_product->conditon == 'used')
                                                       <span class="badge badge-inline badge-danger">{{translate('Used')}}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="p-md-3 p-2 text-left">
                                                <div class="fs-15 mb-1">
                                                    <span class="fw-700 text-primary">{{ single_price($classified_product->unit_price) }}</span>
                                                </div>
                                                <h3 class="fw-600 fs-13 text-truncate-2 lh-1-4 mb-0 h-35px">
                                                    <a href="{{ route('customer.product', $classified_product->slug) }}" class="d-block text-reset">{{ $classified_product->getTranslation('name') }}</a>
                                                </h3>
                                            </div>
                                       </div>
                                   </div>
                               @endforeach
                           </div>
                       </div>
                   </div>
               </section>
           @endif
       @endif

    {{-- Banner Section 2 --}}
    {{--@if (get_setting('home_banner3_images') != null)
    <div class="mb-4">
        <div class="container">
            <div class="row gutters-10">
                @php $banner_3_imags = json_decode(get_setting('home_banner3_images')); @endphp
                @foreach ($banner_3_imags as $key => $value)
                    <div class="col-xl col-md-6">
                        <div class="mb-3 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner3_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_3_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif--}}

    {{-- Best Seller --}}
    <div id="section_best_sellers">

    </div>

    {{-- Top 10 categories and Brands --}}
    @if (get_setting('top10_categories') != null || get_setting('top10_brands') != null)
    <section class="mb-4">
        <div class="container">
            <div class="row gutters-10">
                @php
                    $top10CategoriesValue = get_setting('top10_categories');
                    $top10CategoryIds = $top10CategoriesValue ? json_decode($top10CategoriesValue, true) : [];
                    $top10CategoryIds = is_array($top10CategoryIds) ? $top10CategoryIds : [];

                    if (!empty($top10CategoryIds)) {
                        $top10Categories = \App\Models\Category::whereIn('id', $top10CategoryIds)->get();
                    } else {
                        $top10Categories = \App\Models\Category::inRandomOrder()->take(10)->get();
                    }
                @endphp

                @if ($top10Categories->count() > 0)
                    <div class="col-lg-12">
                        <div class="d-flex pb-2 mb-3 align-items-center border-bottom justify-content-between">
                            <h3 class="fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Top 10 Categories') }}</span>
                            </h3>
                            <a href="{{ route('categories.all') }}" class="btn btn-primary btn-sm shadow-md">{{ translate('View All Categories') }}</a>
                        </div>
                        <div class="row row-cols-lg-5 row-cols-md-5 row-cols-sm-2 gutters-5 top10">
                            @foreach ($top10Categories as $category)
                                <div class="col col-md-3">
                                    <a href="{{ route('products.category', $category->slug) }}" class="bg-white border d-block text-reset rounded p-2 hov-shadow-md mb-2">
                                        <div class="row align-items-center">
                                            <div class="col-4 text-center">
                                                <img
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($category->banner) }}"
                                                    alt="{{ $category->getTranslation('name') }}"
                                                    class="img-fluid img lazyload h-60px"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </div>
                                            <div class="col-6">
                                                <div class="text-truncat-2 pl-3 fs-24 fw-600 text-left">{{ $category->getTranslation('name') }}</div>
                                            </div>
                                            <div class="col-2 text-center">
                                                <i class="la la-angle-right text-primary"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <!-- @if (get_setting('top10_brands') != null)
                    <div class="col-lg-6">
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h3 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Top 10 Brands') }}</span>
                            </h3>
                            <a href="{{ route('brands.all') }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ translate('View All Brands') }}</a>
                        </div>
                        <div class="row gutters-5">
                            @php $top10_brands = json_decode(get_setting('top10_brands')); @endphp
                            @foreach ($top10_brands as $key => $value)
                                @php $brand = \App\Models\Brand::find($value); @endphp
                                @if ($brand != null)
                                    <div class="col-sm-6">
                                        <a href="{{ route('products.brand', $brand->slug) }}" class="bg-white border d-block text-reset rounded p-2 hov-shadow-md mb-2">
                                            <div class="row align-items-center no-gutters">
                                                <div class="col-4 text-center">
                                                    <img
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($brand->logo) }}"
                                                        alt="{{ $brand->getTranslation('name') }}"
                                                        class="img-fluid img lazyload h-60px"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                    >
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-truncate-2 pl-3 fs-14 fw-600 text-left">{{ $brand->getTranslation('name') }}</div>
                                                </div>
                                                <div class="col-2 text-center">
                                                    <i class="la la-angle-right text-primary"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif -->
            </div>
        </div>
    </section>
    @endif

    <div class="title-block-1">
        <div class="block-home policy-shop">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-sm-6 col-xs-12">
                        <div class="item">
                            <div class="icon">
                                <img class="mark-lazy" src="{{ static_asset('assets/img/icon-shipping.png') }}" data-src="{{ static_asset('assets/img/icon-shipping.png') }}" alt="">
                            </div>
                            <div class="info">
                                <h3>Free Shipping</h3>
                                <p>Free delivery to your home</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-xs-12">
                        <div class="item">
                            <div class="icon">
                                <img class="mark-lazy" src="{{ static_asset('assets/img/icon-guarantee.png') }}" data-src="{{ static_asset('assets/img/icon-guarantee.png') }}" alt="">
                            </div>
                            <div class="info">
                                <h3>Money Guarantee</h3>
                                <p>30 days back</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-xs-12">
                        <div class="item">
                            <div class="icon">
                                <img class="mark-lazy" src="{{ static_asset('assets/img/icon-payment.png') }}" data-src="{{ static_asset('assets/img/icon-payment.png') }}" alt="">
                            </div>
                            <div class="info">
                                <h3>Payment Method</h3>
                                <p>Secure System</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-xs-12">
                        <div class="item">
                            <div class="icon">
                                <img class="mark-lazy" src="{{ static_asset('assets/img/icon-support.png') }}" data-src="{{ static_asset('assets/img/icon-support.png') }}" alt="">
                            </div>
                            <div class="info">
                                <h3>Online Support</h3>
                                <p>24 hours on day</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function(){
            $('.home-owl-carousel').owlCarousel({
                loop: true,
                margin: 10,
                responsiveClass: true,
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true,
                responsive: {
                    0:{
                        items:1,
                        nav:true
                    }
                },
                dots: false
            });
            $('.popular-owl-carousel').owlCarousel({
                loop: true,
                margin: 30,
                autoHeight: true,
                responsiveClass: true,
                autoplay: false,
                autoplayTimeout: 6000,
                autoplayHoverPause: true,
                responsive:{
                    0:{
                        items:1,
                        nav:true
                    },
                    480:{
                        items:2,
                        nav:true
                    },
                    768:{
                        items:3,
                        nav:true
                    },
                    992:{
                        items:4,
                        nav:true
                    },
                    1200:{
                        items:5,
                        nav:true
                    },
                    1400:{
                        items:5,
                        nav:true
                    }
                },
                dots: false,
                checkVisible: false
            });
            $('.coupon-owl-carousel').owlCarousel({
                loop: false,
                margin: 30,
                autoHeight: true,
                responsiveClass: true,
                autoplay: true,
                autoplayTimeout: 6000,
                autoplayHoverPause: true,
                responsive:{
                    0:{
                        items:1
                    }
                },
                nav:false,
                dots: true,
                checkVisible: false
            });
            $('.flash-owl-carousel').owlCarousel({
                loop: true,
                margin: 30,
                autoHeight:true,
                responsiveClass: true,
                autoplay: true,
                autoplayTimeout: 6000,
                autoplayHoverPause: true,
                responsive:{
                    0:{
                        items:1,
                        nav:true
                    },
                    768:{
                        items:2,
                        nav:true
                    },
                    992:{
                        items:3,
                        nav:true
                    },
                    1200:{
                        items:4,
                        nav:true
                    },
                    1400:{
                        items:5,
                        nav:true
                    }
                },
                dots: false,
                checkVisible: false
            });
            // $.post('{{ route('home.section.featured') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#section_featured').html(data);
            //     AIZ.plugins.slickCarousel();
            // });
            // $.post('{{ route('home.section.best_selling') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#section_best_selling').html(data);
            //     AIZ.plugins.slickCarousel();
            // });
            // $.post('{{ route('home.section.auction_products') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#auction_products').html(data);
            //     AIZ.plugins.slickCarousel();
            // });
            // $.post('{{ route('home.section.home_categories') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#section_home_categories').html(data);
            //     AIZ.plugins.slickCarousel();
            // });
            $.post('{{ route('home.section.best_sellers') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_best_sellers').html(data);
                AIZ.plugins.slickCarousel();
            });
        });
    </script>
@endsection
