@php
$qty = 0;
foreach ($product->stocks as $key => $stock) {
    $qty += $stock->qty;
} 
@endphp
<div class="product type-product product-{{ $product->id }} custom-hover bg-white mt-1">
    <div class="product-wrapper product-type-1">
        <div class="product-content">
            <div class="product-stock-status">
                @if($qty >= 1)
                    @if(!empty($product->condition))
                    <div class="product-condition">{{ $product->condition }} </div>
                    @endif
                     @if($product->condition_original == 1) 
                     <div class="product-status">  <span>Original</span></div>
                     @endif
                     @if($product->condition_reprint == 1) 
                     <div class="product-status">  <span>Reprint</span></div>
                     @endif
                    <!-- <div class="product-stock in-stock">{{ translate('In Stock') }}</div> -->
                @else
                    <div class="product-stock out-of-stock">{{ translate('Out of Stock') }}</div>
                @endif
            </div>
            
            <div class="thumbnail-wrapper">
            @if($qty >= 1)    
                @if(discount_in_percentage($product) > 0)
                <div class="corner-ribbon">
                <!-- The container -->
                <div class="corner-ribbon__inner">
                <!-- The ribbon -->
                <div class="corner-ribbon__ribbon">{{discount_in_percentage($product)}}%</div>
                </div>
                </div>
                @endif
            @endif
            
                <a href="{{ route('product', $product->slug) }}">
                    <div class="product-card">
                        <div class="hover-slider-images-toggler">
							@if ($product->thumbnail_img != null)
                            <div class="hover-slider-toggle-pane" data-hover-slider-image="{{ uploaded_asset($product->thumbnail_img) }}" data-hover-slider-i="41"></div>
							@endif
							@php
								$photoIds = [];
								if (!empty($product->photos)) {
									if (is_array($product->photos)) {
										$photoIds = $product->photos;
									} else {
										$photoIds = array_filter(explode(',', $product->photos));
									}
								}
							@endphp
							@if (!empty($photoIds))
								@foreach ($photoIds as $key => $photo)
									<div class="hover-slider-toggle-pane" data-hover-slider-image="{{ uploaded_asset($photo) }}" data-hover-slider-i="42"></div>
								@endforeach
							@endif
                        </div>
                        <!-- <div class="hover-slider-indicator">
                            <div data-hover-slider-i="41" class="hover-slider-indicator-dot active"></div>
                            <div data-hover-slider-i="42" class="hover-slider-indicator-dot"></div>
                            <div data-hover-slider-i="43" class="hover-slider-indicator-dot"></div>
                        </div> -->
						@php
							$thumbnailUrl = $product->thumbnail_img ? uploaded_asset($product->thumbnail_img) : null;
						@endphp
						@if ($thumbnailUrl)
							<img src="{{ $thumbnailUrl }}" class="hover-slider-init" data-options="{&quot;touch&quot;:&quot;end&quot;,&quot;preloadImages&quot;: true }">
						@else
							@php
								$imagethumb = null;
								if (!empty($product->photos)) {
									if (is_array($product->photos)) {
										$photosArray = $product->photos;
									} else {
										$photosArray = array_filter(explode(',', $product->photos));
									}
									if (count($photosArray) > 0) {
										$imagethumb = $photosArray[0];
									}
								}
								$galleryUrl = $imagethumb ? uploaded_asset($imagethumb) : null;
							@endphp
							@if ($galleryUrl)
								<img src="{{ $galleryUrl }}" class="hover-slider-init" data-options="{&quot;touch&quot;:&quot;end&quot;,&quot;preloadImages&quot;: true }">
							@else
								<div class="product-image-placeholder" style="width: 100%; padding-top: 100%; background-color: #e0e0e0; border-radius: 4px;"></div>
							@endif
						@endif
                    </div>
                </a>
                <div class="product-buttons">
                    <div class="tinv-wraper woocommerce tinv-wishlist tinvwl-shortcode-add-to-cart" data-tinvwl_product_id="276">
                        <div class="tinv-wishlist-clear"></div>
                        <a href="javascript:void(0)" onclick="addToWishList({{ $product->id }})" role="button" tabindex="0" aria-label="{{ translate('Add to wishlist') }}" class="tinvwl_add_to_wishlist_button tinvwl-icon-heart tinvwl-position-after"><span class="tinvwl_add_to_wishlist-text">{{ translate('Add to wishlist') }}</span></a>
                        <div class="tinv-wishlist-clear"></div>
                        <div class="tinvwl-tooltip">{{ translate('Add to wishlist') }}</div>
                    </div>
                </div>
            </div>
            <div class="content-wrapper">
                <h3 class="product-title"><a href="{{ route('product', $product->slug) }}">{{ $product->getTranslation('name') }}</a></h3>
                <div class="product-rating rating">
                    {{ renderStarRating($product->rating) }}
                    {{--<div class="star-rating" role="img" aria-label="Rated 4.00 out of 5"><span style="width:80%">Rated
                            <strong class="rating">4.00</strong> out of 5</span></div>
                    <div class="count-rating">1 <span class="rating-text">Ratings</span></div>--}}
                </div>
                <div class="product-price-cart">
                    <span class="price">
                        @if(home_base_price($product) != home_discounted_base_price($product))
                        <del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi>{{ home_base_price($product) }}</bdi></span></del>
                        @endif
                        <ins><span class="woocommerce-Price-amount amount"><bdi>{{ home_discounted_base_price($product) }}</bdi></span></ins>
                    </span>
					@if($qty >= 1)
						<a href="javascript:void(0)" onclick="addToCart({{ $product->id }})" aria-label="Add “{{ $product->getTranslation('name') }}” to your cart" rel="nofollow" class="button product_type_simple add_to_cart_button ajax_add_to_cart"><span class="atcpreloader{{ $product->id }} text-center" style="display: none;"><i class="las la-spinner la-spin"></i></span><i class="klbth-icon-shop-1 atcicon{{ $product->id }}"></i> {{ translate('Add to cart') }}</a>
					@endif
				</div>
                <div class="product-meta"></div>
            </div>
        </div>
        @if (addon_is_activated('club_point'))
            <div class="product-footer">
                <div class="product-footer-details">
                    <ul>
                        <li>{{ translate('Club Point') }}: {{ $product->earn_point }}</li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
    <div class="product-content-fade"></div>
</div>
