@extends('frontend.layouts.app')

@section('content')
<section class="pt-4 mb-4">
    <div class="container">
        <div class="d-flex justify-content-between">
            <h1 class="fw-600 h4">{{ translate('Track Order') }}</h1>
            <ul class="breadcrumb bg-transparent p-0">
                <li class="breadcrumb-item opacity-50">
                    <a class="text-reset" href="{{ route('home') }}">{{ translate('Home') }}</a>
                </li>
                <li class="text-dark fw-600 breadcrumb-item">
                    <a class="text-reset" href="{{ route('orders.track') }}">"{{ translate('Track Order') }}"</a>
                </li>
            </ul>
        </div>
    </div>
</section>
<section class="mb-5">
    <div class="container text-left">
        <div class="row">
            <div class="col-xxl-6 col-xl-6 col-lg-8 mx-auto">
                <div class="fw-600 p-3 border-bottom text-center">
                    {{ translate('Check Your Order Status')}}
                </div>
                <form class="form-group" action="{{ route('orders.track') }}" method="GET" enctype="multipart/form-data" id="delhivery">
                    <div class="bg-white rounded shadow-sm">
                        <div class="form-box-content p-3">
                            <div class="form-group">
                                <input id="tracking_code" type="text" class="form-control mb-3" value="@isset($order) {{$order->code}} @endisset" placeholder="{{ translate('Order ID')}}" name="order_code" required>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">{{ translate('Track Order') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @isset($order)
            <div class="bg-white rounded shadow-sm mt-5">
                <div class="fs-15 fw-600 p-3 border-bottom">
                    <h5>{{ translate('Order Summary')}}</h5>
                </div>
                <div class="p-3">
                    <div class="row">
                        <div class="col-lg-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="w-50 fw-600">{{ translate('Order Code')}}:</td>
                                    <td>{{ $order->code }}</td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">{{ translate('Customer')}}:</td>
                                    <td>{{ json_decode($order->shipping_address)->name }}</td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">{{ translate('Email')}}:</td>
                                    @if ($order->user_id != null)
                                        <td>{{ $order->user->email }}</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">{{ translate('Shipping address')}}:</td>
                                    <td>{{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ json_decode($order->shipping_address)->country }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-lg-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="w-50 fw-600">{{ translate('Order date')}}:</td>
                                    <td>{{ date('d-m-Y H:i A', $order->date) }}</td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">{{ translate('Total order amount')}}:</td>
                                    <td>{{ single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('tax')) }}</td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">{{ translate('Shipping method')}}:</td>
                                    <td>{{ translate('Flat shipping rate')}}</td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">{{ translate('Payment method')}}:</td>
                                    <td>{{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">{{ translate('Delivery Status')}}:</td>
                                    <td>{{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</td>
                                </tr>
                                @if ($order->delivery_partner)
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Delivery Partner')}}:</td>
                                        <td>
										@if ($order->delivery_partner == 'other')								
											{{ $order->delivery_partner_other }}
										@else								
											{{ $order->delivery_partner }}
										@endif
										</td>
                                    </tr>
                                @endif
                                @if ($order->tracking_code)
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Tracking code')}}:</td>
                                        <td>{{ $order->tracking_code }}</td>
                                    </tr>
                                @endif
								
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            @foreach ($order->orderDetails as $key => $orderDetail)
                @php
                    $status = $order->delivery_status;
                @endphp
                <div class="bg-white rounded shadow-sm mt-4">

                    @if($orderDetail->product != null)
                    <div class="p-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ translate('Product Name')}}</th>
                                    <th>{{ translate('Quantity')}}</th>
                                    <th>{{ translate('Shipped By')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                <td>{{ $orderDetail->product->getTranslation('name') }} @if($orderDetail->variation != null)({{ $orderDetail->variation }}) @endif</td>
                                    <td>{{ $orderDetail->quantity }}</td>
                                    <td>{{ $orderDetail->product->user->name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            @endforeach

        @endisset
    </div>
</section>
@endsection

@section('script')
<script src="https://widget.pickrr.com/script.js"></script>
<script type="text/javascript">
    $('#delivery_partner').on('change', function() {
        var delivery_partner = $('#delivery_partner').val();
        if(delivery_partner === 'indiapost') {
            $('#indiapost').removeClass('d-none');
            $('#delhivery').addClass('d-none');
            $('#pickrr').addClass('d-none');
        } else if(delivery_partner === 'pickrr') {
            $('#pickrr').removeClass('d-none');
            $('#delhivery').addClass('d-none');
            $('#indiapost').addClass('d-none');
        } else if(delivery_partner === 'delhivery') {
            $('#delhivery').removeClass('d-none');
            $('#pickrr').addClass('d-none');
            $('#indiapost').addClass('d-none');
        } else {
            $('#delhivery').removeClass('d-none');
            $('#indiapost').addClass('d-none');
        }
    });
</script>
@endsection
