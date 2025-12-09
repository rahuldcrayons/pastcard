<div class="modal-body p-4 c-scrollbar-light">
    <div class="row">
        <div class="col-lg-6">
            <div class="row gutters-10 flex-row-reverse">
                @php
                    $photos = explode(',',$product->photos);
                @endphp
                <div class="col">
                    <div style="display:none;" class="html5gallery" data-responsive="true" data-skin="horizontal" data-effect="slide" data-width="480" data-height="272" data-showimagetoolbox="none" data-showtitle="false" data-carouselbgcolorend="false" data-carouseltopborder="false" data-carouselbottomborder="false"  data-galleryshadow="false" data-slideshadow="false" data-thumbmargin="20" data-thumbimagebordercolor="#dee2e6" data-thumbunselectedimagebordercolor="#dee2e6">
                        @if ($product->thumbnail_img != null)
							<a href="{{ uploaded_asset($product->thumbnail_img) }}"><img src="{{ uploaded_asset($product->thumbnail_img) }}" class="img-fluid lazyload mw-100 size-50px" alt=""></a>
						@else
							@foreach ($photos as $key => $photo)
								<a href="{{ uploaded_asset($photo) }}"><img src="{{ uploaded_asset($photo) }}" class="img-fluid lazyload mw-100 size-50px" alt=""></a>
							@endforeach
						@endif
                    </div>
                
                </div>
                
            </div>
        </div>

        <div class="col-lg-6">
            <div class="text-left">
                <h2 class="mb-2 mt-1 fw-600">
                    <a href="{{ route('product', $product->slug) }}">
                        {{  $product->getTranslation('name')  }}
                    </a>    
                </h2>

                @if(home_price($product) != home_discounted_price($product))
                    <div class="no-gutters mt-3 d-flex align-items-start">
                        <div class="">
                            <div class="opacity-50 mt-2">{{ translate('Price')}}:</div>
                        </div>
                        <div class="ml-2">
                            <div class="opacity-60">
                                <del>
                                    {{ home_price($product) }}
                                    @if($product->unit != null)
                                        <span>/{{ $product->getTranslation('unit') }}</span>
                                    @endif
                                </del>
                            </div>
                        </div>
                    </div>

                    <div class="no-gutters mt-2">
                        <div class="col-2">
                            <div class="opacity-50">{{ translate('Discount Price')}}:</div>
                        </div>
                        <div class="col-10">
                            <div class="">
                                <strong class="h2 fw-600 text-primary">
                                    {{ home_discounted_price($product) }}
                                </strong>
                                @if($product->unit != null)
                                    <span class="opacity-70">/{{ $product->getTranslation('unit') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="no-gutters mt-3 d-flex align-items-start">
                        <div class="">
                            <div class="opacity-50">{{ translate('Price')}}:</div>
                        </div>
                        <div class="ml-2">
                            <div class="">
                                <strong class="h2 fw-600 text-primary">
                                    {{ home_discounted_price($product) }}
                                </strong>
                                <span class="opacity-70">/{{ $product->unit }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if (addon_is_activated('club_point') && $product->earn_point > 0)
                    <div class="no-gutters mt-4">
                        <div class="col-2">
                            <div class="opacity-50">{{  translate('Club Point') }}:</div>
                        </div>
                        <div class="col-10">
                            <div class="d-inline-block club-point bg-soft-primary px-3 py-1 border">
                                <span class="strong-700">{{ $product->earn_point }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <hr>

                @php
                    $qty = 0;
                    foreach ($product->stocks as $key => $stock) {
                        $qty += $stock->qty;
                    }
                @endphp

                <form id="option-choice-form">
                    @csrf
                    <input type="hidden" name="id" value="{{ $product->id }}">

                    <!-- Quantity + Add to cart -->
                    @if($product->digital !=1)
                        @if ($product->choice_options != null)
                            @foreach (json_decode($product->choice_options) as $key => $choice)

                                <div class=" no-gutters">
                                    <div class="col-2">
                                        <div class="opacity-50 mt-2 ">{{ \App\Models\Attribute::find($choice->attribute_id)->getTranslation('name') }}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="aiz-radio-inline">
                                            @foreach ($choice->values as $key => $value)
                                            <label class="aiz-megabox pl-0 mr-2">
                                                <input
                                                    type="radio"
                                                    name="attribute_id_{{ $choice->attribute_id }}"
                                                    value="{{ $value }}"
                                                    @if($key == 0) checked @endif
                                                >
                                                <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center py-2 px-3 mb-2">
                                                    {{ $value }}
                                                </span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        @endif

                        @if (count(json_decode($product->colors)) > 0)
                            <div class="no-gutters">
                                <div class="col-2">
                                    <div class="opacity-50 mt-2">{{ translate('Color')}}:</div>
                                </div>
                                <div class="col-10">
                                    <div class="aiz-radio-inline">
                                        @foreach (json_decode($product->colors) as $key => $color)
                                        <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip" data-title="{{ \App\Models\Color::where('code', $color)->first()->name }}">
                                            <input
                                                type="radio"
                                                name="color"
                                                value="{{ \App\Models\Color::where('code', $color)->first()->name }}"
                                                @if($key == 0) checked @endif
                                            >
                                            <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-2">
                                                <span class="size-30px d-inline-block rounded" style="background: {{ $color }};"></span>
                                            </span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <hr>
                        @endif

                        <div class="no-gutters">
                            <div class="col-2">
                                <div class="opacity-50 mt-2">{{ translate('Quantity')}}:</div>
                            </div>
                            <div class="col-10">
                                <div class="product-quantity d-flex align-items-center">
                                <div class="quantity aiz-plus-minus">
                                    <label class="screen-reader-text" for="quantity_{{ $product->id }}">{{ $product->getTranslation('name') }}</label>
                                    <div class="quantity-button minus" data-type="minus" data-field="quantity" disabled=""></div>
                                    <input type="number" name="quantity" id="quantity_{{ $product->id }}" class="input-number qty" value="1" min="1" max="{{ $product->max_qty }}" lang="en" title="Qty" placeholder="" inputmode="numeric">
                                    <div class="quantity-button plus" data-type="plus" data-field="quantity"></div>
                                </div>
                                   
                                    <div class="avialable-amount opacity-60">
                                        @if($product->stock_visibility_state == 'quantity')
                                        (<span id="available-quantity">{{ $qty }}</span> {{ translate('available')}})
                                        @elseif($product->stock_visibility_state == 'text' && $qty >= 1)
                                            (<span id="available-quantity">{{ translate('In Stock') }}</span>)
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                    @endif

                    <div class="no-gutters pb-3 d-none d-flex align-items-start" id="chosen_price_div">
                        <div class="">
                            <div class="opacity-50">{{ translate('Total Price')}}:</div>
                        </div>
                        <div class="ml-2">
                            <div class="product-price">
                                <strong id="chosen_price" class="h4 fw-600 text-primary"></strong>
                            </div>
                        </div>
                    </div>

                </form>
                <div class="mt-3">
                    @if ($product->digital == 1)
                        <button type="button" class="btn btn-primary buy-now fw-600 add-to-cart" onclick="addToCart()">
                            <i class="la la-shopping-cart"></i>
                            <span class="d-md-inline-block">{{ translate('Add to cart')}}</span>
                        </button>
                    @elseif($qty > 0)
                        @if ($product->external_link != null)
                            <a type="button" class="btn btn-soft-primary mr-2 add-to-cart fw-600" href="{{ $product->external_link }}">
                                <i class="las la-share"></i>
                                <span class="d-none d-md-inline-block">{{ translate($product->external_link_btn)}}</span>
                            </a>
                        @else
                            <button type="button" class="btn btn-primary buy-now fw-600 add-to-cart" onclick="addToCart()">
                                <i class="la la-shopping-cart"></i>
                                <span class="d-md-inline-block">{{ translate('Add to cart')}}</span>
                            </button>
                        @endif
                    @endif
                    <button type="button" class="btn btn-secondary out-of-stock fw-600 d-none" disabled>
                        <i class="la la-cart-arrow-down"></i>{{ translate('Out of Stock')}}
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
<script src="https://vjs.zencdn.net/7.18.1/video.min.js"></script>
    <script src="{{ static_asset('assets/js/html5gallery.js') }}"></script>
<script type="text/javascript">
    $('#option-choice-form input').on('change', function () {
        getVariantPrice();
    });
</script>
