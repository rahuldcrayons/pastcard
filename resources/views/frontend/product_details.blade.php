@extends('frontend.layouts.app')

@section('meta_title'){{ $detailedProduct->meta_title }}@stop

@section('meta_description'){{ $detailedProduct->meta_description }}@stop

@section('meta_keywords'){{ $detailedProduct->tags }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $detailedProduct->meta_title }}">
    <meta itemprop="description" content="{{ $detailedProduct->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $detailedProduct->meta_title }}">
    <meta name="twitter:description" content="{{ $detailedProduct->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $detailedProduct->meta_title }}" />
    <meta property="og:type" content="og:product" />
    <meta property="og:url" content="{{ route('product', $detailedProduct->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}" />
    <meta property="og:description" content="{{ $detailedProduct->meta_description }}" />
    <meta property="og:site_name" content="{{ get_setting('meta_title') }}" />
    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
    <meta property="product:price:currency" content="{{ \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code }}" />
    <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">
@endsection

@section('style')
    <link href="https://vjs.zencdn.net/7.18.1/video-js.css" rel="stylesheet" />
@endsection

@section('content')

@php
$flash_deal_product = [];
$flash_deal = \App\Models\FlashDeal::where('status', 1)->where('featured', 1)->first();
if($flash_deal != null && strtotime(date('Y-m-d H:i:s')) >= $flash_deal->start_date && strtotime(date('Y-m-d H:i:s')) <= $flash_deal->end_date) {
    $flash_deal_product = in_array($detailedProduct->id, $flash_deal->flash_deal_products->pluck('product_id')->all());
}

$qty = 0;
foreach ($detailedProduct->stocks as $key => $stock) {
    $qty += $stock->qty;
}

$others_cart = \App\Models\Cart::where('product_id', $detailedProduct->id)->count();
$relatedProduct = filter_products(\App\Models\Product::where('category_id', $detailedProduct->category_id)->where('id', '!=', $detailedProduct->id))->get();
if(!empty($relatedProduct)) {
    $relatedProduct = ($relatedProduct->count() >= 5) ? $relatedProduct->random(5) : $relatedProduct->random($relatedProduct->count());
}
$topProduct = filter_products(\App\Models\Product::where('user_id', $detailedProduct->user_id)->orderBy('num_of_sale', 'desc'))->get();
if(!empty($topProduct)) {
    $topProduct = ($topProduct->count() >= 5) ? $topProduct->random(5) : $topProduct->random($topProduct->count());
}
$photos = explode(',', $detailedProduct->photos);
@endphp

