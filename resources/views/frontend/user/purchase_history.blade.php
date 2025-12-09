@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 mt-0 h6">{{ translate('Purchase History') }}</h5>
        </div>
        @if (count($orders) > 0)
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Code')}}</th>
                            <th data-breakpoints="md">{{ translate('Date')}}</th>
                            <th>{{ translate('Amount')}}</th>
                            <th data-breakpoints="md">{{ translate('Delivery Status')}}</th>
                            <th data-breakpoints="md">{{ translate('Payment Status')}}</th>
                            <th class="text-right">{{ translate('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $key => $order)
                            @if (count($order->orderDetails) > 0)
                                <tr>
                                    <td>
                                        <a href="#{{ $order->code }}" onclick="show_purchase_history_details({{ $order->id }})">{{ $order->code }}</a>
                                    </td>
                                    <td>{{ date('d-m-Y', $order->date) }}</td>
                                    <td>
                                        {{ single_price($order->grand_total) }}
                                    </td>
                                    <td>
                                        {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                                        @if($order->delivery_viewed == 0)
                                            <span class="ml-2" style="color:green"><strong>*</strong></span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($order->payment_status == 'paid')
                                            <span class="badge badge-inline badge-success">{{translate('Paid')}}</span>
                                        @else
                                            <span class="badge badge-inline badge-danger">{{translate('Unpaid')}}</span>
                                        @endif
                                        @if($order->payment_status_viewed == 0)
                                            <span class="ml-2" style="color:green"><strong>*</strong></span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if ($order->orderDetails->first()->delivery_status == 'pending' && $order->payment_status == 'unpaid')
                                            <a href="javascript:void(0)" class="btn btn-danger btn-sm confirm-delete" data-href="{{route('orders.destroy', $order->id)}}" title="{{ translate('Cancel') }}">
                                               <i class="las la-trash"></i>
                                           </a>
                                        @endif
                                        <a href="javascript:void(0)" class="btn btn-info btn-sm" onclick="show_purchase_history_details({{ $order->id }})" title="{{ translate('Order Details') }}">
                                            <i class="las la-eye"></i>
                                        </a>
                                        <a class="btn btn-warning btn-sm" href="{{ route('invoice.download', $order->id) }}" title="{{ translate('Download Invoice') }}">
                                            <i class="las la-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    	{{ $orders->links() }}
              	</div>
            </div>
        @endif
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')

    <div class="modal fade" id="order_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div id="order-details-modal-body">

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="payment_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div id="payment_modal_body">

                </div>
            </div>
        </div>
    </div>

<div class="modal" id="makereview" data-backdrop="static">
	<div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Product Review</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div><div class="container"></div>
        <div class="modal-body" style="padding: 0px 20px 20px;">
            <div class="pt-4">
            <div class="border-bottom mb-4">
            <h3 class="fs-17 fw-600">
            {{ translate('Write a review')}}
            </h3>
            </div>
            <form class="form-default" role="form" action="{{ route('reviews.store') }}" method="POST">
            @csrf
            <input type="hidden" id="product_id" name="product_id" value="">
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
        </div>
      
      </div>
    </div>
</div>


@endsection

@section('script')
    <script type="text/javascript">
        $('#order_details').on('hidden.bs.modal', function () {
            location.reload();
        })
    </script>

@endsection
