@extends('frontend.layouts.app')

@section('meta_title'){{ $shop->meta_title }}@stop

@section('meta_description'){{ $shop->meta_description }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $shop->meta_title }}">
    <meta itemprop="description" content="{{ $shop->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($shop->logo) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="website">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $shop->meta_title }}">
    <meta name="twitter:description" content="{{ $shop->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($shop->meta_img) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $shop->meta_title }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ route('shop.visit', $shop->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($shop->logo) }}" />
    <meta property="og:description" content="{{ $shop->meta_description }}" />
    <meta property="og:site_name" content="{{ $shop->name }}" />
@endsection

@section('content')
    <section class="mb-4 bg-white" style="padding-top: 70px;">
        <div class="container">
            <div class="row">
                <div class="col-md-12 mx-auto">
                    <div class="d-flex justify-content-center" style="background-image: url(@if ($shop->sliders !== null) {{ uploaded_asset($shop->sliders) }} @else {{ static_asset('assets/img/storebg.png') }} @endif);padding: 40px;height:300px;">
						<div class="row" style="background: rgba(0, 0, 0, 0.4);padding:20px;border-radius:10px;">
							<div class="col-md-4">
								<img
									height="70"
									class="lazyload"
									src="{{ static_asset('assets/img/placeholder.jpg') }}"
									data-src="@if ($shop->logo !== null) {{ uploaded_asset($shop->logo) }} @else {{ static_asset('assets/img/placeholder.jpg') }} @endif"
									alt="{{ $shop->name }}"
								style="max-width:180px;object-fit:cover;">
							</div>
							<div class="col-md-8">
								<div class="p-4 text-left" >
									<h1 class="h2 mb-0 text-white" style="font-size: 30px;"><b>{{ ucwords($shop->name) }}
										@if ($shop->user->seller->verification_status == 1)
											<span class="ml-2"><i class="fa fa-check-circle" style="color:green"></i></span>
										@else
											<span class="ml-2"><i class="fa fa-times-circle" style="color:red"></i></span>
										@endif
									</b></h1>
									<div class="rating rating-sm mb-1 text-white">
										{{ renderStarRating($shop->user->seller->rating) }}
									</div>
									<div class="location opacity-60 text-white">{{ ucwords($shop->address) }}</div>
									<ul class="mt-5 mt-lg-0 social colored list-inline mb-0">
										@if ($shop->facebook != null)
											<li class="list-inline-item">
												<a href="{{ $shop->facebook }}" class="facebook" target="_blank">
													<i class="lab la-facebook-f"></i>
												</a>
											</li>
										@endif
										@if ($shop->instagram != null)
											<li class="list-inline-item">
												<a href="{{ $shop->instagram }}" class="instagram" target="_blank">
													<i class="lab la-instagram"></i>
												</a>
											</li>
										@endif
										@if ($shop->twitter != null)
											<li class="list-inline-item">
												<a href="{{ $shop->twitter }}" class="twitter" target="_blank">
													<i class="lab la-twitter"></i>
												</a>
											</li>
										@endif
										@if ($shop->google != null)
											<li class="list-inline-item">
												<a href="{{ $shop->google }}" class="google-plus" target="_blank">
													<i class="lab la-google"></i>
												</a>
											</li>
										@endif
										@if ($shop->youtube != null)
											<li class="list-inline-item">
												<a href="{{ $shop->youtube }}" class="youtube" target="_blank">
													<i class="lab la-youtube"></i>
												</a>
											</li>
										@endif
									</ul>
								</div>
							</div>
						</div>
                    </div>
                </div>
            </div>
            <div class="border-bottom mt-5"></div>
            <div class="row align-items-center">
                <div class="col-lg-12 order-2 order-lg-0">
                    <ul class="list-inline mb-0 text-center text-lg-left">
                        <li class="list-inline-item ">
                            <a class="text-reset d-inline-block fw-600 fs-15 p-3 @if(!isset($type)) border-bottom border-primary border-width-2 @endif" href="{{ route('shop.visit', $shop->slug) }}" style="font-size: 1.0em !important;">{{ translate('Store Home')}}</a>
                        </li>
                        <li class="list-inline-item ">
                            <a class="text-reset d-inline-block fw-600 fs-15 p-3 @if(isset($type) && $type == 'top-selling') border-bottom border-primary border-width-2 @endif" href="{{ route('shop.visit.type', ['slug'=>$shop->slug, 'type'=>'top-selling']) }}" style="font-size: 1.0em !important;">{{ translate('Top Selling')}}</a>
                        </li>
                        <li class="list-inline-item ">
                            <a class="text-reset d-inline-block fw-600 fs-15 p-3 @if(isset($type) && $type == 'all-products') border-bottom border-primary border-width-2 @endif" href="{{ route('shop.visit.type', ['slug'=>$shop->slug, 'type'=>'all-products']) }}" style="font-size: 1.0em !important;">{{ translate('All Products')}}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    @if (!isset($type))
        <section class="mb-5">
            <div class="container">
                <div class="aiz-carousel dots-inside-bottom mobile-img-auto-height" data-arrows="true" data-dots="true" data-autoplay="true">
                    @if ($shop->sliders != null)
                        @foreach (explode(',',$shop->sliders) as $key => $slide)
                            <div class="carousel-box">
                                <img class="d-block w-100 lazyload rounded h-200px h-lg-380px img-fit" src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($slide) }}" alt="{{ $key }} offer">
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </section>

        <section class="mb-4">
            <div class="container">
                <div class="text-center mb-4">
                    <h3 class="h3 fw-600 border-bottom">
                        <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Featured Products')}}</span>
                    </h3>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="aiz-carousel gutters-10" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-autoplay='true' data-infinute="true" data-dots="true">
                            @foreach ($shop->user->products->where('published', 1)->where('approved', 1)->where('seller_featured', 1) as $key => $product)
                                <div class="carousel-box">
                                    @include('frontend.partials.product_box_1',['product' => $product])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="mb-4">
        <div class="container">
            <div class="mb-4">
                <h3 class="h3 fw-600 border-bottom">
                    <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">
                        @if (!isset($type))
                            {{ translate('New Arrival Products')}}
                        @elseif ($type == 'top-selling')
                            {{ translate('Top Selling')}}
                        @elseif ($type == 'all-products')
                            {{ translate('All Products')}}
                        @endif
                    </span>
                </h3>
            </div>
            <div class="row gutters-5 row-cols-xxl-5 row-cols-lg-5 row-cols-md-4 row-cols-2">
                @php
                    if (!isset($type)){
                        $products = \App\Models\Product::where('user_id', $shop->user->id)->where('published', 1)->where('approved', 1)->orderBy('created_at', 'desc')->paginate(24);
                    }
                    elseif ($type == 'top-selling'){
                        $products = \App\Models\Product::where('user_id', $shop->user->id)->where('published', 1)->where('approved', 1)->orderBy('num_of_sale', 'desc')->paginate(24);
                    }
                    elseif ($type == 'all-products'){
                        $products = \App\Models\Product::where('user_id', $shop->user->id)->where('published', 1)->where('approved', 1)->paginate(24);
                    }
                @endphp
                @foreach ($products as $key => $product)
                    <div class="col mb-3">
                        @include('frontend.partials.product_box_1',['product' => $product])
                    </div>
                @endforeach
            </div>
            <div class="aiz-pagination aiz-pagination-center mb-4">
                {{ $products->links() }}
            </div>
        </div>
    </section>

	 <div class="block-home coupon-slider">
                <div class="container">
                    <div class="hover-to-show nav-style-2 nav-position-center">
                        <div class="block-categories">

                            <div class="block-content products">
                                <div class="owl-carousel coupon-owl-carousel owl-theme">
                                @php
                                    $coupons = \App\Models\Coupon::getList_seller($shop->user_id);
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

