{{-- <div class="aiz-mobile-bottom-nav d-xl-none fixed-bottom bg-white shadow-lg border-top rounded-top" style="box-shadow: 0px -1px 10px rgb(0 0 0 / 15%)!important; ">
    <div class="row align-items-center gutters-5">
        <div class="col">
            <a href="{{ route('home') }}" class="text-reset d-block text-center pb-2 pt-3">
                <i class="las la-home fs-20 opacity-60 {{ areActiveRoutes(['home'],'opacity-100 text-primary')}}"></i>
                <span class="d-block fs-10 fw-600 opacity-60 {{ areActiveRoutes(['home'],'opacity-100 fw-600')}}">{{ translate('Home') }}</span>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('categories.all') }}" class="text-reset d-block text-center pb-2 pt-3">
                <i class="las la-list-ul fs-20 opacity-60 {{ areActiveRoutes(['categories.all'],'opacity-100 text-primary')}}"></i>
                <span class="d-block fs-10 fw-600 opacity-60 {{ areActiveRoutes(['categories.all'],'opacity-100 fw-600')}}">{{ translate('Categories') }}</span>
            </a>
        </div>
        @php
            if(auth()->user() != null) {
                $user_id = Auth::user()->id;
                $cart = \App\Models\Cart::where('user_id', $user_id)->get();
            } else {
                $temp_user_id = Session()->get('temp_user_id');
                if($temp_user_id) {
                    $cart = \App\Models\Cart::where('temp_user_id', $temp_user_id)->get();
                }
            }
        @endphp
        <div class="col-auto">
            <a href="{{ route('cart') }}" class="text-reset d-block text-center pb-2 pt-3">
                <span class="align-items-center bg-primary border border-white border-width-4 d-flex justify-content-center position-relative rounded-circle size-50px" style="margin-top: -33px;box-shadow: 0px -5px 10px rgb(0 0 0 / 15%);border-color: #fff !important;">
                    <i class="las la-shopping-bag la-2x text-white"></i>
                </span>
                <span class="d-block mt-1 fs-10 fw-600 opacity-60 {{ areActiveRoutes(['cart'],'opacity-100 fw-600')}}">
                    {{ translate('Cart') }}
                    @php
                        $count = (isset($cart) && count($cart)) ? count($cart) : 0;
                    @endphp
                    (<span class="cart-count">{{$count}}</span>)
                </span>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('all-notifications') }}" class="text-reset d-block text-center pb-2 pt-3">
                <span class="d-inline-block position-relative px-2">
                    <i class="las la-bell fs-20 opacity-60 {{ areActiveRoutes(['all-notifications'],'opacity-100 text-primary')}}"></i>
                    @if(Auth::check() && count(Auth::user()->unreadNotifications) > 0)
                        <span class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right" style="right: 7px;top: -2px;"></span>
                    @endif
                </span>
                <span class="d-block fs-10 fw-600 opacity-60 {{ areActiveRoutes(['all-notifications'],'opacity-100 fw-600')}}">{{ translate('Notifications') }}</span>
            </a>
        </div>
        <div class="col">
        @if (Auth::check())
            @if(isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="text-reset d-block text-center pb-2 pt-3">
                    <span class="d-block mx-auto">
                        @if(Auth::user()->photo != null)
                            <img src="{{ custom_asset(Auth::user()->avatar_original)}}" class="rounded-circle size-20px">
                        @else
                            <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="rounded-circle size-20px">
                        @endif
                    </span>
                    <span class="d-block fs-10 fw-600 opacity-60">{{ translate('Account') }}</span>
                </a>
            @else
                <a href="javascript:void(0)" class="text-reset d-block text-center pb-2 pt-3 mobile-side-nav-thumb" data-toggle="class-toggle" data-backdrop="static" data-target=".aiz-mobile-side-nav">
                    <span class="d-block mx-auto">
                        @if(Auth::user()->photo != null)
                            <img src="{{ custom_asset(Auth::user()->avatar_original)}}" class="rounded-circle size-20px">
                        @else
                            <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="rounded-circle size-20px">
                        @endif
                    </span>
                    <span class="d-block fs-10 fw-600 opacity-60">{{ translate('Account') }}</span>
                </a>
            @endif
        @else
            <a href="{{ route('user.login') }}" class="text-reset d-block text-center pb-2 pt-3">
                <span class="d-block mx-auto">
                    <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="rounded-circle size-20px">
                </span>
                <span class="d-block fs-10 fw-600 opacity-60">{{ translate('Account') }}</span>
            </a>
        @endif
        </div>
    </div>
</div> --}}
<!-- ///////////////////////////////// -->
<footer class="page-footer">
    <div class="footer-style-1">
        <div class="footer-top">
            <div class="container">
                <div class="newsletter-footer align-items-center">
                    <div class="block-footer-title">
                        <h2>Sign Up {{config('app.name')}} Newsletter</h2>
                    </div>
                    <div class="block-footer-content">
                        <div class="block-subscribe-footer">
                            <form class="form subscribe" novalidate="novalidate" action="{{ route('subscribers.store') }}" method="post" id="newsletter-footer-validate-detail">
                                <div class="newsletter-content">
                                    <div class="input-box">
                                        <input name="email" type="email" id="newsletter-footer" onfocus="if(this.value == `{{ translate('Your Email Address') }}`) this.value='';" onblur="if(this.value=='') this.value=`{{ translate('Your Email Address') }}`;" value="{{ translate('Your Email Address') }}" data-validate="{required:true, 'validate-email':true}">
                                    </div>
                                    <div class="action-button">
                                        <button class="action subscribe primary" title="Subscribe" type="submit"><span>{{ translate('Subscribe') }}</span></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-middle">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                        <div class="block-footer aboutus-footer">
                            <div class="block-footer-title">
                                <a href="{{ route('home') }}">
                                @if(get_setting('footer_logo') != null)
                                    <img class="lazyload" src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset(get_setting('footer_logo')) }}" alt="{{ env('APP_NAME') }}" height="44">
                                @else
                                    <img class="lazyload" src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" height="44">
                                @endif
                                </a>
                            </div>
                            <div class="block-footer-content">
                                {!! get_setting('about_us_description',null,App::getLocale()) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                        <div class="block-footer">
                            <div class="block-footer-title">{{ translate('Contact Info') }}</div>
                            <div class="block-footer-content">
                                <ul>
                                    <li>Call us:<br><strong class="text-theme-color phone-number">{{ get_setting('contact_phone') }}</strong></li>
                                    <li>{{ get_setting('contact_address',null,App::getLocale()) }}</li>
                                    <li><a href="mailto:{{ get_setting('contact_email') }}">{{ get_setting('contact_email') }}</a></li>
                                </ul>
                            </div>
                            @if ( get_setting('show_social_links') )
                                <div class="social-footer">
                                    <ul>
                                        @if ( get_setting('facebook_link') !=  null )
                                            <li class="facebook"><a class="icon-facebook" title="Facebook" href="{{ get_setting('facebook_link') }}" target="_blank"> <span class="hidden">Facebook</span> </a></li>
                                        @endif
                                        @if ( get_setting('youtube_link') !=  null )
                                            <li class="youtube"><a class="icon-youtube1" title="Youtube" href="{{ get_setting('youtube_link') }}" target="_blank"> <span class="hidden">Youtube</span> </a></li>
                                        @endif
                                        @if ( get_setting('twitter_link') !=  null )
                                            <li class="twitter"><a class="icon-twitter" title="Twitter" href="{{ get_setting('twitter_link') }}" target="_blank"> <span class="hidden">Twitter</span> </a></li>
                                        @endif
                                        @if ( get_setting('instagram_link') !=  null )
                                            <li class="instagram"><a class="icon-instagram" title="Instagram" href="{{ get_setting('instagram_link') }}" target="_blank"> <span class="hidden">Instagram</span> </a></li>
                                        @endif
                                        @if ( get_setting('linkedin_link') !=  null )
                                            <li class="linkedin"><a class="icon-linkedin" title="Linkedin" href="{{ get_setting('linkedin_link') }}" target="_blank"> <span class="hidden">Linkedin</span> </a></li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                        <div class="block-footer block-links">
                            <div class="block-footer-title">Information</div>
                            <div class="block-footer-content">
                                <ul>
                                    <li>
                                        @if (Auth::check())
                                            <a href="{{ route('logout') }}">{{ translate('Logout') }}</a>
                                        @else
                                            <a href="{{ route('user.login') }}">{{ translate('Login') }}</a>
                                        @endif
                                    </li>
                                    <li><a href="{{ route('aboutus') }}">About Us</a></li>
                                    <li><a href="{{ route('privacypolicy') }}">{{ translate('Privacy Policy') }}</a></li>
                                    <li><a href="{{ route('terms') }}">{{ translate('Terms & conditions') }}</a></li>
                                    <li><a href="{{ route('supportpolicy') }}">{{ translate('Support Policy') }}</a></li>
                                    <li><a href="{{ route('returnpolicy') }}">{{ translate('Return Policy') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                        <div class="block-footer block-links">
                            <div class="block-footer-title">Quick Links</div>
                            <div class="block-footer-content">
                                <ul>
                                    <li><a href="{{ route('beaseller') }}">{{ translate('Be a Seller') }}</a></li>
                                    <li><a href="{{ route('wishlists.index') }}">{{ translate('My Wishlist') }}</a></li>
                                    <li><a href="{{ route('orders.track') }}">{{ translate('Track Order') }}</a></li>
                                    <li><a href="{{ route('faqs') }}">{{ translate('FAQs') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 payment-footer">
                        <ul class="list-inline mb-0">
                            @if ( get_setting('payment_method_images') !=  null )
                                @foreach (explode(',', get_setting('payment_method_images')) as $key => $value)
                                    <li class="list-inline-item">
                                        <img src="{{ uploaded_asset($value) }}" height="30" class="mw-100 h-auto" style="max-height: 30px">
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <address>{!! get_setting('frontend_copyright_text',null,App::getLocale()) !!}</address>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

@if (Auth::check() && !isAdmin())
    <div class="aiz-mobile-side-nav collapse-sidebar-wrap sidebar-xl d-xl-none z-1035">
        <div class="overlay dark c-pointer overlay-fixed" data-toggle="class-toggle" data-backdrop="static" data-target=".aiz-mobile-side-nav" data-same=".mobile-side-nav-thumb"></div>
        <div class="collapse-sidebar bg-white">
            @include('frontend.inc.user_side_nav')
        </div>
    </div>
@endif
