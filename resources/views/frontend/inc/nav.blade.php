@if(get_setting('topbar_banner') != null)
<div class="position-relative top-banner removable-session z-1035 d-none" data-key="top-banner" data-value="removed">
   <a href="{{ get_setting('topbar_banner_link') }}" class="d-block text-reset">
   <img src="{{ uploaded_asset(get_setting('topbar_banner')) }}" class="w-100 mw-100 h-50px h-lg-auto img-fit">
   </a>
   <button class="btn text-white absolute-top-right set-session" data-key="top-banner" data-value="removed" data-toggle="remove-parent" data-parent=".top-banner">
   <i class="la la-close la-2x"></i>
   </button>
</div>
@endif
<aside class="site-offcanvas" style="opacity: 1; visibility: inherit; transform: translate(-320px);">
   <div class="site-scroll ps ps--active-y">
      <div class="site-offcanvas-row site-offcanvas-header">
         <div class="column left">
            <div class="site-brand">
            @php
                     $header_logo = get_setting('header_logo');
                     @endphp
                     @if($header_logo != null)
                     <a class="logo" href="{{ route('home') }}" title="{{ env('APP_NAME') }}">
                     <img src="{{ uploaded_asset($header_logo) }}"
                        alt="{{ env('APP_NAME') }}" width="193">
                     </a>
                     @else
                     <a class="logo" href="{{ route('home') }}" title="{{ env('APP_NAME') }}">
                     <img src="{{ static_asset('assets/img/logo.png') }}"
                        alt="{{ env('APP_NAME') }}" width="193">
                     </a>
                     @endif
               
            </div>
            <!-- site-brand -->
         </div>
         <!-- column -->
         <div class="column right">
            <div class="site-offcanvas-close">
               <i class="klbth-icon-cancel"></i>
            </div>
            <!-- site-offcanvas-close -->
         </div>
         <!-- column -->
      </div>
      <!-- site-offcanvas-header -->
      <div class="site-offcanvas-row site-offcanvas-body">
         <div class="offcanvas-menu-container klb-menu" style="height: 1018px; transition-duration: 350ms;">
            <div class="offcanvas-menu-wrapper klb-menu-wrapper" style="transition-duration: 350ms;">
               <nav class="site-menu vertical categories">
                  <a href="#" class="all-categories">
                     <div class="departments-icon"><i class="klbth-icon-menu"></i></div>
                     <div class="departments-text">All Categories</div>
                     <div class="departments-arrow"><i class="klbth-icon-nav-arrow-down"></i></div>
                  </a>
                  <ul id="menu-sidebar-menu" class="menu departments-menu collapse" style="">
                     @php
                     $offcanvasRootCategories = \App\Models\Category::where('level', 0)->get()->map(function ($category) {
                     $category->product_count = category_product_count($category->id);
                     return $category;
                     })->filter(function ($category) {
                     return $category->product_count > 0;
                     })->sortByDesc('product_count');
                     @endphp
                     @foreach ($offcanvasRootCategories as $key => $category)
                     @php
                     $categoryProductCount = $category->product_count;
                     @endphp
                     <li id="menu-item-2121" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat  menu-item-2121">
                     <a href="{{ route('products.category', $category->slug) }}">{{ $category->getTranslation('name') }} <span class="product-count">({{ $categoryProductCount }})</span></a>
                     @if($category->childrenCategories && count($category->childrenCategories) > 0)
                     <ul class="sub-menu">
                     <li class="menu-header"><a class="back" href="#">{{ $category->getTranslation('name') }}</a></li>
                     @php
                     $offcanvasChildCategories = collect($category->childrenCategories)->map(function ($childCategory) {
                     $childCategory->product_count = category_product_count($childCategory->id);
                     return $childCategory;
                     })->filter(function ($childCategory) {
                     return $childCategory->product_count > 0;
                     })->sortByDesc('product_count');
                     @endphp
                     @foreach ($offcanvasChildCategories as $childCategory)
                     @php
                     $childCategoryProductCount = $childCategory->product_count;
                     @endphp
                     <li id="menu-item-2129" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-2129">
                     <a href="{{ route('products.category', $childCategory->slug) }}">
                     {{ $childCategory->getTranslation('name') }} <span class="product-count">({{ $childCategoryProductCount }})</span>
                     </a>
                     </li>
                     @endforeach
                     </ul>
                     @endif
                     
                     <a class="next" href="#"></a>
                     </li>
                     @endforeach
                  </ul>
                    </ul>
                  
               </nav>
              
               <nav class="site-menu vertical primary">
                    <ul id="menu-menu-1" class="menu">
                        @if(get_setting('header_menu_labels') && json_decode(get_setting('header_menu_labels'), true))
                            @foreach (json_decode( get_setting('header_menu_labels'), true) as $key => $value)
                                <li class="menu-item menu-item-type-custom menu-item-object-custom current-menu-item current_page_item menu-item-home current-menu-ancestor current-menu-parent menu-item-has-children">
                                    <a href="{{ localize_internal_url(json_decode( get_setting('header_menu_links'), true)[$key]) }}">{{ translate($value) }}</a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
               </nav>
               <nav class="site-menu vertical thirdy">
                  <ul id="menu-top-right" class="menu">
                        <li id="menu-item-2233" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2233"><a href="{{ route('shops.create') }}">{{ translate('Be a Seller') }}</a></li>
                        <li id="menu-item-2233" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2233"><a href="{{ route('orders.track') }}">Track your orders</a></li>
                  </ul>
               </nav>
               <div class="site-copyright">
                  <p>PastCart © 2022. All Rights Reserved. Designed by PastCart.Com</p>
               </div>
               <!-- site-copyright -->
            </div>
            <!-- offcanvas-menu-wrapper -->
         </div>
         <!-- offcanvas-menu-container -->
      </div>
      <!-- site-offcanvas-body -->
      <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
         <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
      </div>
      <div class="ps__rail-y" style="top: 0px; height: 592px; right: 0px;">
         <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 312px;"></div>
      </div>
   </div>
   <!-- site-scroll -->
