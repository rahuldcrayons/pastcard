@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h1 class="h2 fs-16 mb-0">{{ translate('Order Details') }}</h1>
    </div>
    <div class="card-body">
        <div class="row gutters-5 mb-3">
           <!-- <div class="col text-center text-md-left">
            </div> -->
            @php
                $delivery_status = $order->delivery_status;
                $payment_status = $order->payment_status;
            @endphp

            <!--Assign Delivery Boy-->
            @if (addon_is_activated('delivery_boy'))
                <div class="col-md-3 ml-auto">
                    <label for="assign_deliver_boy">{{translate('Assign Deliver Boy')}}</label>
                    @if($delivery_status == 'pending' || $delivery_status == 'confirmed' || $delivery_status == 'picked_up')
                    <select class="form-control aiz-selectpicker" data-live-search="true" data-minimum-results-for-search="Infinity" id="assign_deliver_boy">
                        <option value="">{{translate('Select Delivery Boy')}}</option>
                        @foreach($delivery_boys as $delivery_boy)
                        <option value="{{ $delivery_boy->id }}" @if($order->assign_delivery_boy == $delivery_boy->id) selected @endif>
                            {{ $delivery_boy->name }}
                        </option>
                        @endforeach
                    </select>
                    @else
                        <input type="text" class="form-control" value="{{ optional($order->delivery_boy)->name }}" disabled>
                    @endif
                </div>
            @endif

            <div class="col-md-2 ml-auto">
                <label for="update_payment_status">{{translate('Payment Status')}}</label>
                <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_payment_status">
                    <option value="unpaid" @if ($payment_status == 'unpaid') selected @endif>{{translate('Unpaid')}}</option>
                    <option value="paid" @if ($payment_status == 'paid') selected @endif>{{translate('Paid')}}</option>
                </select>
            </div>
            <div class="col-md-2 ml-auto">
                <label for="update_delivery_status">{{translate('Delivery Status')}}</label>
                @if($delivery_status != 'delivered' && $delivery_status != 'cancelled')
                    <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_delivery_status">
                        <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{translate('Pending')}}</option>
                        <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>{{translate('Confirmed')}}</option>
                        <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>{{translate('Picked Up')}}</option>
                        <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>{{translate('On The Way')}}</option>
                        <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{translate('Delivered')}}</option>
                        <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>{{translate('Cancel')}}</option>
                    </select>
                @else
                    <input type="text" class="form-control" value="{{ $delivery_status }}" disabled>
                @endif
            </div>
			<div class="col-md-4 ml-auto">
                <label for="delivery_partner">Delivery Partner</label>
                <!--<input type="text" class="form-control" id="delivery_partners" value="{{ $order->delivery_partner }}"> -->
				<select name="delivery_partner" id="delivery_partner" class="form-control aiz-selectpicker" data-max-options="1" data-live-search="true">
					<option value="">{{ translate('Select Partner') }}</option>
					<option value="indiapost" @if ($order->delivery_partner == 'indiapost') selected @endif>{{ translate('India Post') }}</option>
					<option value="delhivery"  @if ($order->delivery_partner == 'delhivery') selected @endif>{{ translate('Delhivery') }}</option>
					<option value="pickrr"  @if ($order->delivery_partner == 'pickrr') selected @endif>{{ translate('Pickrr') }}</option>
					<option value="other"  @if ($order->delivery_partner == 'other') selected @endif>{{ translate('Other') }}</option>
                </select>
				<input type="text" class="form-control" id="delivery_partner_other" name="delivery_partner_other" value="{{ $order->delivery_partner_other }}" @if ($order->delivery_partner == 'other') style="display:block" @else style="display:none" @endif>
            </div>
			
            <div class="col-md-3 ml-auto">
                <label for="update_tracking_code">{{translate('Tracking Code (optional)')}}</label>
                <input type="text" class="form-control" id="update_tracking_code" value="{{ $order->tracking_code }}">
            </div>
        </div>
        <div class="mb-3">
            @php
                                $removedXML = '<?xml version="1.0" encoding="UTF-8"?>';
                            @endphp
                            {!! str_replace($removedXML,"", QrCode::size(100)->generate($order->code)) !!}
        </div>
        <div class="row gutters-5">
            <div class="col text-center text-md-left">
                <address>
                    <strong class="text-main">{{ json_decode($order->shipping_address)->name }}</strong><br>
                    {{ json_decode($order->shipping_address)->email }}<br>
                    {{ json_decode($order->shipping_address)->phone }}<br>
                    {{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ json_decode($order->shipping_address)->postal_code }}<br>
                    {{ json_decode($order->shipping_address)->country }}
                </address>
                @if ($order->manual_payment && is_array(json_decode($order->manual_payment_data, true)))
                <br>
                <strong class="text-main">{{ translate('Payment Information') }}</strong><br>
                {{ translate('Name') }}: {{ json_decode($order->manual_payment_data)->name }}, {{ translate('Amount') }}: {{ single_price(json_decode($order->manual_payment_data)->amount) }}, {{ translate('TRX ID') }}: {{ json_decode($order->manual_payment_data)->trx_id }}
                <br>
                <a href="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" target="_blank"><img src="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" alt="" height="100"></a>
                @endif
            </div>
            <div class="col-md-4 ml-auto">
                <table>
                    <tbody>
                        <tr>
                            <td class="text-main text-bold">{{translate('Order #')}}</td>
                            <td class="text-right text-info text-bold">	{{ $order->code }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{translate('Order Status')}}</td>
                            <td class="text-right">
                                @if($delivery_status == 'delivered')
                                <span class="badge badge-inline badge-success">{{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
                                @else
                                <span class="badge badge-inline badge-info">{{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{translate('Order Date')}}	</td>
                            <td class="text-right">{{ date('d-m-Y h:i A', $order->date) }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">
                                {{translate('Total amount')}}
                            </td>
                            <td class="text-right">
                                {{ single_price($order->grand_total) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{translate('Payment method')}}</td>
                            <td class="text-right">{{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Shipping method')}}:</td>
                            <td>{{ $order->shipping_type }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
        <hr class="new-section-sm bord-no">
        <div class="row">
            <div class="col-lg-12 table-responsive">
                <table id="tabrem" class="table table-bordered aiz-table invoice-summary">
                    <thead>
                        <tr class="bg-trans-dark">
                            <th data-breakpoints="lg" class="min-col">#</th>
                            <th width="10%">{{translate('Photo')}}</th>
                            <th class="text-uppercase">{{translate('Description')}}</th>
                            <th data-breakpoints="lg" class="text-uppercase">{{translate('Delivery Type')}}</th>
                            <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{translate('Qty')}}</th>
                            <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{translate('Price')}}</th>
                            <th data-breakpoints="lg" class="min-col text-right text-uppercase">{{translate('Total')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->orderDetails as $key => $orderDetail)
                        @if ($orderDetail->cancel == 1)
						<tr style="opacity: 0.7;background: cornsilk;">
						 
							<td>{{ $key+1 }}</td>
                            <td>
                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                    <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                    <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                @else
                                    <strong>{{ translate('N/A') }}</strong>
                                @endif								
                            </td>
                            <td>
							<p style="position: absolute;margin-top: 20px;left: 50%;color: #9f2929;font-size: 18px;pointer-events: none;-webkit-transform: rotate(-45deg);-moz-transform: rotate(-34deg);">Cancelled</p>
						  
                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                    <strong><a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }} - Cancelled</a></strong>
                                    <small>{{ $orderDetail->variation }}</small>
                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                    <strong><a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                @else
                                    <strong>{{ translate('Product Unavailable') }}</strong>
                                @endif
							</td>
                            <td>
                                @if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
                                {{ translate('Home Delivery') }}
                                @elseif ($order->shipping_type == 'pickup_point')

                                @if ($order->pickup_point != null)
                                {{ $order->pickup_point->getTranslation('name') }} ({{ translate('Pickup Point') }})
                                @else
                                {{ translate('Pickup Point') }}
                                @endif
                                @endif
                            </td>
                            <td class="text-center">{{ $orderDetail->quantity }}</td>
                            <td class="text-center">{{ single_price($orderDetail->price/$orderDetail->quantity) }}</td>
                            <td class="text-center">{{ single_price($orderDetail->price) }}</td>
                        </tr>
						@else
							
						<tr>
						  
							<td>{{ $key+1 }}</td>
                            <td>
                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                    <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                    <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                @else
                                    <strong>{{ translate('N/A') }}</strong>
                                @endif								
                            </td>
                            <td>
                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                    <strong><a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                    <small>{{ $orderDetail->variation }}</small>
                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                    <strong><a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                @else
                                    <strong>{{ translate('Product Unavailable') }}</strong>
                                @endif
								<button data-itmid='{{$orderDetail->product->id}}' class="option1 float-right btn btn-soft-danger btn-icon btn-circle btn-sm deleteitem"><i class="las la-trash"></i></button>
                            </td>
                            <td>
                                @if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
                                {{ translate('Home Delivery') }}
                                @elseif ($order->shipping_type == 'pickup_point')

                                @if ($order->pickup_point != null)
                                {{ $order->pickup_point->getTranslation('name') }} ({{ translate('Pickup Point') }})
                                @else
                                {{ translate('Pickup Point') }}
                                @endif
                                @endif
                            </td>
                            <td class="text-center">{{ $orderDetail->quantity }}</td>
                            <td class="text-center">{{ single_price($orderDetail->price/$orderDetail->quantity) }}</td>
                            <td class="text-center">{{ single_price($orderDetail->price) }}</td>
                        </tr>
						
						@endif	
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="clearfix float-right">
            <table class="table">
                <tbody>
                    <tr>
                        <td>
                            <strong class="text-muted">{{translate('Sub Total')}} :</strong>
                        </td>
                        <td> 
                            {{ single_price($order->orderDetails->sum('price')) }}
                        </td>
                    </tr>
					
                    @if ($order->orderDetails->whereIn('cancel','1')->count())
						<tr>
						<td>
							<strong class="text-muted">{{translate('Cancelled')}} :</strong>
						</td>
						<td> 
							- {{ single_price($order->orderDetails->whereIn('cancel', '1')->sum('price')) }}
						</td>
						</tr>
					@endif
					
                    <tr>
                        <td>
                            <strong class="text-muted">{{translate('Tax')}} :</strong>
                        </td>
                        <td>
                            {{ single_price($order->orderDetails->whereIn('cancel', '0')->sum('tax')) }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong class="text-muted">{{translate('Shipping')}} :</strong>
                        </td>
                        <td>
                            @php

                            $shipping_charge= DB::table('shipping_types')->where('name',$order->shipping_type)->pluck('price')->first();
                            if(Auth::user()->is_premium == 1)
            {
                $shipping_charge=0;
            }
                        @endphp
                        {{ single_price($shipping_charge) }}


                            {{-- {{ single_price($order->orderDetails->sum('shipping_cost')) }} --}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong class="text-muted">{{translate('Coupon')}} :</strong>
                        </td>
                        <td>
                            {{ single_price($order->coupon_discount) }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong class="text-muted">{{translate('TOTAL')}} :</strong>
                        </td>
                        <td class="text-muted h5">
                            {{ single_price($order->grand_total) }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="text-right no-print">
                <a href="{{ route('invoice.download', $order->id) }}" type="button" class="btn btn-icon btn-light"><i class="las la-print"></i></a>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
	
		$(document).on("click", ".deleteitem", function(e){
		var order_id = {{ $order->id }};
		var itemid = $(this).data('itmid');
		if(!confirm('Are you Sure to delete?')){
           e.preventDefault();
		}
		else{
			//$(this).closest("tr").remove();
			$.ajax({
				type: "post",
				url: '{{ route('orders.order_item_remove') }}',
				data: {
				_token:'{{ @csrf_token() }}',
				order_id        :order_id,
                removeid    	:itemid
				},
				dataType: "json",
				 
				success: function (response) {
				}
			});
			$(document).ajaxStop(function(){
			location.reload()
			});				
				 
		}
		});

	
		$('#assign_deliver_boy').on('change', function(){
            var order_id = {{ $order->id }};
            var delivery_boy = $('#assign_deliver_boy').val();
            $.post('{{ route('orders.delivery-boy-assign') }}', {
                _token          :'{{ @csrf_token() }}',
                order_id        :order_id,
                delivery_boy    :delivery_boy
            }, function(data){
                AIZ.plugins.notify('success', '{{ translate('Delivery boy has been assigned') }}');
            });
        });

        $('#update_delivery_status').on('change', function(){
            var order_id = {{ $order->id }};
            var status = $('#update_delivery_status').val();
            $.post('{{ route('orders.update_delivery_status') }}', {
                _token:'{{ @csrf_token() }}',
                order_id:order_id,
                status:status
            }, function(data){
                AIZ.plugins.notify('success', '{{ translate('Delivery status has been updated') }}');
            });
        });

        $('#update_payment_status').on('change', function(){
            var order_id = {{ $order->id }};
            var status = $('#update_payment_status').val();
            $.post('{{ route('orders.update_payment_status') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){
                AIZ.plugins.notify('success', '{{ translate('Payment status has been updated') }}');
            });
        });
		$('#delivery_partner').on('change', function(){
			if($(this).val() == "other"){
				$('#delivery_partner_other').show();
			}else{
				$('#delivery_partner_other').hide();
			}
		});
        $('#update_tracking_code').on('change', function(){
            var order_id = {{ $order->id }};
			var delivery_partner = $('#delivery_partner').val(); 
			var other = $('#delivery_partner_other').val();
            var tracking_code = $('#update_tracking_code').val();
            $.post('{{ route('orders.update_tracking_code') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,delivery_partner:delivery_partner,delivery_partner_other:other,tracking_code:tracking_code}, function(data){
                AIZ.plugins.notify('success', '{{ translate('Order tracking code has been updated') }}');
            });
        });
    </script>
@endsection
