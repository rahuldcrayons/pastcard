@extends('frontend.layouts.app')

@section('content')

    <section class="pt-5 mb-4">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 mx-auto">
                    <div class="row aiz-steps arrow-divider">
                        <div class="col done">
                            <div class="text-center text-success">
                                <i class="la-3x mb-2 las la-shopping-cart"></i>
                                <h3 class="fw-600 d-none d-lg-block">{{ translate('1. My Cart') }}</h3>
                            </div>
                        </div>
                        <div class="col done">
                            <div class="text-center text-success">
                                <i class="la-3x mb-2 las la-map"></i>
                                <h3 class="fw-600 d-none d-lg-block">{{ translate('2. Shipping info') }}</h3>
                            </div>
                        </div>
                        <div class="col active">
                            <div class="text-center text-primary">
                                <i class="la-3x mb-2 las la-truck"></i>
                                <h3 class="fw-600 d-none d-lg-block">{{ translate('3. Delivery info') }}</h3>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-center">
                                <i class="la-3x mb-2 opacity-50 las la-credit-card"></i>
                                <h3 class="fw-600 d-none d-lg-block opacity-50">{{ translate('4. Payment') }}</h3>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-center">
                                <i class="la-3x mb-2 opacity-50 las la-check-circle"></i>
                                <h3 class="fw-600 d-none d-lg-block opacity-50">{{ translate('5. Confirmation') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-4 gry-bg">
        <div class="container">
            <div class="row">
                <div class="col-xxl-8 col-xl-10 mx-auto">
                    <form class="form-default" action="{{ route('guest.checkout.store_delivery_info') }}" role="form"
                        method="POST">
                        @csrf
                       
                        <div class="row border-top pt-3 mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-600">{{ translate('Choose Delivery Type') }}</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="row gutters-5">
                                    @foreach ($shipping_types as $shipping_type)
                                    @if ($shipping_type=='Normal Delivery')
                                    <div class="col-6">
                                        <label class="aiz-megabox d-block bg-white mb-0">
                                            <input type="radio" name="shipping_type"
                                                value="{{ $shipping_type }}"
                                                @if ($shipping_type=='Normal Delivery')
                                                checked
                                                @endif
                                                >
                                            <span class="d-flex p-3 aiz-megabox-elem">
                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                <span class="flex-grow-1 pl-3 fw-600">{{ $shipping_type }}</span>
                                            </span>
                                        </label>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="pt-4 d-flex justify-content-between align-items-center">
                            <a href="{{ route('home') }}">
                                <i class="la la-angle-left"></i>
                                {{ translate('Return to shop') }}
                            </a>
                            <button type="submit"
                                class="btn fw-600 btn-primary">{{ translate('Continue to Payment') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        function display_option(key) {

        }

        function show_pickup_point(el) {
            var value = $(el).val();
            var target = $(el).data('target');

            if (value == 'home_delivery') {
                if (!$(target).hasClass('d-none')) {
                    $(target).addClass('d-none');
                }
            } else {
                $(target).removeClass('d-none');
            }
        }
    </script>
@endsection
