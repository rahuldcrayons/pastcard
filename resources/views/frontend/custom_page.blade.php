@extends('frontend.layouts.app')

@section('meta_title'){{ $page ? $page->meta_title : (isset($page_type) && $page_type == 'about_us' ? 'About Us' : (isset($page_type) && $page_type == 'contact_us' ? 'Contact Us' : 'Page')) }}@stop

@section('meta_description'){{ $page ? $page->meta_description : (isset($page_type) && $page_type == 'about_us' ? 'Learn more about our company' : (isset($page_type) && $page_type == 'contact_us' ? 'Get in touch with us' : 'Page Information')) }}@stop

@section('meta_keywords'){{ $page ? $page->tags : (isset($page_type) && $page_type == 'about_us' ? 'about, company, information' : (isset($page_type) && $page_type == 'contact_us' ? 'contact, support, help' : 'page, info')) }}@stop

@section('meta')
<!-- Schema.org markup for Google+ -->
<meta itemprop="name" content="{{ $page ? $page->meta_title : 'Page' }}">
<meta itemprop="description" content="{{ $page ? $page->meta_description : 'Page Information' }}">
@if($page && $page->meta_img)
<meta itemprop="image" content="{{ uploaded_asset($page->meta_img) }}">
@endif

<!-- Twitter Card data -->
<meta name="twitter:card" content="website">
<meta name="twitter:site" content="@publisher_handle">
<meta name="twitter:title" content="{{ $page ? $page->meta_title : 'Page' }}">
<meta name="twitter:description" content="{{ $page ? $page->meta_description : 'Page Information' }}">
<meta name="twitter:creator" content="@author_handle">
@if($page && $page->meta_img)
<meta name="twitter:image" content="{{ uploaded_asset($page->meta_img) }}">
@endif

<!-- Open Graph data -->
<meta property="og:title" content="{{ $page ? $page->meta_title : 'Page' }}" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ url()->current() }}" />
@if($page && $page->meta_img)
<meta property="og:image" content="{{ uploaded_asset($page->meta_img) }}" />
@endif
<meta property="og:description" content="{{ $page ? $page->meta_description : 'Page Information' }}" />
<meta property="og:site_name" content="{{ env('APP_NAME') }}" />
@endsection

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ $page ? $page->getTranslation('title') : (isset($page_type) && $page_type == 'about_us' ? 'About Us' : (isset($page_type) && $page_type == 'contact_us' ? 'Contact Us' : 'Page')) }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <span class="text-reset">{{ $page ? $page->getTranslation('title') : (isset($page_type) && $page_type == 'about_us' ? 'About Us' : (isset($page_type) && $page_type == 'contact_us' ? 'Contact Us' : 'Page')) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
    <div class="container">
        <div class="p-4 bg-white rounded shadow-sm overflow-hidden mw-100 text-left">
            @if($page && $page->getTranslation('content'))
                @php echo $page->getTranslation('content'); @endphp
            @else
                @if(isset($page_type) && $page_type == 'about_us')
                    <h3>About Us</h3>
                    <p>Welcome to {{ get_setting('website_name', env('APP_NAME')) }}! We are committed to providing you with the best shopping experience. Our team works hard to bring you quality products at competitive prices.</p>
                    <p>This page content is being updated with more detailed information about our company. Please check back soon or contact us directly for more information.</p>
                @elseif(isset($page_type) && $page_type == 'contact_us')
                    <h3>Contact Us</h3>
                    <p>We'd love to hear from you! Get in touch with us for any questions, support, or feedback.</p>
                    @if(get_setting('contact_email'))
                        <p><strong>Email:</strong> {{ get_setting('contact_email') }}</p>
                    @endif
                    @if(get_setting('contact_phone'))
                        <p><strong>Phone:</strong> {{ get_setting('contact_phone') }}</p>
                    @endif
                    @if(get_setting('contact_address'))
                        <p><strong>Address:</strong> {{ get_setting('contact_address') }}</p>
                    @endif
                    <p>This page content is being updated with more contact information and a contact form. Please check back soon.</p>
                @else
                    <h3>Page Content</h3>
                    <p>This page content is being updated. Please check back later for more information.</p>
                @endif
            @endif
        </div>
    </div>
</section>
@endsection