</aside>
<header class="page-header site-header">
   <div class="header-container header-style-1">
      <div class="header-top">
         <div class="container">
            <div class="row">
               <div class="col-md-6">
                  <div class="messenger-header"><span class="warning">Covid-19 alert</span>Stay at home if you
                     feel unwell.
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="language-currency mt-2">
                     <ul class="list-inline d-flex justify-content-between justify-content-lg-start mb-0">
                        @if(get_setting('show_currency_switcher') == 'on')
                        <li class="switcher currency switcher-currency" id="currency-change">
                           @php
                           if(Session::has('currency_code')){
                           $currency_code = Session::get('currency_code');
                           }
                           else{
                           $currency_code = \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code;
                           }
                           @endphp
                           <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2 opacity-60" data-toggle="dropdown" data-display="static">
                           {{ \App\Models\Currency::where('code', $currency_code)->first()->name }}
                           </a>
                           <ul class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left list-item">
                              @foreach (\App\Models\Currency::where('status', 1)->get() as $key => $currency)
                              <li class="currency-{{ $currency->name }} switcher-option">
                                 <a class="dropdown-item @if($currency_code == $currency->code) active @endif" href="javascript:void(0)" data-currency="{{ $currency->code }}">{{ $currency->name }} ({{ $currency->symbol }})</a>
                              </li>
                              @endforeach
                           </ul>
                        </li>
                        @endif
                        @if(get_setting('show_language_switcher') == 'on')
                        <li class="switcher language switcher-language" id="lang-change">
                           @php
                           if(Session::has('locale')){
                           $locale = Session::get('locale', Config::get('app.locale'));
                           }
                           else{
                           $locale = 'en';
                           }
                           @endphp
                           <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2" data-toggle="dropdown" data-display="static">
                           <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ static_asset('assets/img/flags/'.$locale.'.png') }}" class="mr-2 lazyload" alt="{{ \App\Models\Language::where('code', $locale)->first()->name }}" height="11">
                           <span class="opacity-60">{{ \App\Models\Language::where('code', $locale)->first()->name }}</span>
                           </a>
                           <ul class="dropdown-menu dropdown-menu-left list-item">
                              @foreach (\App\Models\Language::where('status', 1)->get() as $key => $language)
                              <li class="switcher-option">
                                 <a href="javascript:void(0)" data-flag="{{ $language->code }}" class="dropdown-item @if($locale == $language) active @endif">
                                 <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" class="mr-1 lazyload" alt="{{ $language->name }}" height="11">
                                 <span class="language">{{ $language->name }}</span>
                                 </a>
                              </li>
                              @endforeach
                           </ul>
                        </li>
                        @endif
						<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-2176">
							<div id="google_translate_element"></div>
						</li>
                     </ul>
                  </div>
                  <ul class="header-top-right">
                     <li><a href="{{ route('shops.create') }}">{{ translate('Be a Seller') }}</a></li>
					 <li><a href="{{ route('user.login') }}">Seller Login</a></li>
                     <li><a href="{{ route('orders.track') }}">Track your orders</a></li>
                  </ul>
               </div>
            </div>
         </div>
      </div>
      <div class="header-middle">
         <div class="container">
            <div class="middle-content">
               <div class="logo-container">
                  <h1 class="logo-content">
                     <strong>{{ env('APP_NAME') }}</strong>
                     @php
                     $header_logo = get_setting('header_logo');
                     @endphp
                     @if($header_logo != null)
                     <a class="logo" href="{{ route('home') }}" title="{{ env('APP_NAME') }}">
                     <img src="{{ uploaded_asset($header_logo) }}"
                        alt="{{ env('APP_NAME') }}" width="193">
                     </a>
                     @else
                     <a class="logo" href="{{ route('home') }}" title="{{ env('APP_NAME') }}">
                     <img src="{{ static_asset('assets/img/logo.png') }}"
                        alt="{{ env('APP_NAME') }}" width="193">
                     </a>
                     @endif
                  </h1>
               </div>
               <div class="right-container">
                  <div class="hotline-header"><span>{{ translate('Help line')}}:</span>
                     <strong class="text-theme-color">{{ get_setting('helpline_number') }}</strong>
                  </div>
                  <div class="search-container">
                     <div class="search-wrapper">
                        <div id="sm_searchbox6831254731662883422" class="block block-search search-pro">
                           <div class="block block-content">
                              <form class="form minisearch stop-propagation" id="searchbox_mini_form" action="{{ route('search') }}" method="get">
                                 <div class="field search">
                                    <div class="control">
                                       <select class="cat searchbox-cat" name="category_id">
                                          <option value="">All Categories</option>
                                          @foreach (\App\Models\Category::where('level', 0)->orderBy('order_level', 'desc')->get() as $key => $category)
                                          <option value="{{ $category->id }}">- {{ $category->getTranslation('name') }}</option>
                                          @foreach ($category->childrenCategories as $childCategory)
                                          @include('categories.child_category', ['child_category' => $childCategory])
                                          @endforeach
                                          @endforeach
                                       </select>
                                       <input type="text" class="input-text input-searchbox" maxlength="128" role="combobox" aria-haspopup="false" aria-expanded="true" aria-autocomplete="both" autocomplete="off" id="search" name="keyword" @isset($query) value="{{ $query }}" @endisset placeholder="{{translate('Enter keywords to search...')}}" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="actions">
                                    <button type="submit" title="Search" class="btn-searchbox"><span>Search</span></button>
                                 </div>
                              </form>
                              <div class="typed-search-box stop-propagation document-click-d-none d-none bg-white rounded shadow-lg position-absolute left-0 top-100 w-100" style="min-height: 200px">
                                 <div class="search-preloader absolute-top-center">
                                    <div class="dot-loader">
                                       <div></div>
                                       <div></div>
                                       <div></div>
                                    </div>
                                 </div>
                                 <div class="search-nothing d-none p-3 text-center fs-16">
                                 </div>
                                 <div id="search-content" class="text-left">
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  @include('frontend.partials.wishlist')
                  @include('frontend.partials.cart')
                  <div class="customer-action">
                     <div class="head-title">My Account</div>
                     <div class="customer-links" data-move="customer-mobile">
                        <ul class="header links">
                           @auth
                           @if(isAdmin())
                           <li class="link authorization-link">
                              <a href="{{ route('admin.dashboard') }}">{{ translate('My Panel')}}</a>
                           </li>
                           @else
                           <li class="link authorization-link">
                              <a href="{{ route('dashboard') }}">{{ translate('My Panel')}}</a>
                           </li>
                           @endif
                           <li class="link authorization-link">
                              <a href="{{ route('logout') }}">{{ translate('Logout')}}</a>
                           </li>
                           @else
                           <li class="link authorization-link">
                              <a href="{{ route('user.login') }}">{{ translate('Login')}}</a>
                           </li>
                           <li class="link authorization-link">
                              <a href="{{ route('user.registration') }}">{{ translate('Registration')}}</a>
                           </li>
                           @endauth
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="header-bottom ontop-element">
         <div class="container">
            <div class="desktop-menu">
               <div class="vertical-block">
                  <div class="vertical-menu">
                     <div class="vertical-menu-block">
                        <div class="block-title-menu">All Categories</div>
                        <div class="vertical-menu-content">
                           <nav class="sm_megamenu_wrapper_vertical_menu sambar" id="sm_megamenu_menu631d965e7329e">
                              <div class="sambar-inner">
                                 <div class="mega-content">
                                    <ul class="vertical-type sm-megamenu-hover sm_megamenu_menu sm_megamenu_menu_black">
                                       <li class="other-toggle sm_megamenu_lv1 sm_megamenu_drop parent">
                                          <a class="sm_megamenu_head sm_megamenu_drop" href="javascript:void(0)" id="sm_megamenu_0">
                                          <span class="sm_megamenu_icon sm_megamenu_nodesc">
                                          <span class="sm_megamenu_title">All Categories</span>
                                          </span>
                                          </a>
                                       </li>
                                       @php
                                           $headerRootCategories = \App\Models\Category::where('level', 0)->get()->map(function ($category) {
                                               $category->product_count = category_product_count($category->id);
                                               return $category;
                                           })->filter(function ($category) {
                                               return $category->product_count > 0;
                                           })->sortByDesc('product_count');
                                       @endphp
                                       @foreach ($headerRootCategories as $key => $category)
                                       @php
                                           $firstLevelChildren = collect(\App\Utility\CategoryUtility::get_immediate_children($category->id));
                                           $parentProductCount = $category->product_count;
                                           $firstLevelChildren = $firstLevelChildren->map(function ($childCategory) {
                                               $childCategory->product_count = category_product_count($childCategory->id);
                                               return $childCategory;
                                           })->filter(function ($childCategory) {
                                               return $childCategory->product_count > 0;
                                           })->sortByDesc('product_count');
                                       @endphp
                                       <li class="other-toggle sm_megamenu_lv1 sm_megamenu_drop parent @if($firstLevelChildren->count() > 0) parent-item @endif">
                                          <a class="sm_megamenu_head sm_megamenu_drop @if($firstLevelChildren->count() > 0)sm_megamenu_haschild @endif" href="{{ route('products.category', $category->slug) }}" id="sm_megamenu_{{ $category->id }}">
                                          <span class="sm_megamenu_icon sm_megamenu_nodesc">
                                          <span class="sm_megamenu_title">{{ $category->getTranslation('name') }} <span class="product-count">({{ $parentProductCount }})</span></span>
                                          </span>
                                          </a>
                                          @if($firstLevelChildren->count() > 0)
                                          <div class="sm-megamenu-child sm_megamenu_dropdown_4columns">
                                             <div class="sm_megamenu_col_4 sm_megamenu_firstcolumn">
                                                <div class="sm_megamenu_col_3">
                                                   <div class="sm_megamenu_head_item">
                                                      <div class="sm_megamenu_title">
                                                         @foreach ($firstLevelChildren as $childCategory)
                                                         @php
                                                             $secondLevelChildren = collect(\App\Utility\CategoryUtility::get_immediate_children($childCategory->id))->map(function ($subChildCategory) {
                                                                 $subChildCategory->product_count = category_product_count($subChildCategory->id);
                                                                 return $subChildCategory;
                                                             })->filter(function ($subChildCategory) {
                                                                 return $subChildCategory->product_count > 0;
                                                             })->sortByDesc('product_count');
                                                             $childProductCount = $childCategory->product_count;
                                                         @endphp
                                                         <div class="sm_megamenu_col_6 sm_megamenu_firstcolumn">
                                                            <div class="sm_megamenu_head_item">
                                                               <div class="sm_megamenu_title">
                                                                  <a class="sm_megamenu_nodrop" href="{{ route('products.category', $childCategory->slug) }}">
                                                                  <span class="sm_megamenu_title_lv-2"><strong>{{ $childCategory->getTranslation('name') }} <span class="product-count">({{ $childProductCount }})</span></strong></span>
                                                                  </a>
                                                                  @if($secondLevelChildren->count() > 0)
                                                                  @foreach ($secondLevelChildren as $subChildCategory)
                                                                  @php
                                                                      $subChildProductCount = $subChildCategory->product_count;
                                                                  @endphp
                                                                  <div class="sm_megamenu_title">
                                                                     <a class="sm_megamenu_nodrop" href="{{ route('products.category', $subChildCategory->slug) }}">
                                                                     <span class="sm_megamenu_title_lv-3">{{ $subChildCategory->getTranslation('name') }} <span class="product-count">({{ $subChildProductCount }})</span></span>
                                                                     </a>
                                                                  </div>
                                                                  @endforeach
                                                                  @endif
                                                               </div>
                                                            </div>
                                                         </div>
                                                         @endforeach
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>
                                          </div>
                                          @endif
                                       </li>
                                       @endforeach
                                       <!-- <li class="other-toggle sm_megamenu_lv1 sm_megamenu_drop parent">
                                          <a class="sm_megamenu_head sm_megamenu_drop " href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop.html" id="sm_megamenu_3">
                                              <span class="sm_megamenu_icon sm_megamenu_nodesc">
                                                  <span class="sm_megamenu_title">All Medicines</span>
                                              </span>
                                          </a>
                                          </li>
                                          <li class="other-toggle sm_megamenu_lv1 sm_megamenu_drop parent parent-item">
                                          <a class="sm_megamenu_head sm_megamenu_drop sm_megamenu_haschild" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/features.html" id="sm_megamenu_4">
                                              <span class="sm_megamenu_icon sm_megamenu_nodesc">
                                                  <span class="sm_megamenu_title">Coronavirus Prevention</span>
                                              </span>
                                          </a>
                                          <div class="sm-megamenu-child sm_megamenu_dropdown_4columns">
                                              <div data-link="https://magento2.magentech.com/themes/sm_medisine/pub/default/" class="sm_megamenu_col_4 sm_megamenu_firstcolumn">
                                                  <div data-link="" class="sm_megamenu_col_3">
                                                      <div class="sm_megamenu_head_item">
                                                          <div class="sm_megamenu_title">
                                                              <div data-link="https://magento2.magentech.com/themes/sm_medisine/pub/default/" class="sm_megamenu_col_6 sm_megamenu_firstcolumn">
                                                                  <div class="sm_megamenu_head_item">
                                                                      <div class="sm_megamenu_title">
                                                                          <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/pharmacy.html">
                                                                              <span class="sm_megamenu_title_lv-3">Oral Care</span>
                                                                          </a>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/pharmacy/mint-tea.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Mint Tea</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/pharmacy/lemon-tea.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Lemon Tea</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/pharmacy/ginger-tea.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Ginger Tea</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/pharmacy/herbal-tea.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Herbal tea</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/pharmacy/accessories.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Accessories</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/pharmacy/trousers.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Trousers</span>
                                                                              </a>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                              <div data-link="https://magento2.magentech.com/themes/sm_medisine/pub/default/" class="sm_megamenu_col_6 sm_megamenu_firstcolumn">
                                                                  <div class="sm_megamenu_head_item">
                                                                      <div class="sm_megamenu_title ">
                                                                          <a class="sm_megamenu_nodrop " href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/health-care.html">
                                                                              <span class="sm_megamenu_title_lv-3">Harbal Tea</span>
                                                                          </a>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/health-care/personal-care.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Personal Care</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/health-care/baby-care.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Baby Care</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/health-care/hair-care.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Hair Care</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/health-care/oral-care.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Oral Care</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/health-care/accessories.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Accessories</span>
                                                                              </a>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>
                                                  <div data-link="" class="sm_megamenu_col_3 sm_megamenu_firstcolumn">
                                                      <div class="sm_megamenu_head_item">
                                                          <div class="sm_megamenu_title ">
                                                              <div data-link="https://magento2.magentech.com/themes/sm_medisine/pub/default/" class="sm_megamenu_col_6 sm_megamenu_firstcolumn">
                                                                  <div class="sm_megamenu_head_item">
                                                                      <div class="sm_megamenu_title ">
                                                                          <a class="sm_megamenu_nodrop " href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/baby-toys.html">
                                                                              <span class="sm_megamenu_title_lv-3">Sports Nutrition</span>
                                                                          </a>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/baby-toys/baby-care.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Baby Care</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/baby-toys/hair-care.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Hair Care</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/baby-toys/oral-care.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Oral Care</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/baby-toys/balls.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Balls</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/baby-toys/harbal-tea.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Harbal Tea</span>
                                                                              </a>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                              <div data-link="https://magento2.magentech.com/themes/sm_medisine/pub/default/" class="sm_megamenu_col_6 sm_megamenu_firstcolumn">
                                                                  <div class="sm_megamenu_head_item">
                                                                      <div class="sm_megamenu_title ">
                                                                          <a class="sm_megamenu_nodrop " href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/children.html">
                                                                              <span class="sm_megamenu_title_lv-3">Iron Proteins</span>
                                                                          </a>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/children/skin-care.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Skin Care</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/children/health-food.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Health Food</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/children/protein-supplements.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Protein Supplements</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/children/dresses.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Harbal Tea</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/children/trousers.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Trousers</span>
                                                                              </a>
                                                                          </div>
                                                                          <div class="sm_megamenu_title">
                                                                              <a class="sm_megamenu_nodrop" href="https://magento2.magentech.com/themes/sm_medisine/pub/default/shop/children/iron-proteins.html">
                                                                                  <span class="sm_megamenu_title_lv-3">Iron Proteins</span>
                                                                              </a>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>
                                          <span class="btn-submobile"></span>
                                          </li> -->
                                    </ul>
                                 </div>
                                 <div class="more-w">
                                    <span class="more-view">More Categories</span>
                                 </div>
                              </div>
                           </nav>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="horizontal-block">
                  <div class="horizontal-menu">
                     <div class="horizontal-megamenu-block">
                        <nav class="sm_megamenu_wrapper_horizontal_menu sambar" id="sm_megamenu_menu631d965feeaca">
                           <div class="sambar-inner">
                              <div class="mega-content">
                                 <ul class="horizontal-type  sm_megamenu_menu sm_megamenu_menu_black">
                                    @php
                                       $headerMenuLabels = json_decode(get_setting('header_menu_labels'), true) ?: [];
                                       $headerMenuLinks  = json_decode(get_setting('header_menu_links'), true) ?: [];
                                    @endphp
                                    @foreach ($headerMenuLabels as $key => $value)
                                        @php
                                            $rawLink = $headerMenuLinks[$key] ?? '#';
                                            $safeLink = localize_internal_url($rawLink);
                                        @endphp
                                        <li class="{{ translate($value) }}-item other-toggle sm_megamenu_lv1 sm_megamenu_drop">
                                           <a class="sm_megamenu_head sm_megamenu_drop" href="{{ $safeLink }}">
                                           <span class="sm_megamenu_icon sm_megamenu_nodesc">
                                           <span class="sm_megamenu_title">{{ translate($value) }}</span>
                                           </span>
                                           </a>
                                        </li>
                                    @endforeach
                                 </ul>
                              </div>
                           </div>
                        </nav>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="header-mobile-switcher hide-desktop d-block d-sm-block d-md-none">
      <div class="header-wrapper">
         <div class="column left">
            <div class="site-switcher site-currency">
               <nav class="site-menu horizontal">
                  <ul id="menu-top-right-2" class="menu">
                     <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2233"><a href="{{ route('orders.track')}}">Order Tracking</a></li>
                     @if(get_setting('show_currency_switcher') == 'on')
                     <li class=" switcher currency switcher-currency" id="currency-change">
                        @php
                        if(Session::has('currency_code')){
                        $currency_code = Session::get('currency_code');
                        }
                        else{
                        $currency_code = \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code;
                        }
                        @endphp
                        <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2 opacity-60" data-toggle="dropdown" data-display="static">
                        {{ \App\Models\Currency::where('code', $currency_code)->first()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left list-item">
                           @foreach (\App\Models\Currency::where('status', 1)->get() as $key => $currency)
                           <li class="currency-{{ $currency->name }} switcher-option">
                              <a class="dropdown-item @if($currency_code == $currency->code) active @endif" href="javascript:void(0)" data-currency="{{ $currency->code }}">{{ $currency->name }} ({{ $currency->symbol }})</a>
                           </li>
                           @endforeach
                        </ul>
                     </li>
                     @endif
                     @if(get_setting('show_language_switcher') == 'on')
                     <li class="switcher language switcher-language" id="lang-change">
                        @php
                        if(Session::has('locale')){
                        $locale = Session::get('locale', Config::get('app.locale'));
                        }
                        else{
                        $locale = 'en';
                        }
                        @endphp
                        <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2" data-toggle="dropdown" data-display="static">
                        <img src="{{ static_asset('assets/img/flag-placeholder.png') }}" data-src="{{ static_asset('assets/img/flags/'.$locale.'.png') }}" class="mr-2 lazyload" alt="{{ \App\Models\Language::where('code', $locale)->first()->name }}" height="11">
                        <span class="opacity-60">{{ \App\Models\Language::where('code', $locale)->first()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-left list-item">
                           @foreach (\App\Models\Language::where('status', 1)->get() as $key => $language)
                           <li class="switcher-option">
                              <a href="javascript:void(0)" data-flag="{{ $language->code }}" class="dropdown-item @if($locale == $language) active @endif">
                              <img src="{{ static_asset('assets/img/flag-placeholder.png') }}" data-src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" class="mr-1 lazyload" alt="{{ $language->name }}" height="11">
                              <span class="language">{{ $language->name }}</span>
                              </a>
                           </li>
                           @endforeach
                        </ul>
                     </li>
                     @endif
					 <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-2176">
							<div id="m_google_translate_element"></div>
						</li>
                     <!-- <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-2176">
						<a href="#"><img src="{{ static_asset('assets/img/flag-placeholder.png') }}" data-src="{{ static_asset('assets/img/flags/en.png') }}" class="mr-1 lazyload" alt="English" height="11"> English</a>
                        <ul class="sub-menu">
                            <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2177">
								<a href="#"><img src="{{ static_asset('assets/img/flag-placeholder.png') }}" data-src="{{ static_asset('assets/img/flags/en.png') }}" class="mr-1 lazyload" alt="English" height="11"> English</a>
							</li>
                            <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2178">
								<a href="#"><img src="{{ static_asset('assets/img/flag-placeholder.png') }}" data-src="{{ static_asset('assets/img/flags/hi.png') }}" class="mr-1 lazyload" alt="Hindi" height="11"> Hindi</a>
							</li>
                        </ul>
						</li>
                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-2194"><a href="#">USD</a>
                        <ul class="sub-menu">
                            <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2195"><a href="#">USD</a></li>
                            <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2196"><a href="#">INR</a></li>
                            <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2197"><a href="#">GBP</a></li>
                        </ul>
                        </li> -->
                  </ul>
               </nav>
            </div>
            <!-- site-currency -->
         </div>
         <!-- column -->
      </div>
      <!-- header-wrapper -->
   </div>
   <div class="header-mobile hide-desktop">
      <div class="header-wrapper">
         <div class="column left">
            <div class="header-addons menu-toggle">
               <a href="#">
                  <div class="header-addons-icon">
                     <i class="klbth-icon-menu"></i>
                  </div>
                  <!-- header-addons-icon -->
               </a>
            </div>
            <!-- menu-toggle -->
         </div>&nbsp;&nbsp;
         <!-- column -->
         <div class="column center">
            <div class="site-brand">
               @php
               $header_logo = get_setting('header_logo');
               @endphp
               @if($header_logo != null)
               <a class="logo" href="{{ route('home') }}" title="{{ env('APP_NAME') }}">
               <img src="{{ uploaded_asset($header_logo) }}"
                  alt="{{ env('APP_NAME') }}" width="193">
               </a>
               @else
               <a class="logo" href="{{ route('home') }}" title="{{ env('APP_NAME') }}">
               <img src="{{ static_asset('assets/img/logo.png') }}"
                  alt="{{ env('APP_NAME') }}" width="193">
               </a>
               @endif
            </div>
            <!-- site-brand -->
         </div>
         <!-- column -->
         <div class="column right">
            <div class="header-addons ">
               <a href="{{ route('cart') }}">
                  <div class="header-addons-icon">
                     <i class="klbth-icon-simple-cart"></i>
                     <div class="button-count cart-count">0</div>
                  </div>                  
                  <!-- header-addons-text -->
               </a>&nbsp;&nbsp;
			   @auth
			   @if(isAdmin())
			   <a href="{{ route('admin.dashboard') }}">{{ translate('My Panel')}}</a>&nbsp;&nbsp;
			   @else
				   <a href="{{ route('dashboard') }}">{{ translate('My Panel')}}</a>&nbsp;&nbsp;
			   @endif
					<a href="{{ route('logout') }}">{{ translate('Logout')}}</a>
			   @else
			   <a href="{{ route('user.login') }}">{{ translate('Login')}}</a>&nbsp;&nbsp;
			   <a href="{{ route('user.registration') }}">{{ translate('Registration')}}</a>
			   @endauth
               
               <!-- cart-dropdown -->
            </div>
            <!-- header-addons -->
         </div>
         <!-- column -->
      </div>
      <!-- header-wrapper -->
   </div>
</header>
<div class="modal fade" id="order_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
      <div class="modal-content">
         <div id="order-details-modal-body">
         </div>
      </div>
   </div>
</div>
@section('script')
<script type="text/javascript">
   function show_order_details(order_id)
   {
       $('#order-details-modal-body').html(null);
   
       if(!$('#modal-size').hasClass('modal-lg')){
           $('#modal-size').addClass('modal-lg');
       }
   
       $.post('{{ route('orders.details') }}', { _token : AIZ.data.csrf, order_id : order_id}, function(data){
           $('#order-details-modal-body').html(data);
           $('#order_details').modal();
           $('.c-preloader').hide();
           AIZ.plugins.bootstrapSelect('refresh');
       });
   }
</script>
<script type="text/javascript">
   function ($) {
       var limit;
       limit = 10;
   
       var i;
       i = 0;
       var items;
       items = $('.sm_megamenu_wrapper_vertical_menu .sm_megamenu_menu > li').length;
   
       if (items > limit) {
           $('.sm_megamenu_wrapper_vertical_menu .sm_megamenu_menu > li').each(function () {
               i++;
               if (i > limit) {
                   $(this).css('display', 'none');
               }
           });
   
           $('.sm_megamenu_wrapper_vertical_menu .sambar-inner .more-w > .more-view').click(function () {
               if ($(this).hasClass('open')) {
                   i = 0;
                   $('.sm_megamenu_wrapper_vertical_menu .sm_megamenu_menu > li').each(function () {
                       i++;
                       if (i > limit) {
                           $(this).slideUp(200);
                       }
                   });
                   $(this).removeClass('open');
                   $('.more-w').removeClass('active-i');
                   $(this).html('More Categories');
               } else {
                   i = 0;
                   $('.sm_megamenu_wrapper_vertical_menu ul.sm_megamenu_menu > li').each(function () {
                       i++;
                       if (i > limit) {
                           $(this).slideDown(200);
                       }
                   });
                   $(this).addClass('open');
                   $('.more-w').addClass('active-i');
                   $(this).html('Close Menu');
               }
           });
       } else {
           $(".more-w").css('display', 'none');
       }
   
       var menu_width = $('.sm_megamenu_wrapper_horizontal_menu').width();
       $('.sm_megamenu_wrapper_horizontal_menu .sm_megamenu_menu > li > div').each(function () {
           $this = $(this);
           var lv2w = $this.width();
           var lv2ps = $this.position();
           var lv2psl = $this.position().left;
           var sw = lv2w + lv2psl;
           if (sw > menu_width) {
               $this.css({'right': '0'});
           }
       });
       var _item_active = $('div.sm_megamenu_actived');
       if (_item_active.length) {
           _item_active.each(function () {
               var _self = $(this), _parent_active = _self.parents('.sm_megamenu_title'),
                   _level1 = _self.parents('.sm_megamenu_lv1');
               if (_parent_active.length) {
                   _parent_active.each(function () {
                       if (!$(this).hasClass('sm_megamenu_actived'))
                           $(this).addClass('sm_megamenu_actived');
                   });
               }
   
               if (_level1.length && !_level1.hasClass('sm_megamenu_actived')) {
                   _level1.addClass('sm_megamenu_actived');
               }
           });
       }
   
   };
</script>
@endsection
