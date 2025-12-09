@extends('frontend.layouts.app')

@section('meta_title'){{ $page ? $page->meta_title : 'Support Policy' }}@stop

@section('meta_description'){{ $page ? $page->meta_description : 'Support Policy Information' }}@stop

@section('meta_keywords'){{ $page ? $page->tags : 'support, policy' }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $page ? $page->meta_title : 'Support Policy' }}">
    <meta itemprop="description" content="{{ $page ? $page->meta_description : 'Support Policy Information' }}">
    @if($page && $page->meta_img)
        <meta itemprop="image" content="{{ uploaded_asset($page->meta_img) }}">
    @endif

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="website">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $page ? $page->meta_title : 'Support Policy' }}">
    <meta name="twitter:description" content="{{ $page ? $page->meta_description : 'Support Policy Information' }}">
    <meta name="twitter:creator" content="@author_handle">
    @if($page && $page->meta_img)
        <meta name="twitter:image" content="{{ uploaded_asset($page->meta_img) }}">
    @endif

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $page ? $page->meta_title : 'Support Policy' }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ route('supportpolicy') }}" />
    @if($page && $page->meta_img)
        <meta property="og:image" content="{{ uploaded_asset($page->meta_img) }}" />
    @endif
    <meta property="og:description" content="{{ $page ? $page->meta_description : 'Support Policy Information' }}" />
    <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
@endsection

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ $page ? $page->getTranslation('title') : 'Support Policy' }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('supportpolicy') }}">"{{ translate('Support Policy') }}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
    <div class="container">
        <div class="p-4 bg-white rounded shadow-sm overflow-hidden mw-100 text-left">
            @if($page)
                @php
                    echo $page->getTranslation('content');
                @endphp
            @else
                <h3>Support Policy</h3>
                <p>This page content will be available soon. Please contact our support team for any assistance.</p>
            @endif
        </div>
    </div>
</section>
@endsection