<main id="main" class="site-primary" style="font-family: var(--font-primary);">
    <div class="site-content">
        <div class="shop-content">
            <div class="container">
                <div class="single-product-wrapper">
                    <div id="product-{{$detailedProduct->id}}" class="product type-product">
                        <div class="single-product-container">
                            <form id="option-choice-form">
                                <div class="row">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $detailedProduct->id }}">
                                    <div class="col col-12 col-lg-6">
                                        <div style="display:none;" class="html5gallery" data-responsive="true" data-skin="horizontal" data-effect="slide" data-width="480" data-height="272" data-showimagetoolbox="none" data-showtitle="false" data-carouselbgcolorend="false" data-carouseltopborder="false" data-carouselbottomborder="false"  data-galleryshadow="false" data-slideshadow="false" data-thumbmargin="20" data-thumbimagebordercolor="#dee2e6" data-thumbunselectedimagebordercolor="#dee2e6">
                                            @foreach ($photos as $key => $photo)
                                                <a href="{{ uploaded_asset($photo) }}"><img src="{{ uploaded_asset($photo) }}" class="img-fluid lazyload mw-100 size-50px" alt="{{$detailedProduct->name}}"></a>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col col-12 col-lg-6">
                                        <h1 class="product_title entry-title fw-600">{{ $detailedProduct->getTranslation('name') }}</h1>
                                        <div class="product-meta">
                                            @if($detailedProduct->condition != null)
                                                <div class="product-model">
                                                    <span>{{ translate('condition') }} :</span>
                                                    {{ $detailedProduct->condition }}
                                                    @if($detailedProduct->comic)
                                                        @if($detailedProduct->condition_original)
                                                            <span>, {{ translate('Original') }}</span>
                                                        @endif
                                                        @if($detailedProduct->condition_reprint)
                                                            <span>, {{ translate('Reprint') }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endif
                                            @if(!is_null($detailedProduct->stock))
                                                <div class="sku-wrapper">
                                                    <span>SKU: </span>
                                                    <span class="sku">{{ $detailedProduct->stock->sku }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="product-ratings">
                                            <div class="product-rating">
                                                @php
                                                    $total = 0;
                                                    $total += $detailedProduct->reviews->count();
                                                @endphp
                                                <span class="rating">
                                                    {{ renderStarRating($detailedProduct->rating) }}
                                                </span>
                                                <div class="count-rating"><a href="#reviews" class="woocommerce-review-link fs-5 fw-600" rel="nofollow"><span class="count">{{ $total }}</span> {{ translate('reviews')}}</a></div>
                                            </div>
                                        </div>

                                        <div class="klb-single-stock">
                                            @if($qty >= 1)
                                                <div class="product-stock in-stock">{{ translate('In Stock') }}</div>
                                            @else
                                                <div class="product-stock out-of-stock">{{ translate('Out of Stock') }}</div>
                                            @endif
                                        </div>

                                        <div class="product-price">
                                            <span class="price flex-row">
                                                <ins><span class="woocommerce-Price-amount amount"><bdi>{{ home_discounted_base_price($detailedProduct) }}</bdi></span></ins>
                                                @if(home_base_price($detailedProduct) != home_discounted_base_price($detailedProduct))
                                                <del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi>{{ home_base_price($detailedProduct) }}</bdi></span></del>
                                                @endif
                                            </span>
                                        </div>

                                        <div class="product-info">
                                            <div class="product-info-top justify-content-between">
                                                <div class="cart">
                                                    <div class="quantity aiz-plus-minus">
                                                        <label class="screen-reader-text" for="quantity_{{ $detailedProduct->id }}">{{ $detailedProduct->getTranslation('name') }}</label>
                                                        <div class="quantity-button minus" data-type="minus" data-field="quantity" disabled=""></div>
                                                        <input type="number" name="quantity" id="quantity_{{ $detailedProduct->id }}" class="input-number qty" value="1" min="1" max="{{ $detailedProduct->max_qty }}" lang="en" title="Qty" placeholder="" inputmode="numeric">
                                                        <div class="quantity-button plus" data-type="plus" data-field="quantity"></div>
                                                    </div>

                                                    @if ($detailedProduct->external_link != null)
                                                        <a type="button" class="btn btn-primary add_to_cart_button fw-600" href="{{ $detailedProduct->external_link }}">
                                                            <i class="la la-share"></i> {{ translate($detailedProduct->external_link_btn)}}
                                                        </a>
                                                    @else
                                                        <button type="button" class="btn btn-success add_to_cart_button border-0 fw-600" onclick="addToCartsingle({{ $detailedProduct->id }})"><span class="atcpreloader{{ $detailedProduct->id }} text-center" style="display: none;"><i class="las la-spinner la-spin"></i></span> {{ translate('Add to cart')}}</button>
                                                        {{--<button type="button" class="btn btn-success add_to_cart_button border-0 fw-600" onclick="buyNow()">{{ translate('Buy Now')}}</button>--}}
                                                    @endif

                                                    <div class="product-actions">
                                                        <div class="tinv-wraper woocommerce tinv-wishlist tinvwl-shortcode-add-to-cart">
                                                            <div class="tinv-wishlist-clear"></div>
                                                            <a role="button" aria-label="{{ translate('Add to wishlist')}}" class="tinvwl_add_to_wishlist_button tinvwl-icon-heart tinvwl-position-after" onclick="addToWishList({{ $detailedProduct->id }})"><span class="tinvwl_add_to_wishlist-text">{{ translate('Add to wishlist')}}</span></a>
                                                            <div class="tinv-wishlist-clear"></div>
                                                            <div class="tinvwl-tooltip">{{ translate('Add to wishlist')}}</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                    <div class="product-price mt-0" id="chosen_price_div"><span class="price flex-row woocommerce-Price-amount amount"><ins id="chosen_price"></ins></span></div>
                                            </div>

                                            <div class="product-info-bottom text-capitalize">
                                                <div class="info-message">
                                                    <i class="klbth-icon-delivery-box-3"></i>
                                                    <strong>
                                                        @if ($detailedProduct->added_by == 'seller' && get_setting('vendor_system_activation') == 1)
                                                            <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="text-reset">{{ $detailedProduct->user->shop->name }}</a>
                                                        @else
                                                            {{  translate('Inhouse product') }}
                                                        @endif
                                                    </strong>
                                                </div>
                                                @if ($detailedProduct->est_shipping_days)
                                                    <div class="info-message">
                                                        <strong>
                                                        {{ translate('Estimated Shipping Time')}}: {{ $detailedProduct->est_shipping_days }} {{  translate('Days') }}
                                                        </strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @if($others_cart > 0)
                                            <div class="people-have product-message warning">
                                                <i class="klbth-icon-shopping-bag-3"></i> <strong>Other people want this. </strong>
                                                {{$others_cart}} people have this in their carts right now.
                                            </div>
                                        @endif
                                        <div class="product_meta product-categories">
                                            @if(!is_null($detailedProduct->stock))
                                                <span class="sku_wrapper">SKU: <span class="sku">{{ $detailedProduct->stock->sku }}</span></span>
                                            @endif
                                            <span class="posted_in">Category: <a class="text-capitalize" href="{{ route('products.category', $detailedProduct->category->slug) }}" rel="tag">{{ $detailedProduct->category->name}}</a></span>
                                        </div>
                                        <div class="aiz-share"></div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="bg-white mb-3 shadow-sm rounded">
                            <div class="nav border-bottom aiz-nav-tabs">
                                <a href="#tab_default_1" data-toggle="tab" class="p-3 fs-24 fw-600 text-reset active show">{{ translate('Description')}}</a>
                                @if($detailedProduct->video_link != null || $detailedProduct->videos != null)
                                    <a href="#tab_default_2" data-toggle="tab" class="p-3 fs-24 fw-600 text-reset">{{ translate('Video')}}</a>
                                @endif
                                @if($detailedProduct->pdf != null)
                                    <a href="#tab_default_3" data-toggle="tab" class="p-3 fs-24 fw-600 text-reset">{{ translate('Downloads')}}</a>
                                @endif
                                    <a href="#tab_default_4" data-toggle="tab" class="p-3 fs-24 fw-600 text-reset">{{ translate('Reviews')}}</a>
                            </div>

                            <div class="tab-content pt-0">
                                <div class="tab-pane fade active show" id="tab_default_1">
                                    <div class="p-4">
                                        <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                            {!! nl2br($detailedProduct->getTranslation('description')) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="tab_default_2">
                                    <div class="p-4">
                                        @if($detailedProduct->video_provider == 'youtube' || $detailedProduct->video_provider == 'dailymotion' || $detailedProduct->video_provider == 'vimeo')
                                        <div class="embed-responsive embed-responsive-16by9">
                                            @if ($detailedProduct->video_provider == 'youtube' && isset(explode('=', $detailedProduct->video_link)[1]))
                                                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ explode('=', $detailedProduct->video_link)[1] }}"></iframe>
                                            @elseif ($detailedProduct->video_provider == 'dailymotion' && isset(explode('video/', $detailedProduct->video_link)[1]))
                                                <iframe class="embed-responsive-item" src="https://www.dailymotion.com/embed/video/{{ explode('video/', $detailedProduct->video_link)[1] }}"></iframe>
                                            @elseif ($detailedProduct->video_provider == 'vimeo' && isset(explode('vimeo.com/', $detailedProduct->video_link)[1]))
                                                <iframe src="https://player.vimeo.com/video/{{ explode('vimeo.com/', $detailedProduct->video_link)[1] }}" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                            @endif
                                        </div>
                                        @endif
                                        @if($detailedProduct->video_provider == 'other' && isset($detailedProduct->videos))
                                            <video id="my-video" class="video-js vjs-fluid vjs-16-9" controls preload="auto" poster="{{ uploaded_asset($detailedProduct->thumbnail_img) }}" data-setup="{}" >
                                                <source src="{{ uploaded_asset($detailedProduct->videos) }}" type="video/mp4"/>
                                                <source src="{{ uploaded_asset($detailedProduct->videos) }}" type="video/ogg"/>
                                                <source src="{{ uploaded_asset($detailedProduct->videos) }}" type="video/webm"/>
                                                <p class="vjs-no-js">
                                                    To view this video please enable JavaScript, and consider upgrading to a
                                                    web browser that
                                                    <a href="https://videojs.com/html5-video-support/" target="_blank" >supports HTML5 video</a>
                                                </p>
                                            </video>
                                        @endif
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="tab_default_3">
                                    <div class="p-4 text-center ">
                                        <a href="{{ uploaded_asset($detailedProduct->pdf) }}" class="btn btn-primary">{{  translate('Download') }}</a>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="tab_default_4">
                                    <div class="p-4">
                                        <ul class="list-group list-group-flush">
                                            @foreach ($detailedProduct->reviews as $key => $review)
                                                @if($review->user != null)
                                                <li class="media list-group-item d-flex">
                                                    <span class="avatar avatar-md mr-3">
                                                        <img
                                                            class="lazyload"
                                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                            @if($review->user->avatar_original !=null)
                                                                data-src="{{ uploaded_asset($review->user->avatar_original) }}"
                                                            @else
                                                                data-src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                            @endif
                                                        >
                                                    </span>
                                                    <div class="media-body text-left">
                                                        <div class="d-flex justify-content-between">
                                                            <h3 class="fs-15 fw-600 mb-0">{{ $review->user->name }}</h3>
                                                            <span class="rating rating-sm">
                                                                @for ($i=0; $i < $review->rating; $i++)
                                                                    <i class="las la-star active"></i>
                                                                @endfor
                                                                @for ($i=0; $i < 5-$review->rating; $i++)
                                                                    <i class="las la-star"></i>
                                                                @endfor
                                                            </span>
                                                        </div>
                                                        <div class="opacity-60 mb-2">{{ date('d-m-Y', strtotime($review->created_at)) }}</div>
                                                        <p class="comment-text">
                                                            {{ $review->comment }}
                                                        </p>
                                                    </div>
                                                </li>
                                                @endif
                                            @endforeach
                                        </ul>

                                        @if(count($detailedProduct->reviews) <= 0)
                                            <div class="text-center opacity-70">
                                                {{  translate('There have been no reviews for this product yet.') }}
                                            </div>
                                        @endif

                                        @if((Auth::check()) && (isset($pidd)))
                                            @php
                                                $commentable = false;
                                            @endphp

                                            @if($pidd->delivery_status =='delivered' && $pidd->product_id == $detailedProduct->id && \App\Models\Review::where('user_id', Auth::user()->id)->where('product_id', $detailedProduct->id)->first() == null)
                                                 @php
                                                     $commentable = true;
                                                 @endphp
                                            @endif

                                           <!-- @foreach ($detailedProduct->orderDetails as $key => $orderDetail)
                                                @if($orderDetail->order != null && $orderDetail->order->user_id == Auth::user()->id && $orderDetail->delivery_status == 'delivered' && \App\Models\Review::where('user_id', Auth::user()->id)->where('product_id', $detailedProduct->id)->first() == null)
                                                    @php
                                                        $commentable = true;
                                                    @endphp
                                                @endif
                                            @endforeach -->
                                            @if ($commentable)
                                                <div class="pt-4">
                                                    <div class="border-bottom mb-4">
                                                        <h3 class="fs-17 fw-600">
                                                            {{ translate('Write a review')}}
                                                        </h3>
                                                    </div>
                                                    <form class="form-default" role="form" action="{{ route('reviews.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="" class="text-uppercase c-gray-light">{{ translate('Your name')}}</label>
                                                                    <input type="text" name="name" value="{{ Auth::user()->name }}" class="form-control" disabled required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="" class="text-uppercase c-gray-light">{{ translate('Email')}}</label>
                                                                    <input type="text" name="email" value="{{ Auth::user()->email }}" class="form-control" required disabled>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="opacity-60">{{ translate('Rating')}}</label>
                                                            <div class="rating rating-input">
                                                                <label>
                                                                    <input type="radio" name="rating" value="1" required>
                                                                    <i class="las la-star"></i>
                                                                </label>
                                                                <label>
                                                                    <input type="radio" name="rating" value="2">
                                                                    <i class="las la-star"></i>
                                                                </label>
                                                                <label>
                                                                    <input type="radio" name="rating" value="3">
                                                                    <i class="las la-star"></i>
                                                                </label>
                                                                <label>
                                                                    <input type="radio" name="rating" value="4">
                                                                    <i class="las la-star"></i>
                                                                </label>
                                                                <label>
                                                                    <input type="radio" name="rating" value="5">
                                                                    <i class="las la-star"></i>
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="opacity-60">{{ translate('Comment')}}</label>
                                                            <textarea class="form-control" rows="4" name="comment" placeholder="{{ translate('Your review')}}" required></textarea>
                                                        </div>

                                                        <div class="text-right">
                                                            <button type="submit" class="btn btn-primary mt-3">
                                                                {{ translate('Submit review')}}
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <section class="related products site-module related-products mt-30">
                                <div class="module-header">
                                    <h2 class="entry-title text-dark">{{ translate('Related Products')}}</h2>
                                </div><!-- module-header -->
                                <div class="products column-5 mobile-2">
                                    @foreach ($relatedProduct as $key => $related_product)
                                        @include('frontend.partials.product_box_2', ['product' => $related_product])
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <section class="related products site-module related-products mt-20 mb-10">
                                <div class="module-header">
                                    <h2 class="entry-title text-dark">{{ translate('Top Selling Products')}}</h2>
                                </div><!-- module-header -->
                                <div class="products column-5 mobile-2">
                                    @foreach ($topProduct as $key => $top_product)
                                        @include('frontend.partials.product_box_2', ['product' => $top_product])
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{--<section class="mb-4 pt-3">
    <div class="container">
        <div class="bg-white shadow-sm rounded p-3">
            <div class="row">
                <div class="col-xl-5 col-lg-6 mb-4">
                    <div class="sticky-top z-3 row gutters-10">
                        <div class="col order-1 order-md-2">
                            <div class="aiz-carousel product-gallery" data-nav-for='.product-gallery-thumb' data-fade='true' data-auto-height='true'>
                                @foreach ($photos as $key => $photo)
                                    <div class="carousel-box img-zoom rounded">
                                        <img
                                            class="img-fluid lazyload"
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            data-src="{{ uploaded_asset($photo) }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                        >
                                    </div>
                                @endforeach
                                @foreach ($detailedProduct->stocks as $key => $stock)
                                    @if ($stock->image != null)
                                        <div class="carousel-box img-zoom rounded">
                                            <img
                                                class="img-fluid lazyload"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($stock->image) }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            >
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 col-md-auto w-md-80px order-2 order-md-1 mt-3 mt-md-0">
                            <div class="aiz-carousel product-gallery-thumb" data-items='5' data-nav-for='.product-gallery' data-horizontal='true' data-vertical='false' data-vertical-sm='false' data-focus-select='true' data-arrows='true'>
                                @foreach ($photos as $key => $photo)
                                <div class="carousel-box c-pointer border p-1 rounded">
                                    <img
                                        class="lazyload mw-100 size-50px mx-auto"
                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                        data-src="{{ uploaded_asset($photo) }}"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                    >
                                </div>
                                @endforeach
                                @foreach ($detailedProduct->stocks as $key => $stock)
                                    @if ($stock->image != null)
                                        <div class="carousel-box c-pointer border p-1 rounded" data-variation="{{ $stock->variant }}">
                                            <img
                                                class="lazyload mw-100 size-50px mx-auto"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($stock->image) }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            >
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
</section>--}}