@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"
        integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function() {
            $('.home-owl-carousel').owlCarousel({
                loop: true,
                margin: 10,
                responsiveClass: true,
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true,
                responsive: {
                    0: {
                        items: 1,
                        nav: true
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
                responsive: {
                    0: {
                        items: 1,
                        nav: true
                    },
                    480: {
                        items: 2,
                        nav: true
                    },
                    768: {
                        items: 3,
                        nav: true
                    },
                    992: {
                        items: 4,
                        nav: true
                    },
                    1200: {
                        items: 5,
                        nav: true
                    },
                    1400: {
                        items: 5,
                        nav: true
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
                responsive: {
                    0: {
                        items: 1
                    }
                },
                nav: false,
                dots: true,
                checkVisible: false
            });
            $('.flash-owl-carousel').owlCarousel({
                loop: true,
                margin: 30,
                autoHeight: true,
                responsiveClass: true,
                autoplay: true,
                autoplayTimeout: 6000,
                autoplayHoverPause: true,
                responsive: {
                    0: {
                        items: 1,
                        nav: true
                    },
                    768: {
                        items: 2,
                        nav: true
                    },
                    992: {
                        items: 3,
                        nav: true
                    },
                    1200: {
                        items: 4,
                        nav: true
                    },
                    1400: {
                        items: 5,
                        nav: true
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
            $.post('{{ route('home.section.best_sellers') }}', {
                _token: '{{ csrf_token() }}'
            }, function(data) {
                $('#section_best_sellers').html(data);
                AIZ.plugins.slickCarousel();
            });
        });
    </script>
@endsection