@endsection

@section('modal')
    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 h5">{{ translate('Any query about this product')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                {{-- Conversation feature temporarily disabled
                <form class="" action="{{ route('conversations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="form-group">
                            <input type="text" class="form-control mb-3" name="title" value="{{ $detailedProduct->name }}" placeholder="{{ translate('Product Name') }}" required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" rows="8" name="message" required placeholder="{{ translate('Your Question') }}">{{ route('product', $detailedProduct->slug) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600" data-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ translate('Send')}}</button>
                    </div>
                </form>
                --}}
                <div class="modal-body gry-bg px-3 pt-3">
                    <p class="text-center">{{ translate('Query feature is temporarily unavailable. Please contact us directly.') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary fw-600" data-dismiss="modal">{{ translate('Close')}}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-600">{{ translate('Login')}}</h6>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <form class="form-default" role="form" action="{{ route('cart.login.submit') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                @if (addon_is_activated('otp_system'))
                                    <input type="text" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ translate('Email Or Phone')}}" name="email" id="email">
                                @else
                                    <input type="email" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                                @endif
                                @if (addon_is_activated('otp_system'))
                                    <span class="opacity-60">{{  translate('Use country code before number') }}</span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" name="password" class="form-control h-auto form-control-lg" placeholder="{{ translate('Password')}}">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class=opacity-60>{{  translate('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{ route('password.request') }}" class="text-reset opacity-60 fs-14">{{ translate('Forgot password?')}}</a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <button type="submit" class="btn btn-primary btn-block fw-600">{{  translate('Login') }}</button>
                            </div>
                        </form>

                        <div class="text-center mb-3">
                            <p class="text-muted mb-0">{{ translate('Dont have an account?')}}</p>
                            <a href="{{ route('user.registration') }}">{{ translate('Register Now')}}</a>
                        </div>
                        @if(get_setting('google_login') == 1 ||
                            get_setting('facebook_login') == 1 ||
                            get_setting('twitter_login') == 1)
                            <div class="separator mb-3">
                                <span class="bg-white px-3 opacity-60">{{ translate('Or Login With')}}</span>
                            </div>
                            <ul class="list-inline social colored text-center mb-5">
                                @if (get_setting('facebook_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="facebook">
                                            <i class="lab la-facebook-f"></i>
                                        </a>
                                    </li>
                                @endif
                                @if(get_setting('google_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'google']) }}" class="google">
                                            <i class="lab la-google"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('twitter_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'twitter']) }}" class="twitter">
                                            <i class="lab la-twitter"></i>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://vjs.zencdn.net/7.18.1/video.min.js"></script>
    <script src="{{ static_asset('assets/js/html5gallery.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            getVariantPrice();
        });

        function CopyToClipboard(e) {
            var url = $(e).data('url');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(url).select();
            try {
                document.execCommand("copy");
                AIZ.plugins.notify('success', '{{ translate('Link copied to clipboard') }}');
            } catch (err) {
                AIZ.plugins.notify('danger', '{{ translate('Oops, unable to copy') }}');
            }
            $temp.remove();
            // if (document.selection) {
            //     var range = document.body.createTextRange();
            //     range.moveToElementText(document.getElementById(containerid));
            //     range.select().createTextRange();
            //     document.execCommand("Copy");

            // } else if (window.getSelection) {
            //     var range = document.createRange();
            //     document.getElementById(containerid).style.display = "block";
            //     range.selectNode(document.getElementById(containerid));
            //     window.getSelection().addRange(range);
            //     document.execCommand("Copy");
            //     document.getElementById(containerid).style.display = "none";

            // }
            // AIZ.plugins.notify('success', 'Copied');
        }
        function show_chat_modal(){
            @if (Auth::check())
                $('#chat_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }

    </script>
	<script type="text/javascript">
	  $(function() {
		$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
			$('.tab-pane:not(.active)').each(function(idx,el){
			var vid = $(this).find('video');
			if(vid.length && !vid.paused)
			{
			  vid.get(0).pause();
			}
		  });
		});
	  });
	</script>
@endsection
